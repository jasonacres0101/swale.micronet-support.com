<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'type',
    'contact_name',
    'contact_email',
    'contact_phone',
    'notes',
])]
class Organisation extends Model
{
    public function sites(): HasMany
    {
        return $this->hasMany(Site::class);
    }

    public function maintenanceTasks(): HasMany
    {
        return $this->hasMany(MaintenanceTask::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
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

            $query->whereKey($user->organisation_id);
        }
    }
}
