<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

#[Fillable([
    'organisation_id',
    'site_id',
    'camera_id',
    'assigned_user_id',
    'task_type',
    'title',
    'description',
    'status',
    'priority',
    'scheduled_for',
    'due_at',
    'completed_at',
    'recurrence_type',
    'recurrence_interval',
    'next_due_at',
    'notes',
    'engineer_recommendations',
    'completion_notes',
    'recurring_source_id',
    'recurrence_generated_at',
])]
class MaintenanceTask extends Model
{
    public const TYPE_INSPECTION = 'scheduled_camera_inspection';
    public const TYPE_LENS_CLEANING = 'lens_cleaning';
    public const TYPE_FIRMWARE_UPDATE = 'firmware_update';
    public const TYPE_ROUTER_SIM_CHECK = 'router_sim_check';
    public const TYPE_ANNUAL_SERVICE_REPORT = 'annual_service_report';
    public const TYPE_OTHER = 'other';

    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_OVERDUE = 'overdue';
    public const STATUS_CANCELLED = 'cancelled';

    public const PRIORITY_LOW = 'low';
    public const PRIORITY_NORMAL = 'normal';
    public const PRIORITY_HIGH = 'high';
    public const PRIORITY_URGENT = 'urgent';

    public const RECURRENCE_NONE = 'none';
    public const RECURRENCE_WEEKLY = 'weekly';
    public const RECURRENCE_MONTHLY = 'monthly';
    public const RECURRENCE_QUARTERLY = 'quarterly';
    public const RECURRENCE_SIX_MONTHLY = 'six_monthly';
    public const RECURRENCE_ANNUALLY = 'annually';

    protected function casts(): array
    {
        return [
            'scheduled_for' => 'date',
            'due_at' => 'datetime',
            'completed_at' => 'datetime',
            'next_due_at' => 'datetime',
            'recurrence_generated_at' => 'datetime',
        ];
    }

    public static function taskTypes(bool $activeOnly = true): array
    {
        $defaults = [
            self::TYPE_INSPECTION => 'Scheduled camera inspection',
            self::TYPE_LENS_CLEANING => 'Lens cleaning',
            self::TYPE_FIRMWARE_UPDATE => 'Firmware update',
            self::TYPE_ROUTER_SIM_CHECK => 'Router/SIM check',
            self::TYPE_ANNUAL_SERVICE_REPORT => 'Annual service report',
            self::TYPE_OTHER => 'Other',
        ];

        if (! Schema::hasTable('maintenance_task_types')) {
            return $defaults;
        }

        $query = MaintenanceTaskType::query()->orderBy('sort_order')->orderBy('name');

        if ($activeOnly) {
            $query->where('is_active', true);
        }

        $types = $query->pluck('name', 'slug')->all();

        return $types === [] ? $defaults : $types;
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_SCHEDULED => 'Scheduled',
            self::STATUS_IN_PROGRESS => 'In progress',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_OVERDUE => 'Overdue',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }

    public static function priorities(): array
    {
        return [
            self::PRIORITY_LOW => 'Low',
            self::PRIORITY_NORMAL => 'Normal',
            self::PRIORITY_HIGH => 'High',
            self::PRIORITY_URGENT => 'Urgent',
        ];
    }

    public static function recurrenceTypes(): array
    {
        return [
            self::RECURRENCE_NONE => 'None',
            self::RECURRENCE_WEEKLY => 'Weekly',
            self::RECURRENCE_MONTHLY => 'Monthly',
            self::RECURRENCE_QUARTERLY => 'Quarterly',
            self::RECURRENCE_SIX_MONTHLY => 'Six monthly',
            self::RECURRENCE_ANNUALLY => 'Annually',
        ];
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function camera(): BelongsTo
    {
        return $this->belongsTo(Camera::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(MaintenanceTaskAttachment::class);
    }

    public function recurringSource(): BelongsTo
    {
        return $this->belongsTo(self::class, 'recurring_source_id');
    }

    public function scopeWithMaintenanceData(Builder $query): void
    {
        $query->with([
            'organisation',
            'site.organisation',
            'camera.site.organisation',
            'assignedUser',
            'attachments.uploadedBy',
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

            $query->where(function (Builder $visibilityQuery) use ($user): void {
                $visibilityQuery
                    ->where('organisation_id', $user->organisation_id)
                    ->orWhereHas('site', fn (Builder $siteQuery) => $siteQuery->where('organisation_id', $user->organisation_id))
                    ->orWhereHas('camera.site', fn (Builder $siteQuery) => $siteQuery->where('organisation_id', $user->organisation_id));
            });
        }
    }

    public function scopeApplyMaintenanceFilters(Builder $query, array $filters): void
    {
        if (filled($filters['organisation'] ?? null)) {
            $query->where(function (Builder $organisationQuery) use ($filters): void {
                $organisationQuery
                    ->where('organisation_id', $filters['organisation'])
                    ->orWhereHas('site', fn (Builder $siteQuery) => $siteQuery->where('organisation_id', $filters['organisation']))
                    ->orWhereHas('camera.site', fn (Builder $siteQuery) => $siteQuery->where('organisation_id', $filters['organisation']));
            });
        }

        foreach (['site' => 'site_id', 'camera' => 'camera_id', 'assigned_user' => 'assigned_user_id'] as $filter => $column) {
            if (filled($filters[$filter] ?? null)) {
                $query->where($column, $filters[$filter]);
            }
        }

        foreach (['task_type', 'status', 'priority'] as $column) {
            if (filled($filters[$column] ?? null)) {
                $query->where($column, $filters[$column]);
            }
        }

        if (filled($filters['due_date'] ?? null)) {
            $query->whereDate('due_at', $filters['due_date']);
        }
    }

    public function statusLabel(): string
    {
        return self::statuses()[$this->status] ?? str($this->status)->replace('_', ' ')->title()->toString();
    }

    public function taskTypeLabel(): string
    {
        return self::taskTypes(activeOnly: false)[$this->task_type] ?? str($this->task_type)->replace('_', ' ')->title()->toString();
    }

    public function priorityLabel(): string
    {
        return self::priorities()[$this->priority] ?? ucfirst($this->priority);
    }
}
