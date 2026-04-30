<?php

namespace App\Console\Commands;

use App\Models\Camera;
use App\Models\CameraEmailSnapshot;
use App\Models\CameraStatusLog;
use App\Models\HikvisionEvent;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use stdClass;

class IngestCameraEmailSnapshots extends Command
{
    protected $signature = 'cameras:ingest-email-snapshots {--limit=50 : Maximum unseen messages to process}';

    protected $description = 'Read camera snapshot emails from an IMAP mailbox and match them to cameras by sender serial number.';

    public function handle(): int
    {
        if (! config('camera_email.enabled')) {
            $this->components->info('Camera email ingest is disabled.');

            return self::SUCCESS;
        }

        if (! function_exists('imap_open')) {
            $this->components->error('The PHP IMAP extension is not installed or enabled.');

            return self::FAILURE;
        }

        $mailbox = $this->mailboxString();
        $connection = @imap_open($mailbox, (string) config('camera_email.username'), (string) config('camera_email.password'));

        if ($connection === false) {
            $this->components->error('Unable to connect to camera email mailbox: '.implode('; ', imap_errors() ?: []));

            return self::FAILURE;
        }

        try {
            $messageNumbers = imap_search($connection, 'UNSEEN') ?: [];
            sort($messageNumbers);
            $messageNumbers = array_slice($messageNumbers, 0, max(1, (int) $this->option('limit')));

            foreach ($messageNumbers as $messageNumber) {
                $this->importMessage($connection, (int) $messageNumber);
            }

            if (config('camera_email.delete_after_import')) {
                imap_expunge($connection);
            }
        } finally {
            imap_close($connection);
        }

        $this->components->info('Camera email ingest complete.');

        return self::SUCCESS;
    }

    private function mailboxString(): string
    {
        $flags = ['/imap'];
        $encryption = strtolower((string) config('camera_email.encryption'));

        if (in_array($encryption, ['ssl', 'tls'], true)) {
            $flags[] = '/'.$encryption;
        }

        if (! config('camera_email.validate_cert')) {
            $flags[] = '/novalidate-cert';
        }

        return sprintf(
            '{%s:%d%s}%s',
            config('camera_email.host'),
            config('camera_email.port'),
            implode('', $flags),
            config('camera_email.mailbox')
        );
    }

    private function importMessage($connection, int $messageNumber): void
    {
        $overview = imap_fetch_overview($connection, (string) $messageNumber, 0)[0] ?? null;
        $uid = (string) imap_uid($connection, $messageNumber);
        $messageUid = sha1($this->mailboxString().'|'.$uid);

        if (CameraEmailSnapshot::query()->where('message_uid', $messageUid)->exists()) {
            $this->markMessageHandled($connection, $messageNumber);

            return;
        }

        $fromEmail = $this->fromEmail($overview);
        $fromName = $this->fromName($overview);
        $textBody = $this->textBody($connection, $messageNumber);
        $serialNumber = $this->serialFromEmail($fromEmail, $fromName) ?: $this->serialFromBody($textBody);
        $normalizedSerial = Camera::normalizeSerialNumber($serialNumber);
        $camera = $normalizedSerial
            ? Camera::query()->where('serial_number_normalized', $normalizedSerial)->first()
            : null;

        $receivedAt = $this->receivedAt($overview);
        $attachment = $this->firstImageAttachment($connection, $messageNumber);

        if ($attachment) {
            $attachment['path'] = $this->storeAttachment($attachment, $camera, $normalizedSerial, $messageUid);
        }

        $snapshot = CameraEmailSnapshot::query()->create([
            'camera_id' => $camera?->id,
            'message_uid' => $messageUid,
            'serial_number' => $serialNumber,
            'from_email' => $fromEmail,
            'subject' => $this->decodeHeader((string) ($overview->subject ?? '')),
            'attachment_path' => $attachment['path'] ?? null,
            'attachment_name' => $attachment['filename'] ?? null,
            'attachment_mime' => $attachment['mime'] ?? null,
            'attachment_size' => isset($attachment['contents']) ? strlen($attachment['contents']) : null,
            'received_at' => $receivedAt,
            'imported_at' => now(),
        ]);

        if ($camera instanceof Camera) {
            $this->markCameraSeen($camera, $snapshot, $receivedAt);
        } else {
            Log::warning('Camera snapshot email could not be matched to a camera.', [
                'snapshot_id' => $snapshot->id,
                'from_email' => $fromEmail,
                'serial_number' => $serialNumber,
            ]);
        }

        $this->markMessageHandled($connection, $messageNumber);
    }

