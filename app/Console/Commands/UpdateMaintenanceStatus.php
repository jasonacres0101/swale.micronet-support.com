<?php

namespace App\Console\Commands;

use App\Models\MaintenanceTask;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateMaintenanceStatus extends Command
{
    protected $signature = 'maintenance:update-status';

    protected $description = 'Mark overdue maintenance tasks and generate the next recurring maintenance tasks.';

    public function handle(): int
    {
        $overdueCount = MaintenanceTask::query()
            ->whereIn('status', [MaintenanceTask::STATUS_SCHEDULED, MaintenanceTask::STATUS_IN_PROGRESS])
            ->whereNull('completed_at')
            ->whereNotNull('due_at')
            ->where('due_at', '<', now())
            ->update(['status' => MaintenanceTask::STATUS_OVERDUE]);

        if ($overdueCount > 0) {
            Log::warning('Maintenance tasks marked overdue.', ['count' => $overdueCount]);
        }

        $generatedCount = 0;

        MaintenanceTask::query()
            ->where('status', MaintenanceTask::STATUS_COMPLETED)
            ->whereNotNull('completed_at')
            ->whereNull('recurrence_generated_at')
            ->whereNotNull('next_due_at')
            ->whereNotIn('recurrence_type', [MaintenanceTask::RECURRENCE_NONE, ''])
            ->with('assignedUser')
            ->each(function (MaintenanceTask $task) use (&$generatedCount): void {
                $alreadyGenerated = MaintenanceTask::query()
                    ->where('recurring_source_id', $task->id)
                    ->exists();

                if ($alreadyGenerated) {
                    $task->update(['recurrence_generated_at' => now()]);

                    return;
                }

                $nextTask = MaintenanceTask::query()->create([
                    'organisation_id' => $task->organisation_id,
                    'site_id' => $task->site_id,
                    'camera_id' => $task->camera_id,
                    'assigned_user_id' => $task->assigned_user_id,
                    'task_type' => $task->task_type,
                    'title' => $task->title,
                    'description' => $task->description,
                    'status' => MaintenanceTask::STATUS_SCHEDULED,
                    'priority' => $task->priority,
                    'scheduled_for' => $task->next_due_at?->toDateString(),
                    'due_at' => $task->next_due_at,
                    'recurrence_type' => $task->recurrence_type,
                    'recurrence_interval' => $task->recurrence_interval,
                    'notes' => $task->notes,
                    'engineer_recommendations' => null,
                    'completion_notes' => null,
                    'recurring_source_id' => $task->id,
                ]);

                $task->update(['recurrence_generated_at' => now()]);
                $generatedCount++;

                Log::info('Generated recurring maintenance task.', [
                    'source_task_id' => $task->id,
                    'new_task_id' => $nextTask->id,
                    'assigned_user_id' => $nextTask->assigned_user_id,
                ]);
            });

        $dueSoonCount = MaintenanceTask::query()
            ->where('task_type', MaintenanceTask::TYPE_ANNUAL_SERVICE_REPORT)
            ->whereIn('status', [MaintenanceTask::STATUS_SCHEDULED, MaintenanceTask::STATUS_IN_PROGRESS])
            ->whereBetween('due_at', [now(), now()->addDays(14)])
            ->count();

        if ($dueSoonCount > 0) {
            Log::info('Annual service reports due soon.', ['count' => $dueSoonCount]);
        }

        $this->info("Marked {$overdueCount} overdue task(s); generated {$generatedCount} recurring task(s).");

        return self::SUCCESS;
    }
}
