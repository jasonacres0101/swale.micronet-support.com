<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        return view('settings.index');
    }

    public function hikvisionSetup(): View
    {
        abort_unless(auth()->user()?->canViewAlarmAdmin(), 403);

        return view('settings.hikvision-setup', [
            'alarmEndpoint' => url('/api/hikvision/events'),
            'alarmPath' => '/api/hikvision/events',
            'tokenEnabled' => filled(config('hikvision.alarm_token')),
        ]);
    }

    public function cameraEmail(): View
    {
        abort_unless(auth()->user()?->canViewAlarmAdmin(), 403);

        return view('settings.camera-email', [
            'settings' => $this->cameraEmailSettings(),
            'imapEnabled' => function_exists('imap_open'),
        ]);
    }

    public function updateCameraEmail(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->canViewAlarmAdmin(), 403);

        $validated = $this->validatedCameraEmailSettings($request);
        $current = $this->cameraEmailSettings();

        $this->writeEnvValues([
            'CAMERA_EMAIL_INGEST_ENABLED' => $request->boolean('enabled') ? 'true' : 'false',
            'CAMERA_EMAIL_PROTOCOL' => $validated['protocol'],
            'CAMERA_EMAIL_HOST' => $validated['host'] ?? '',
            'CAMERA_EMAIL_PORT' => (string) $validated['port'],
            'CAMERA_EMAIL_ENCRYPTION' => $validated['encryption'],
            'CAMERA_EMAIL_VALIDATE_CERT' => $request->boolean('validate_cert') ? 'true' : 'false',
            'CAMERA_EMAIL_MAILBOX' => $validated['mailbox'],
            'CAMERA_EMAIL_USERNAME' => $validated['username'] ?? '',
            'CAMERA_EMAIL_PASSWORD' => filled($validated['password'] ?? null) ? $validated['password'] : ($current['password'] ?? ''),
            'CAMERA_EMAIL_MARK_SEEN_AFTER_IMPORT' => $request->boolean('mark_seen_after_import') ? 'true' : 'false',
            'CAMERA_EMAIL_DELETE_AFTER_IMPORT' => $request->boolean('delete_after_import') ? 'true' : 'false',
            'CAMERA_EMAIL_OFFLINE_AFTER_MINUTES' => (string) $validated['offline_after_minutes'],
        ]);

        Artisan::call('config:clear');

        return redirect()
            ->route('settings.camera-email')
            ->with('status', 'Camera email settings saved.');
    }

    public function testCameraEmail(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->canViewAlarmAdmin(), 403);

        $validated = $this->validatedCameraEmailSettings($request, passwordRequired: blank($this->cameraEmailSettings()['password'] ?? null));
        $password = filled($validated['password'] ?? null)
            ? $validated['password']
            : ($this->cameraEmailSettings()['password'] ?? '');

        if (($validated['protocol'] ?? 'imap') === 'pop3') {
            $message = $this->testPop3Connection([
                ...$validated,
                'password' => $password,
                'validate_cert' => $request->boolean('validate_cert'),
            ]);

            return back()
                ->withInput($request->except('password'))
                ->with($message['ok'] ? 'email_test_status' : 'email_test_error', $message['message']);
        }

        if (! function_exists('imap_open')) {
            return back()
                ->withInput($request->except('password'))
                ->with('email_test_error', 'The PHP IMAP extension is not enabled for this PHP version.');
        }

        $mailbox = $this->mailboxString([
            ...$validated,
            'validate_cert' => $request->boolean('validate_cert'),
        ]);

        $connection = @imap_open($mailbox, (string) ($validated['username'] ?? ''), (string) $password, OP_READONLY);

        if ($connection === false) {
            return back()
                ->withInput($request->except('password'))
                ->with('email_test_error', 'Connection failed: '.implode('; ', imap_errors() ?: ['Unknown IMAP error']));
        }

        $unseenCount = count(imap_search($connection, 'UNSEEN') ?: []);
        imap_close($connection);

        return back()
            ->withInput($request->except('password'))
            ->with('email_test_status', "Connection successful. {$unseenCount} unseen message(s) found.");
    }

    private function cameraEmailSettings(): array
    {
        $env = $this->readEnvValues();

        return [
            'enabled' => filter_var($env['CAMERA_EMAIL_INGEST_ENABLED'] ?? config('camera_email.enabled'), FILTER_VALIDATE_BOOLEAN),
            'protocol' => $env['CAMERA_EMAIL_PROTOCOL'] ?? config('camera_email.protocol', 'imap'),
            'host' => $env['CAMERA_EMAIL_HOST'] ?? config('camera_email.host'),
            'port' => (int) ($env['CAMERA_EMAIL_PORT'] ?? config('camera_email.port', 993)),
            'encryption' => $env['CAMERA_EMAIL_ENCRYPTION'] ?? config('camera_email.encryption', 'ssl'),
            'validate_cert' => filter_var($env['CAMERA_EMAIL_VALIDATE_CERT'] ?? config('camera_email.validate_cert', true), FILTER_VALIDATE_BOOLEAN),
            'mailbox' => $env['CAMERA_EMAIL_MAILBOX'] ?? config('camera_email.mailbox', 'INBOX'),
            'username' => $env['CAMERA_EMAIL_USERNAME'] ?? config('camera_email.username'),
            'password' => $env['CAMERA_EMAIL_PASSWORD'] ?? config('camera_email.password'),
            'mark_seen_after_import' => filter_var($env['CAMERA_EMAIL_MARK_SEEN_AFTER_IMPORT'] ?? config('camera_email.mark_seen_after_import', true), FILTER_VALIDATE_BOOLEAN),
            'delete_after_import' => filter_var($env['CAMERA_EMAIL_DELETE_AFTER_IMPORT'] ?? config('camera_email.delete_after_import', false), FILTER_VALIDATE_BOOLEAN),
            'offline_after_minutes' => (int) ($env['CAMERA_EMAIL_OFFLINE_AFTER_MINUTES'] ?? config('camera_email.offline_after_minutes', 65)),
        ];
    }

    private function validatedCameraEmailSettings(Request $request, bool $passwordRequired = false): array
    {
        return $request->validate([
            'enabled' => ['nullable', 'boolean'],
            'protocol' => ['required', 'in:imap,pop3'],
            'host' => ['required', 'string', 'max:255'],
            'port' => ['required', 'integer', 'min:1', 'max:65535'],
            'encryption' => ['required', 'in:ssl,tls,none'],
            'validate_cert' => ['nullable', 'boolean'],
            'mailbox' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255'],
            'password' => [$passwordRequired ? 'required' : 'nullable', 'string', 'max:255'],
            'mark_seen_after_import' => ['nullable', 'boolean'],
            'delete_after_import' => ['nullable', 'boolean'],
            'offline_after_minutes' => ['required', 'integer', 'min:5', 'max:1440'],
        ]);
    }

    private function testPop3Connection(array $settings): array
    {
        $transport = match (strtolower((string) $settings['encryption'])) {
            'ssl' => 'ssl',
            'tls' => 'tcp',
            default => 'tcp',
        };

        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => (bool) ($settings['validate_cert'] ?? true),
                'verify_peer_name' => (bool) ($settings['validate_cert'] ?? true),
            ],
        ]);

        $socket = @stream_socket_client(
            $transport.'://'.$settings['host'].':'.$settings['port'],
            $errorCode,
            $errorMessage,
            20,
            STREAM_CLIENT_CONNECT,
            $context
        );

        if (! is_resource($socket)) {
            return ['ok' => false, 'message' => "Connection failed: {$errorMessage} ({$errorCode})"];
        }

        try {
            $this->pop3ReadResponse($socket);

            if ($settings['encryption'] === 'tls') {
                $this->pop3Command($socket, 'STLS');
                stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            }

            $this->pop3Command($socket, 'USER '.$settings['username']);
            $this->pop3Command($socket, 'PASS '.$settings['password']);
            $status = $this->pop3Command($socket, 'STAT');
            $this->pop3Command($socket, 'QUIT');
        } catch (\Throwable $exception) {
            fclose($socket);

            return ['ok' => false, 'message' => 'Connection failed: '.$exception->getMessage()];
        }

        return ['ok' => true, 'message' => 'POP3 connection successful. '.$status];
    }

    private function pop3Command($socket, string $command): string
    {
        fwrite($socket, $command."\r\n");

        return $this->pop3ReadResponse($socket);
    }

    private function pop3ReadResponse($socket): string
    {
        $line = fgets($socket);

        if ($line === false || ! str_starts_with($line, '+OK')) {
            throw new \RuntimeException(trim((string) $line) ?: 'No response from POP3 server');
        }

        return rtrim($line, "\r\n");
    }

    private function mailboxString(array $settings): string
    {
        $flags = ['/imap'];
        $encryption = strtolower((string) $settings['encryption']);

        if (in_array($encryption, ['ssl', 'tls'], true)) {
            $flags[] = '/'.$encryption;
        }

        if (! (bool) ($settings['validate_cert'] ?? true)) {
            $flags[] = '/novalidate-cert';
        }

        return sprintf(
            '{%s:%d%s}%s',
            $settings['host'],
            $settings['port'],
            implode('', $flags),
            $settings['mailbox']
        );
    }

    private function readEnvValues(): array
    {
        $path = base_path('.env');

        if (! is_file($path)) {
            return [];
        }

        $values = [];

        foreach (file($path, FILE_IGNORE_NEW_LINES) ?: [] as $line) {
            if ($line === '' || str_starts_with(ltrim($line), '#') || ! str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $values[trim($key)] = trim($value, " \t\n\r\0\x0B\"'");
        }

        return $values;
    }

    private function writeEnvValues(array $values): void
    {
        $path = base_path('.env');
        $lines = is_file($path) ? file($path, FILE_IGNORE_NEW_LINES) ?: [] : [];
        $written = [];

        foreach ($lines as $index => $line) {
            if (! str_contains($line, '=')) {
                continue;
            }

            [$key] = explode('=', $line, 2);
            $key = trim($key);

            if (array_key_exists($key, $values)) {
                $lines[$index] = $key.'='.$this->envValue($values[$key]);
                $written[] = $key;
            }
        }

        foreach (array_diff(array_keys($values), $written) as $key) {
            $lines[] = $key.'='.$this->envValue($values[$key]);
        }

        file_put_contents($path, implode(PHP_EOL, $lines).PHP_EOL, LOCK_EX);
    }

    private function envValue(?string $value): string
    {
        $value = (string) $value;

        if ($value === '') {
            return '';
        }

        if (in_array(strtolower($value), ['true', 'false', 'null'], true) || is_numeric($value)) {
            return $value;
        }

        return '"'.str_replace(['\\', '"'], ['\\\\', '\\"'], $value).'"';
    }
}
