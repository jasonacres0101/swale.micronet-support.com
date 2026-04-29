<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Camera;
use App\Models\CameraStatusLog;
use App\Models\HikvisionEvent;
use App\Services\HikvisionEventParser;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HikvisionEventController extends Controller
{
    public function __invoke(Request $request, HikvisionEventParser $parser): JsonResponse
    {
        $configuredToken = config('hikvision.alarm_token');

        if (filled($configuredToken)) {
            $suppliedToken = $request->header('X-Hikvision-Token') ?: $request->query('token');

            if (! hash_equals((string) $configuredToken, (string) $suppliedToken)) {
                Log::warning('Hikvision alarm endpoint rejected an invalid token.', [
                    'source_ip' => $request->ip(),
                    'has_header_token' => filled($request->header('X-Hikvision-Token')),
                    'has_query_token' => filled($request->query('token')),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Forbidden',
                ], 403);
            }
        } else {
            Log::warning('Hikvision alarm endpoint is accepting requests without X-Hikvision-Token protection.');
        }

        $parsed = $parser->parse($request);
        $sourceIp = $request->ip();
        $normalizedMac = Camera::normalizeMacAddress($parsed['mac_address']);
        $payloadIp = $parsed['ip_address'];

        $camera = $this->matchCamera($normalizedMac, $payloadIp, $sourceIp);
        $eventTime = $parsed['event_time'] instanceof CarbonImmutable
            ? $parsed['event_time']
            : CarbonImmutable::now();

        $event = HikvisionEvent::query()->create([
            'camera_id' => $camera?->id,
            'source_ip' => $sourceIp,
            'event_type' => $parsed['event_type'],
            'event_state' => $parsed['event_state'],
            'event_description' => $parsed['event_description'],
            'event_time' => $parsed['event_time'],
            'mac_address' => $parsed['mac_address'],
            'ip_address' => $payloadIp,
            'raw_payload' => $parsed['raw_payload'],
            'parsed_payload' => $parsed['parsed_payload'],
        ]);

        if ($camera instanceof Camera) {
            $this->updateCameraStatusFromEvent($camera, $parsed['mac_address'], $normalizedMac, $payloadIp, $eventTime);
        } else {
            Log::warning('Hikvision event could not be matched to a camera.', [
                'event_id' => $event->id,
                'mac_address' => $parsed['mac_address'],
                'ip_address' => $payloadIp,
                'source_ip' => $sourceIp,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Event received',
        ]);
    }

    private function matchCamera(?string $normalizedMac, ?string $payloadIp, ?string $sourceIp): ?Camera
    {
        if ($normalizedMac) {
            $camera = Camera::query()
                ->where('mac_address_normalized', $normalizedMac)
                ->first();

            if ($camera) {
                return $camera;
            }
        }

        if ($payloadIp) {
            $camera = Camera::query()
                ->where('ip_address', $payloadIp)
                ->first();

            if ($camera) {
                return $camera;
            }
        }

        if ($sourceIp) {
            return Camera::query()
                ->where('ip_address', $sourceIp)
                ->first();
        }

        return null;
    }

    private function updateCameraStatusFromEvent(
        Camera $camera,
        ?string $rawMacAddress,
        ?string $normalizedMac,
        ?string $payloadIp,
        CarbonImmutable $eventTime
    ): void {
        $oldStatus = $camera->status ?: ($camera->is_online ? 'online' : 'unknown');

        if ($normalizedMac) {
            $camera->mac_address = $rawMacAddress;
            $camera->mac_address_normalized = $normalizedMac;
        }

        if ($normalizedMac && $payloadIp && $camera->ip_address !== $payloadIp) {
            $camera->ip_address = $payloadIp;
        }

        $camera->status = 'online';
        $camera->last_seen_at = now();
        $camera->last_event_at = $eventTime;
        $camera->save();

        if ($oldStatus !== 'online') {
            CameraStatusLog::query()->create([
                'camera_id' => $camera->id,
                'old_status' => $oldStatus,
                'new_status' => 'online',
                'reason' => 'Hikvision alarm event received',
                'created_at' => now(),
            ]);
        }
    }
}
