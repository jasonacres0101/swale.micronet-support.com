<x-layouts.app
    :title="$site->name.' | Site | '.config('app.name')"
    :heading="$site->name"
    :subheading="($site->organisation?->name ?: 'Unassigned organisation').' · '.trim(($site->town ?: '').' '.($site->postcode ?: ''))"
>
    <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_22rem]">
        <main class="space-y-5">
            <section class="panel p-5">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Site overview</p>
                        <h2 class="mt-1 text-xl font-semibold text-slate-950">{{ $site->name }}</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-600">{{ $site->notes ?: 'No site notes recorded.' }}</p>
                    </div>
                    @if (auth()->user()?->canManageSites())
                        <a href="{{ route('sites.edit', $site) }}" class="btn-primary">Edit site</a>
                    @endif
                </div>
            </section>

            <section class="panel overflow-hidden">
                <div class="border-b border-slate-200 px-5 py-4">
                    <h2 class="section-title">Maintenance schedule</h2>
                    <p class="text-sm text-slate-500">Upcoming and recent maintenance across all cameras on this site.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-left text-slate-500">
                            <tr>
                                <th class="px-5 py-3 font-semibold">Task</th>
                                <th class="px-5 py-3 font-semibold">Camera</th>
                                <th class="px-5 py-3 font-semibold">Status</th>
                                <th class="px-5 py-3 font-semibold">Due</th>
                                <th class="px-5 py-3 font-semibold">Assigned</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse ($maintenanceTasks as $task)
                                <tr>
                                    <td class="px-5 py-4">
                                        <a href="{{ route('maintenance.show', $task) }}" class="font-semibold text-slate-950 hover:text-brand-700">{{ $task->title }}</a>
                                        <p class="mt-1 text-xs text-slate-500">{{ $task->taskTypeLabel() }}</p>
                                    </td>
                                    <td class="px-5 py-4 text-slate-600">{{ $task->camera?->name ?: 'Site level' }}</td>
                                    <td class="px-5 py-4 text-slate-600">{{ $task->statusLabel() }}</td>
                                    <td class="px-5 py-4 text-slate-600">{{ optional($task->due_at)->format('d M Y H:i') ?? 'No due date' }}</td>
                                    <td class="px-5 py-4 text-slate-600">{{ $task->assignedUser?->name ?: 'Unassigned' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-8 text-center text-sm text-slate-500">No maintenance tasks recorded for this site.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </main>

        <aside class="space-y-4">
            <section class="panel p-5">
                <h2 class="section-title">Site details</h2>
                <div class="mt-4 space-y-3 text-sm text-slate-600">
                    <p><span class="font-semibold text-slate-900">Organisation:</span> {{ $site->organisation?->name ?: 'Not set' }}</p>
                    <p><span class="font-semibold text-slate-900">Address:</span> {{ $site->address_line_1 ?: 'Not set' }}</p>
                    <p><span class="font-semibold text-slate-900">what3words:</span> {{ $site->what3words ?: 'Not set' }}</p>
                    <p><span class="font-semibold text-slate-900">Permit:</span> {{ $site->permit_to_dig_number ?: 'Not set' }}</p>
                </div>
            </section>

            <section class="panel p-5">
                <h2 class="section-title">Cameras</h2>
                <div class="mt-4 grid gap-2">
                    @foreach ($site->cameras as $camera)
                        <a href="{{ route('cameras.show', $camera) }}" class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-3 text-sm font-semibold text-slate-900 hover:border-brand-200 hover:bg-white">
                            {{ $camera->name }}
                        </a>
                    @endforeach
                </div>
            </section>
        </aside>
    </div>
</x-layouts.app>