    private function fromEmail(?stdClass $overview): ?string
    {
        $from = (string) ($overview->from ?? '');

        if ($from === '') {
            return null;
        }

        $addresses = imap_rfc822_parse_adrlist($from, '');
        $address = $addresses[0] ?? null;

        if (! $address || empty($address->mailbox) || empty($address->host)) {
            return null;
        }

        return strtolower($address->mailbox.'@'.$address->host);
    }

    private function fromName(?stdClass $overview): ?string
    {
        $from = (string) ($overview->from ?? '');

        if ($from === '') {
            return null;
        }

        $addresses = imap_rfc822_parse_adrlist($from, '');
        $address = $addresses[0] ?? null;

        if (! $address || empty($address->personal)) {
            return null;
        }

        return $this->decodeHeader((string) $address->personal);
    }

    private function serialFromEmail(?string $fromEmail, ?string $fromName): ?string
    {
        $fromNameSerial = $this->cameraSerialCandidate($fromName);

        if ($fromNameSerial !== null) {
            return $fromNameSerial;
        }

        if (! $fromEmail || ! str_contains($fromEmail, '@')) {
            return null;
        }

        return $this->cameraSerialCandidate(str($fromEmail)->before('@')->toString());
    }

    private function serialFromBody(?string $textBody): ?string
    {
        if (! $textBody) {
            return null;
        }

        if (! preg_match('/IPDOME\s+S\/N:\s*(.+)$/im', $textBody, $matches)) {
            return null;
        }

        return $this->cameraSerialCandidate(trim($matches[1]));
    }

    private function cameraSerialCandidate(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        $value = trim($value);

        if (preg_match('/([A-Z0-9]{2,}\d{8,}[A-Z0-9]*)/i', $value, $matches)) {
            return strtoupper($matches[1]);
        }

        return $value !== '' ? $value : null;
    }

    private function textBody($connection, int $messageNumber): ?string
    {
        $structure = imap_fetchstructure($connection, $messageNumber);

        if (! $structure) {
            return null;
        }

        return $this->findTextBody($connection, $messageNumber, $structure);
    }

    private function findTextBody($connection, int $messageNumber, stdClass $part, string $partNumber = ''): ?string
    {
        if (isset($part->parts) && is_array($part->parts)) {
            foreach ($part->parts as $index => $childPart) {
                $childPartNumber = $partNumber === '' ? (string) ($index + 1) : $partNumber.'.'.($index + 1);
                $body = $this->findTextBody($connection, $messageNumber, $childPart, $childPartNumber);

                if ($body !== null) {
                    return $body;
                }
            }

            return null;
        }

        $isTextPlain = (int) ($part->type ?? 7) === 0 && strtolower((string) ($part->subtype ?? '')) === 'plain';

        if (! $isTextPlain) {
            return null;
        }

        $body = imap_fetchbody($connection, $messageNumber, $partNumber === '' ? '1' : $partNumber);

        return match ((int) ($part->encoding ?? 0)) {
            3 => base64_decode($body, true) ?: '',
            4 => quoted_printable_decode($body),
            default => $body,
        };
    }

    private function receivedAt(?stdClass $overview): ?CarbonImmutable
    {
        if (! isset($overview->date)) {
            return null;
        }

        try {
            return CarbonImmutable::parse((string) $overview->date);
        } catch (\Throwable) {
            return null;
        }
    }

    private function firstImageAttachment($connection, int $messageNumber): ?array
    {
        $structure = imap_fetchstructure($connection, $messageNumber);

        if (! $structure) {
            return null;
        }

        foreach ($this->attachments($connection, $messageNumber, $structure) as $attachment) {
            if (str_starts_with((string) $attachment['mime'], 'image/')) {
                return $attachment;
            }
        }

        return null;
    }

