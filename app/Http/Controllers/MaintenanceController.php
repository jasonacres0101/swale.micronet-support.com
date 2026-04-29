<?php

namespace App\Http\Controllers;

use App\Models\Camera;
use App\Models\MaintenanceTask;
use App\Models\MaintenanceTaskAttachment;
use App\Models\Organisation;
use App\Models\Site;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MaintenanceController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $this->filters($request);
        $tasks = MaintenanceTask::query()
            ->withMaintenanceData()
            ->visibleToUser($request->user())
            ->applyMaintenanceFilters($filters)
            ->orderByRaw('due_at is null')
            ->orderBy('due_at')
            ->latest('id')
            ->get();

        return view('maintenance.index', [
            'tasks' => $tasks,
            'filters' => $filters,
            ...$this->formData($request->user()),
        ]);
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()?->canCreateMaintenance(), 403);

        return view('maintenance.create', [
            'task' => new MaintenanceTask([
                'status' => MaintenanceTask::STATUS_SCHEDULED,
                'priority' => MaintenanceTask::PRIORITY_NORMAL,
                'recurrence_type' => MaintenanceTask::RECURRENCE_NONE,
            ]),
            ...$this->formData($request->user()),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->canCreateMaintenance(), 403);

        $validated = $this->validatedTask($request);
        $task = MaintenanceTask::query()->create($this->normaliseTaskData($validated));

        if ($task->assigned_user_id) {
            Log::info('Maintenance task assigned.', [
                'task_id' => $task->id,
                'assigned_user_id' => $task->assigned_user_id,
            ]);
        }

        return redirect()
            ->route('maintenance.show', $task)
            ->with('status', 'Maintenance task created.');
    }

    public function show(Request $request, MaintenanceTask $task): View
    {
        $task->loadMissing('organisation', 'site.organisation', 'camera.site.organisation', 'assignedUser', 'attachments.uploadedBy');
        abort_unless($request->user()?->canViewMaintenanceTask($task), 403);

        return view('maintenance.show', [
            'task' => $task,
            'maxUploadKb' => config('maintenance.max_upload_kb', 5120),
        ]);
    }

    public function edit(Request $request, MaintenanceTask $task): View
    {
        $task->loadMissing('organisation', 'site.organisation', 'camera.site.organisation');
        abort_unless($request->user()?->canUpdateMaintenanceTask($task), 403);

        return view('maintenance.edit', [
            'task' => $task,
            ...$this->formData($request->user()),
        ]);
    }

    public function update(Request $request, MaintenanceTask $task): RedirectResponse
    {
        $task->loadMissing('organisation', 'site.organisation', 'camera.site.organisation');
        abort_unless($request->user()?->canUpdateMaintenanceTask($task), 403);

        $task->update($this->normaliseTaskData($this->validatedTask($request)));

        return redirect()
            ->route('maintenance.show', $task)
            ->with('status', 'Maintenance task updated.');
    }

    public function start(Request $request, MaintenanceTask $task): RedirectResponse
    {
        $task->loadMissing('organisation', 'site.organisation', 'camera.site.organisation');
        abort_unless($request->user()?->canUpdateMaintenanceTask($task), 403);

        $task->update(['status' => MaintenanceTask::STATUS_IN_PROGRESS]);

        return back()->with('status', 'Maintenance task started.');
    }

    public function complete(Request $request, MaintenanceTask $task): RedirectResponse
    {
        $task->loadMissing('organisation', 'site.organisation', 'camera.site.organisation');
        abort_unless($request->user()?->canUpdateMaintenanceTask($task), 403);

        $validated = $request->validate([
            'completion_notes' => ['nullable', 'string'],
            'engineer_recommendations' => ['nullable', 'string'],
        ]);

        $task->update([
            'status' => MaintenanceTask::STATUS_COMPLETED,
            'completed_at' => now(),
            'completion_notes' => $validated['completion_notes'] ?? $task->completion_notes,
            'engineer_recommendations' => $validated['engineer_recommendations'] ?? $task->engineer_recommendations,
            'next_due_at' => $this->nextDueAt($task),
        ]);

        return back()->with('status', 'Maintenance task completed.');
    }

    public function cancel(Request $request, MaintenanceTask $task): RedirectResponse
    {
        $task->loadMissing('organisation', 'site.organisation', 'camera.site.organisation');
        abort_unless($request->user()?->canUpdateMaintenanceTask($task), 403);

        $task->update(['status' => MaintenanceTask::STATUS_CANCELLED]);

        return back()->with('status', 'Maintenance task cancelled.');
    }

    public function upload(Request $request, MaintenanceTask $task): RedirectResponse|JsonResponse
    {
        $task->loadMissing('organisation', 'site.organisation', 'camera.site.organisation');
        abort_unless($request->user()?->canUploadMaintenanceTaskAttachments($task), 403);

        $validated = $request->validate([
            'attachments' => ['required', 'array', 'min:1'],
            'attachments.*' => [
                'required',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:'.config('maintenance.max_upload_kb', 5120),
            ],
        ]);

        $stored = [];
        $disk = config('maintenance.upload_disk', 'public');
        $directory = trim(config('maintenance.upload_directory', 'maintenance'), '/');

        foreach ($validated['attachments'] as $file) {
            $originalFilename = preg_replace('/[^A-Za-z0-9._ -]/', '', basename($file->getClientOriginalName())) ?: 'maintenance-image.'.$file->extension();
            $safeName = Str::slug(pathinfo($originalFilename, PATHINFO_FILENAME)) ?: 'maintenance-image';
            $filename = $safeName.'-'.Str::random(10).'.'.$file->extension();
            $path = $file->storeAs($directory, $filename, $disk);

            $stored[] = MaintenanceTaskAttachment::query()->create([
                'maintenance_task_id' => $task->id,
                'filename' => $originalFilename,
                'path' => $path,
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'uploaded_by' => $request->user()->id,
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'attachments' => collect($stored)->map(fn (MaintenanceTaskAttachment $attachment): array => [
                    'id' => $attachment->id,
                    'filename' => $attachment->filename,
                    'url' => Storage::disk($disk)->url($attachment->path),
                ])->values(),
            ]);
        }

        return back()->with('status', count($stored).' attachment(s) uploaded.');
    }

    public function serviceReportPdf(Request $request, MaintenanceTask $task)
    {
        $task->loadMissing('organisation', 'site.organisation', 'camera.site.organisation', 'assignedUser', 'attachments.uploadedBy');
        abort_unless($request->user()?->canViewMaintenanceTask($task), 403);
        abort_unless($task->task_type === MaintenanceTask::TYPE_ANNUAL_SERVICE_REPORT, 404);

        return Pdf::loadView('maintenance.service-report-pdf', [
            'task' => $task,
            'generatedAt' => now(),
        ])
            ->setPaper('a4')
            ->download('annual-service-report-'.$task->id.'.pdf');
    }

    private function validatedTask(Request $request): array
    {
        return $request->validate([
            'organisation_id' => ['nullable', 'exists:organisations,id'],
            'site_id' => ['nullable', 'exists:sites,id'],
            'camera_id' => ['nullable', 'exists:cameras,id'],
            'assigned_user_id' => ['nullable', 'exists:users,id'],
            'task_type' => ['required', Rule::in(array_keys(MaintenanceTask::taskTypes()))],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(array_keys(MaintenanceTask::statuses()))],
            'priority' => ['required', Rule::in(array_keys(MaintenanceTask::priorities()))],
            'scheduled_for' => ['nullable', 'date'],
            'due_at' => ['nullable', 'date'],
            'recurrence_type' => ['nullable', Rule::in(array_keys(MaintenanceTask::recurrenceTypes()))],
            'recurrence_interval' => ['nullable', 'integer', 'min:1', 'max:52'],
            'notes' => ['nullable', 'string'],
            'engineer_recommendations' => ['nullable', 'string'],
            'completion_notes' => ['nullable', 'string'],
        ]);
    }

    private function normaliseTaskData(array $data): array
    {
        if (filled($data['camera_id'] ?? null)) {
            $camera = Camera::query()->with('site')->findOrFail($data['camera_id']);
            $data['site_id'] = $camera->site_id ?: $data['site_id'];
            $data['organisation_id'] = $camera->site?->organisation_id ?: $data['organisation_id'];
        }

        if (filled($data['site_id'] ?? null)) {
            $site = Site::query()->findOrFail($data['site_id']);
            $data['organisation_id'] = $site->organisation_id;
        }

        $data['recurrence_type'] = $data['recurrence_type'] ?: MaintenanceTask::RECURRENCE_NONE;
        $data['recurrence_interval'] = $data['recurrence_interval'] ?: 1;

        if (($data['status'] ?? null) === MaintenanceTask::STATUS_COMPLETED && blank($data['completed_at'] ?? null)) {
            $data['completed_at'] = now();
        }

        return $data;
    }

    private function filters(Request $request): array
    {
        return [
            'organisation' => $request->string('organisation')->toString(),
            'site' => $request->string('site')->toString(),
            'camera' => $request->string('camera')->toString(),
            'task_type' => $request->string('task_type')->toString(),
            'status' => $request->string('status')->toString(),
            'priority' => $request->string('priority')->toString(),
            'due_date' => $request->string('due_date')->toString(),
            'assigned_user' => $request->string('assigned_user')->toString(),
        ];
    }

    private function formData(?User $user): array
    {
        return [
            'organisations' => Organisation::query()->visibleToUser($user)->orderBy('name')->get(),
            'sites' => Site::query()->with('organisation')->visibleToUser($user)->orderBy('name')->get(),
            'cameras' => Camera::query()->with('site.organisation')->visibleToUser($user)->orderBy('name')->get(),
            'assignableUsers' => User::query()
                ->whereIn('role', [User::ROLE_ADMIN, User::ROLE_COUNCIL_OPERATOR, User::ROLE_ENGINEER])
                ->orderBy('name')
                ->get(),
            'taskTypes' => MaintenanceTask::taskTypes(),
            'statuses' => MaintenanceTask::statuses(),
            'priorities' => MaintenanceTask::priorities(),
            'recurrenceTypes' => MaintenanceTask::recurrenceTypes(),
        ];
    }

    private function nextDueAt(MaintenanceTask $task): ?\Carbon\CarbonInterface
    {
        if (blank($task->recurrence_type) || $task->recurrence_type === MaintenanceTask::RECURRENCE_NONE) {
            return null;
        }

        $base = $task->due_at ?? $task->scheduled_for ?? now();
        $interval = max(1, (int) ($task->recurrence_interval ?: 1));

        return match ($task->recurrence_type) {
            MaintenanceTask::RECURRENCE_WEEKLY => $base->copy()->addWeeks($interval),
            MaintenanceTask::RECURRENCE_MONTHLY => $base->copy()->addMonths($interval),
            MaintenanceTask::RECURRENCE_QUARTERLY => $base->copy()->addMonths(3 * $interval),
            MaintenanceTask::RECURRENCE_SIX_MONTHLY => $base->copy()->addMonths(6 * $interval),
            MaintenanceTask::RECURRENCE_ANNUALLY => $base->copy()->addYears($interval),
            default => null,
        };
    }
}
