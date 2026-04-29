<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceTaskType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MaintenanceTaskTypeController extends Controller
{
    public function index(): View
    {
        return view('settings.maintenance-task-types.index', [
            'taskTypes' => MaintenanceTaskType::query()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('settings.maintenance-task-types.create', [
            'taskType' => new MaintenanceTaskType([
                'is_active' => true,
                'sort_order' => MaintenanceTaskType::query()->max('sort_order') + 1,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        MaintenanceTaskType::query()->create($this->validated($request));

        return redirect()
            ->route('settings.maintenance-task-types.index')
            ->with('status', 'Maintenance task type created.');
    }

    public function edit(MaintenanceTaskType $taskType): View
    {
        return view('settings.maintenance-task-types.edit', [
            'taskType' => $taskType,
        ]);
    }

    public function update(Request $request, MaintenanceTaskType $taskType): RedirectResponse
    {
        $taskType->update($this->validated($request, $taskType));

        return redirect()
            ->route('settings.maintenance-task-types.index')
            ->with('status', 'Maintenance task type updated.');
    }

    private function validated(Request $request, ?MaintenanceTaskType $taskType = null): array
    {
        if (blank($request->input('slug'))) {
            $request->merge(['slug' => Str::slug($request->input('name', ''), '_')]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9_]+$/',
                Rule::unique('maintenance_task_types', 'slug')->ignore($taskType?->id),
            ],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        return $validated;
    }
}