    private function attachments($connection, int $messageNumber, stdClass $part, string $partNumber = ''): array
    {
        $attachments = [];

        if (isset($part->parts) && is_array($part->parts)) {
            foreach ($part->parts as $index => $childPart) {
                $childPartNumber = $partNumber === '' ? (string) ($index + 1) : $partNumber.'.'.($index + 1);
                $attachments = [
                    ...$attachments,
                    ...$this->attachments($connection, $messageNumber, $childPart, $childPartNumber),
                ];
            }

            return $attachments;
        }

        $filename = $this->partFilename($part);
        $mime = $this->partMime($part);
        $isAttachment = $filename !== null || str_starts_with($mime, 'image/');

        if (! $isAttachment) {
            return [];
        }

        $body = imap_fetchbody($connection, $messageNumber, $partNumber === '' ? '1' : $partNumber);
        $contents = match ((int) ($part->encoding ?? 0)) {
            3 => base64_decode($body, true) ?: '',
            4 => quoted_printable_decode($body),
            default => $body,
        };

        $attachments[] = [
            'filename' => $filename ?: 'snapshot.'.str($mime)->after('/')->before(';')->toString(),
            'mime' => $mime,
            'contents' => $contents,
        ];

        return $attachments;
    }

    private function partFilename(stdClass $part): ?string
    {
        foreach (['dparameters', 'parameters'] as $property) {
            foreach (($part->{$property} ?? []) as $parameter) {
                $attribute = strtolower((string) ($parameter->attribute ?? ''));

                if (in_array($attribute, ['filename', 'name'], true)) {
                    return $this->decodeHeader((string) $parameter->value);
                }
            }
        }

        return null;
    }

    private function partMime(stdClass $part): string
    {
        $primary = [
            0 => 'text',
            1 => 'multipart',
            2 => 'message',
            3 => 'application',
            4 => 'audio',
            5 => 'image',
            6 => 'video',
            7 => 'other',
        ][(int) ($part->type ?? 7)] ?? 'application';

        return strtolower($primary.'/'.($part->subtype ?? 'octet-stream'));
    }

    private function decodeHeader(string $value): string
    {
        $decoded = '';

        foreach (imap_mime_header_decode($value) ?: [] as $part) {
            $decoded .= $part->text;
        }

        return $decoded !== '' ? $decoded : $value;
    }

    private function storeAttachment(array $attachment, ?Camera $camera, ?string $normalizedSerial, string $messageUid): string
    {
        $extension = pathinfo((string) $attachment['filename'], PATHINFO_EXTENSION) ?: 'jpg';
        $folder = $camera ? 'camera-snapshots/'.$camera->id : 'camera-snapshots/unmatched';
        $filename = ($normalizedSerial ?: 'unknown').'-'.$messageUid.'.'.$extension;
        $path = $folder.'/'.$filename;

        Storage::disk('public')->put($path, $attachment['contents']);

        return $path;
    }

    private function markCameraSeen(Camera $camera, CameraEmailSnapshot $snapshot, ?CarbonImmutable $receivedAt): void
    {
        $oldStatus = $camera->status ?: ($camera->is_online ? 'online' : 'unknown');
        $seenAt = $receivedAt ?: now();

        $camera->status = 'online';
        $camera->last_seen_at = now();
        $camera->last_event_at = $seenAt;
        $camera->save();

        HikvisionEvent::query()->create([
            'camera_id' => $camera->id,
            'source_ip' => null,
            'event_type' => 'email_snapshot',
            'event_state' => 'received',
            'event_description' => 'Snapshot email received from '.$snapshot->from_email,
            'event_time' => $seenAt,
            'mac_address' => $camera->mac_address,
            'ip_address' => $camera->ip_address,
            'raw_payload' => json_encode([
                'snapshot_id' => $snapshot->id,
                'from_email' => $snapshot->from_email,
                'subject' => $snapshot->subject,
            ], JSON_THROW_ON_ERROR),
            'parsed_payload' => [
                'snapshot_id' => $snapshot->id,
                'serial_number' => $snapshot->serial_number,
                'attachment_path' => $snapshot->attachment_path,
            ],
        ]);

        if ($oldStatus !== 'online') {
            CameraStatusLog::query()->create([
                'camera_id' => $camera->id,
                'old_status' => $oldStatus,
                'new_status' => 'online',
                'reason' => 'Snapshot email received from camera',
                'created_at' => now(),
            ]);
        }
    }

    private function markMessageHandled($connection, int $messageNumber): void
    {
        if (config('camera_email.delete_after_import')) {
            imap_delete($connection, (string) $messageNumber);

            return;
        }

        if (config('camera_email.mark_seen_after_import')) {
            imap_setflag_full($connection, (string) $messageNumber, '\\Seen');
        }
    }
}
