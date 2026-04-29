<x-layouts.app
    :title="'Maintenance task types | '.config('app.name')"
    heading="Maintenance task types"
    subheading="Add and edit the task types shown when creating maintenance tasks."
>
    <div class="space-y-5">
        <section class="panel p-5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="section-title">Task type settings</h2>
                    <p class="mt-1 text-sm text-slate-500">These values power the Task type dropdown on maintenance create and edit pages.</p>
                </div>
                <a href="{{ route('settings.maintenance-task-types.create') }}" class="btn-primary">Add task type</a>
            </div>
        </section>

        <section class="panel overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-slate-500">
                        <tr>
                            <th class="px-5 py-3 font-semibold">Task type</th>
                            <th class="px-5 py-3 font-semibold">System key</th>
                            <th class="px-5 py-3 font-semibold">Sort</th>
                            <th class="px-5 py-3 font-semibold">Status</th>
                            <th class="px-5 py-3 font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach ($taskTypes as $taskType)
                            <tr>
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-slate-950">{{ $taskType->name }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $taskType->description ?: 'No description' }}</p>
                                </td>
                                <td class="px-5 py-4 font-mono text-xs text-slate-600">{{ $taskType->slug }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $taskType->sort_order }}</td>
                                <td class="px-5 py-4">
                                    <span class="rounded-md px-2.5 py-1 text-xs font-semibold uppercase tracking-wide {{ $taskType->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600' }}">
                                        {{ $taskType->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <a href="{{ route('settings.maintenance-task-types.edit', $taskType) }}" class="btn-secondary">Edit task type</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-layouts.app>
