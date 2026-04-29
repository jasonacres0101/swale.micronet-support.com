<?php

namespace App\Models;

use App\Support\NormalizesMacAddresses;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'site_name',
    'site_id',
    'location_name',
    'ownership_type',
    'managed_by_council',
    'ip_address',
    'mac_address',
    'mac_address_normalized',
    'web_ui_url',
    'latitude',
    'longitude',
    'what3words',
    'connectivity_type',
    'connectivity_provider',
    'sim_number',
    'sim_iccid',
    'sim_static_ip',
    'apn_name',
    'router_model',
    'router_serial',
    'router_ip_address',
    'wan_ip_address',
    'private_apn',
    'connectivity_notes',
    'status',
    'is_online',
    'last_seen_at',
    'last_event_at',
    'description',
])]
class Camera extends Model
{
    use NormalizesMacAddresses;

    protected function casts(): array
    {
        return [
            'is_online' => 'boolean',
            'managed_by_council' => 'boolean',
            'private_apn' => 'boolean',
            'last_seen_at' => 'datetime',
            'last_event_at' => 'datetime',
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Camera $camera): void {
            if ($camera->isDirty('mac_address')) {
                $camera->mac_address_normalized = self::normalizeMacAddress($camera->mac_address);
            }

            if ($camera->isDirty('status')) {
                $camera->is_online = $camera->status === 'online';
            }
        });
    }

    public function hikvisionEvents(): HasMany
    {
        return $this->hasMany(HikvisionEvent::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function latestHikvisionEvent(): HasOne
    {
        return $this->hasOne(HikvisionEvent::class)->latestOfMany('event_time');
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(CameraStatusLog::class);
    }

    public function maintenanceTasks(): HasMany
    {
        return $this->hasMany(MaintenanceTask::class);
    }

    public function scopeWithMonitoringData(Builder $query): void
    {
        $query->with([
            'latestHikvisionEvent',
            'site.organisation',
            'site.cameras',
        ]);
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

            $query->whereHas('site', fn (Builder $siteQuery) => $siteQuery->where('organisation_id', $user->organisation_id));
        }
    }

    public function scopeApplyMonitoringFilters(Builder $query, array $filters): void
    {
        if (filled($filters['organisation'] ?? null)) {
            $query->whereHas('site', fn (Builder $siteQuery) => $siteQuery->where('organisation_id', $filters['organisation']));
        }

        if (filled($filters['site'] ?? null)) {
            $query->where('site_id', $filters['site']);
        }

        if (filled($filters['status'] ?? null)) {
            $status = $filters['status'];

            $query->where(function (Builder $statusQuery) use ($status): void {
                if ($status === 'online') {
                    $statusQuery->where('status', 'online')
                        ->orWhere(function (Builder $legacyQuery): void {
                            $legacyQuery->whereNull('status')->where('is_online', true);
                        });

                    return;
                }

                if ($status === 'offline') {
                    $statusQuery->where('status', 'offline')
                        ->orWhere(function (Builder $legacyQuery): void {
                            $legacyQuery->whereNull('status')->where('is_online', false);
                        });

                    return;
                }

                $statusQuery->where('status', 'unknown');
            });
        }

        if (filled($filters['connectivity_type'] ?? null)) {
            $query->where('connectivity_type', $filters['connectivity_type']);
        }

        if (filled($filters['ownership_type'] ?? null)) {
            $query->where('ownership_type', $filters['ownership_type']);
        }
    }
}
