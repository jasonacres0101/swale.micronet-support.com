<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'organisation_id',
    'name',
    'address_line_1',
    'address_line_2',
    'town',
    'postcode',
    'latitude',
    'longitude',
    'what3words',
    'permit_to_dig_number',
    'notes',
])]
class Site extends Model
{
    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function cameras(): HasMany
    {
        return $this->hasMany(Camera::class);
    }

    public function maintenanceTasks(): HasMany
    {
        return $this->hasMany(MaintenanceTask::class);
    }

    public function scopeVisibleToUser(Builder $query, ?User $user): void
    {
        if (! $user) {
            $query->whereRaw('1 = 0');

            return;
        }

        if ($user->isClient()) {
            if (blank($user->organisation_id)) {
                $query->whereRaw('1 = 0');

                return;
            }

            $query->where('organisation_id', $user->organisation_id);
        }
    }

    public function monitoringStatus(?Collection $cameras = null): string
    {
        return self::statusForCameras($cameras ?? $this->cameras);
    }

    public static function statusForCameras(iterable $cameras): string
    {
        $collection = $cameras instanceof Collection ? $cameras : Collection::make($cameras);

        if ($collection->isEmpty()) {
            return 'unknown';
        }

        $statuses = $collection
            ->map(fn (Camera $camera): string => $camera->status ?: ($camera->is_online ? 'online' : 'unknown'))
            ->values();

        if ($statuses->every(fn (string $status): bool => $status === 'online')) {
            return 'online';
        }

        if ($statuses->every(fn (string $status): bool => $status === 'offline')) {
            return 'offline';
        }

        return 'degraded';
    }
}
