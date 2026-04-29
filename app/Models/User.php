<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name',
    'email',
    'password',
    'role',
    'organisation_id',
    'phone',
    'job_title',
    'department',
    'notes',
    'is_active',
    'last_login_at',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_COUNCIL_OPERATOR = 'council_operator';
    public const ROLE_ENGINEER = 'engineer';
    public const ROLE_CLIENT = 'client';
    public const ROLE_AUDITOR = 'auditor';

    public const ROLE_OPERATOR = 'council_operator';
    public const ROLE_VIEWER = 'auditor';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function assignedMaintenanceTasks(): HasMany
    {
        return $this->hasMany(MaintenanceTask::class, 'assigned_user_id');
    }

    public function uploadedMaintenanceAttachments(): HasMany
    {
        return $this->hasMany(MaintenanceTaskAttachment::class, 'uploaded_by');
    }

    public static function availableRoles(): array
    {
        return [
            self::ROLE_ADMIN => 'Administrator',
            self::ROLE_COUNCIL_OPERATOR => 'Council operator',
            self::ROLE_ENGINEER => 'Engineer',
            self::ROLE_CLIENT => 'Client',
            self::ROLE_AUDITOR => 'Auditor',
        ];
    }

    public function hasRole(string $role): bool
    {
        return $this->role === $this->canonicalRole($role);
    }

    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, array_map($this->canonicalRole(...), $roles), true);
    }

    public function canManageUsers(): bool
    {
        return $this->hasRole(self::ROLE_ADMIN);
    }

    public function canCreateCameras(): bool
    {
        return $this->hasRole(self::ROLE_ADMIN);
    }

    public function canUpdateCameras(): bool
    {
        return $this->hasAnyRole([self::ROLE_ADMIN, self::ROLE_ENGINEER]);
    }

    public function canManageCameras(): bool
    {
        return $this->canUpdateCameras();
    }

    public function canViewAlarmAdmin(): bool
    {
        return $this->hasAnyRole([self::ROLE_ADMIN, self::ROLE_COUNCIL_OPERATOR, self::ROLE_ENGINEER]);
    }

    public function canViewSettings(): bool
    {
        return $this->canManageUsers()
            || $this->canViewAlarmAdmin()
            || $this->canViewOrganisationDirectory()
            || $this->canViewSiteDirectory();
    }

    public function canViewOrganisationDirectory(): bool
    {
        return $this->hasAnyRole([self::ROLE_ADMIN, self::ROLE_COUNCIL_OPERATOR, self::ROLE_AUDITOR]);
    }

    public function canManageOrganisations(): bool
    {
        return $this->hasRole(self::ROLE_ADMIN);
    }

    public function canViewSiteDirectory(): bool
    {
        return $this->hasAnyRole([self::ROLE_ADMIN, self::ROLE_COUNCIL_OPERATOR, self::ROLE_CLIENT, self::ROLE_AUDITOR]);
    }

    public function canManageSites(): bool
    {
        return $this->hasRole(self::ROLE_ADMIN);
    }

    public function isClient(): bool
    {
        return $this->hasRole(self::ROLE_CLIENT);
    }

    public function canViewCamera(Camera $camera): bool
    {
        if (! $this->isClient()) {
            return true;
        }

        return filled($this->organisation_id)
            && (int) $camera->site?->organisation_id === (int) $this->organisation_id;
    }

    public function canUpdateCamera(Camera $camera): bool
    {
        return $this->canUpdateCameras() && $this->canViewCamera($camera);
    }

    public function canViewMaintenance(): bool
    {
        return $this->hasAnyRole([
            self::ROLE_ADMIN,
            self::ROLE_COUNCIL_OPERATOR,
            self::ROLE_ENGINEER,
            self::ROLE_CLIENT,
            self::ROLE_AUDITOR,
        ]);
    }

    public function canCreateMaintenance(): bool
    {
        return $this->hasAnyRole([self::ROLE_ADMIN, self::ROLE_COUNCIL_OPERATOR]);
    }

    public function canUpdateMaintenance(): bool
    {
        return $this->hasAnyRole([self::ROLE_ADMIN, self::ROLE_COUNCIL_OPERATOR, self::ROLE_ENGINEER]);
    }

    public function canUploadMaintenanceAttachments(): bool
    {
        return $this->hasAnyRole([self::ROLE_ADMIN, self::ROLE_COUNCIL_OPERATOR, self::ROLE_ENGINEER]);
    }

    public function canViewMaintenanceTask(MaintenanceTask $task): bool
    {
        if (! $this->canViewMaintenance()) {
            return false;
        }

        if (! $this->isClient()) {
            return true;
        }

        return filled($this->organisation_id)
            && (
                (int) $task->organisation_id === (int) $this->organisation_id
                || (int) $task->site?->organisation_id === (int) $this->organisation_id
                || (int) $task->camera?->site?->organisation_id === (int) $this->organisation_id
            );
    }

    public function canUpdateMaintenanceTask(MaintenanceTask $task): bool
    {
        return $this->canUpdateMaintenance() && $this->canViewMaintenanceTask($task);
    }

    public function canUploadMaintenanceTaskAttachments(MaintenanceTask $task): bool
    {
        return $this->canUploadMaintenanceAttachments() && $this->canViewMaintenanceTask($task);
    }

    private function canonicalRole(string $role): string
    {
        return match ($role) {
            'operator' => self::ROLE_COUNCIL_OPERATOR,
            'viewer' => self::ROLE_AUDITOR,
            default => $role,
        };
    }
}
