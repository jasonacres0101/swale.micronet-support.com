<?php

namespace App\Console\Commands;

use App\Models\Camera;
use App\Models\CameraStatusLog;
use Illuminate\Console\Command;

class CheckCameraStatus extends Command
{
    protected $signature = 'cameras:check-status';

    protected $description = 'Mark cameras offline or unknown based on last_seen_at timestamps.';

    public function handle(): int
    {
        $offlineAfterMinutes = max(5, (int) config('camera_email.offline_after_minutes', 65));
        $threshold = now()->subMinutes($offlineAfterMinutes);

        Camera::query()->orderBy('id')->each(function (Camera $camera) use ($threshold, $offlineAfterMinutes): void {
            $newStatus = match (true) {
                $camera->last_seen_at === null => 'unknown',
                $camera->last_seen_at->greaterThan($threshold) => 'online',
                default => 'offline',
            };

            $oldStatus = $camera->status ?: ($camera->is_online ? 'online' : 'unknown');

            if ($oldStatus === $newStatus) {
                return;
            }

            $camera->status = $newStatus;
            $camera->save();

            CameraStatusLog::query()->create([
                'camera_id' => $camera->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'reason' => match ($newStatus) {
                    'offline' => "No camera report received within {$offlineAfterMinutes} minutes",
                    'unknown' => 'Camera has never reported a camera event',
                    default => 'Camera reported within status threshold',
                },
                'created_at' => now(),
            ]);
        });

        return self::SUCCESS;
    }
}
