<x-layouts.app
    :title="'Sites | '.config('app.name')"
    heading="Sites"
    subheading="Manage monitored locations, map coordinates, and site ownership across council and client estates."
>
    <div class="space-y-6">
        <section class="panel p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-xl font-bold text-slate-950">Site directory</h2>
                    <p class="mt-2 text-sm text-slate-500">Sites connect organisations to cameras and hold shared location data such as addresses and map coordinates.</p>
                </div>
                @if (auth()->user()?->canManageSites())
                    <a href="{{ route('sites.create') }}" class="btn-primary">Add site</a>
                @endif
            </div>
        </section>

        <section class="panel overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50/90 text-left text-slate-500">
                        <tr>
                            <th class="px-6 py-4 font-semibold">Site</th>
                            <th class="px-6 py-4 font-semibold">Organisation</th>
                            <th class="px-6 py-4 font-semibold">Address</th>
                            <th class="px-6 py-4 font-semibold">Status</th>
                            <th class="px-6 py-4 font-semibold">Cameras</th>
                            @if (auth()->user()?->canManageSites())
                                <th class="px-6 py-4 font-semibold">Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($sites as $site)
                            @php($status = $site->monitoringStatus($site->cameras))
                            <tr class="bg-white/70">
                                <td class="px-6 py-4">
                                    <a href="{{ route('sites.show', $site) }}" class="font-semibold text-slate-900 hover:text-brand-700">{{ $site->name }}</a>
                                    <p class="mt-1 text-xs text-slate-500">{{ $site->what3words ?: 'No what3words set' }}</p>
                                </td>
                                <td class="px-6 py-4 text-slate-600">{{ $site->organisation?->name ?: 'Unassigned organisation' }}</td>
                                <td class="px-6 py-4 text-slate-600">
                                    <p>{{ $site->address_line_1 ?: 'No address line 1' }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ trim(($site->town ?: '').' '.($site->postcode ?: '')) ?: 'No town or postcode' }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <span @class([
                                        'status-pill',
                                        'bg-emerald-100 text-emerald-700' => $status === 'online',
                                        'bg-amber-100 text-amber-700' => $status === 'degraded',
                                        'bg-red-100 text-red-700' => $status === 'offline',
                                        'bg-slate-200 text-slate-700' => $status === 'unknown',
                                    ])>
                                        <span class="h-2.5 w-2.5 rounded-full {{ $status === 'online' ? 'bg-emerald-500' : ($status === 'offline' ? 'bg-red-500' : ($status === 'degraded' ? 'bg-amber-500' : 'bg-slate-500')) }}"></span>
                                        {{ ucfirst($status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-slate-600">{{ $site->cameras_count }}</td>
                                @if (auth()->user()?->canManageSites())
                                    <td class="px-6 py-4">
                                        <a href="{{ route('sites.edit', $site) }}" class="btn-secondary">Edit site</a>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-layouts.app>
