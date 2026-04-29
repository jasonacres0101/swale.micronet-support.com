<x-layouts.app
    :title="'Maintenance | '.config('app.name')"
    heading="Maintenance"
    subheading="Schedule, track, complete, and report on CCTV maintenance tasks across sites, cameras, routers, SIMs, and annual service visits."
>
    @include('maintenance.partials.filters')

    <section class="panel overflow-hidden">
        <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="section-title">Maintenance tasks</h2>
                <p class="text-sm text-slate-500">{{ $tasks->count() }} task(s) match the current filters.</p>
            </div>
            @if (auth()->user()?->canCreateMaintenance())
                <a href="{{ route('maintenance.create') }}" class="btn-primary">Add maintenance task</a>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-slate-500">
                    <tr>
                        <th class="px-5 py-3 font-semibold">Task</th>
                        <th class="px-5 py-3 font-semibold">Type</th>
                        <th class="px-5 py-3 font-semibold">Site</th>
                        <th class="px-5 py-3 font-semibold">Camera</th>
                        <th class="px-5 py-3 font-semibold">Assigned</th>
                        <th class="px-5 py-3 font-semibold">Status</th>
                        <th class="px-5 py-3 font-semibold">Due</th>
                        <th class="px-5 py-3 font-semibold">Priority</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($tasks as $task)
                        <tr>
                            <td class="px-5 py-4">
                                <a href="{{ route('maintenance.show', $task) }}" class="font-semibold text-slate-950 hover:text-brand-700">{{ $task->title }}</a>
                                <p class="mt-1 text-xs text-slate-500">{{ $task->organisation?->name ?: $task->site?->organisation?->name ?: 'No organisation' }}</p>
                            </td>
                            <td class="px-5 py-4 text-slate-600">{{ $task->taskTypeLabel() }}</td>
                            <td class="px-5 py-4 text-slate-600">{{ $task->site?->name ?: 'No site' }}</td>
                            <td class="px-5 py-4 text-slate-600">{{ $task->camera?->name ?: 'No camera' }}</td>
                            <td class="px-5 py-4 text-slate-600">{{ $task->assignedUser?->name ?: 'Unassigned' }}</td>
                            <td class="px-5 py-4">
                                <span @class([
                                    'status-pill',
                                    'bg-slate-200 text-slate-700' => $task->status === 'scheduled',
                                    'bg-brand-100 text-brand-700' => $task->status === 'in_progress',
                                    'bg-emerald-100 text-emerald-700' => $task->status === 'completed',
                                    'bg-red-100 text-red-700' => $task->status === 'overdue',
                                    'bg-amber-100 text-amber-700' => $task->status === 'cancelled',
                                ])>{{ $task->statusLabel() }}</span>
                            </td>
                            <td class="px-5 py-4 text-slate-600">{{ optional($task->due_at)->format('d M Y H:i') ?? 'No due date' }}</td>
                            <td class="px-5 py-4 text-slate-600">{{ $task->priorityLabel() }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-8 text-center text-sm text-slate-500">No maintenance tasks found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-layouts.app>
