<x-layouts.app
    :title="'Cameras | '.config('app.name')"
    heading="Camera list"
    subheading="Review every monitored endpoint, open the camera web UI, and jump into deeper details."
>
    @include('partials.camera-filters', ['action' => route('cameras.index')])

    <section class="panel overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-200 px-6 py-5">
            <div>
                <h2 class="text-xl font-bold text-slate-950">Camera inventory</h2>
                <p class="text-sm text-slate-500">Review live devices and camera connectivity information.</p>
            </div>
            @if (auth()->user()?->canCreateCameras())
                <a href="{{ route('cameras.create') }}" class="btn-primary">Add camera</a>
            @endif
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50/90 text-left text-slate-500">
                    <tr>
                        <th class="px-6 py-4 font-semibold">Camera</th>
                        <th class="px-6 py-4 font-semibold">Organisation / site</th>
                        <th class="px-6 py-4 font-semibold">Location</th>
                        <th class="px-6 py-4 font-semibold">IP</th>
                        <th class="px-6 py-4 font-semibold">Ownership</th>
                        <th class="px-6 py-4 font-semibold">Status</th>
                        <th class="px-6 py-4 font-semibold">Last seen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($cameras as $camera)
                        <tr class="bg-white/70">
                            <td class="px-6 py-4">
                                <a href="{{ route('cameras.show', $camera) }}" class="font-semibold text-slate-900 hover:text-brand-700">
                                    {{ $camera->name }}
                                </a>
                                <p class="mt-1 text-xs text-slate-500">{{ $camera->latestHikvisionEvent?->event_type ? $camera->latestHikvisionEvent->event_type.' · '.($camera->latestHikvisionEvent->event_state ?: 'Unknown') : 'No events yet' }}</p>
                            </td>
                            <td class="px-6 py-4 text-slate-600">
                                <p class="font-medium text-slate-900">{{ $camera->site?->organisation?->name ?: 'Unassigned organisation' }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $camera->site?->name ?: $camera->site_name }}</p>
                            </td>
                            <td class="px-6 py-4 text-slate-600">{{ $camera->location_name }}</td>
                            <td class="px-6 py-4 font-mono text-xs text-slate-600">{{ $camera->ip_address }}</td>
                            <td class="px-6 py-4 text-slate-600">
                                <p>{{ ucfirst($camera->ownership_type ?: 'council') }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $camera->managed_by_council ? 'Council managed' : 'Externally managed' }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span @class([
                                    'status-pill',
                                    'bg-emerald-100 text-emerald-700' => ($camera->status ?: ($camera->is_online ? 'online' : 'unknown')) === 'online',
                                    'bg-red-100 text-red-700' => ($camera->status ?: ($camera->is_online ? 'online' : 'unknown')) === 'offline',
                                    'bg-slate-200 text-slate-700' => ($camera->status ?: ($camera->is_online ? 'online' : 'unknown')) === 'unknown',
                                ])>
                                    <span class="h-2.5 w-2.5 rounded-full {{ ($camera->status ?: ($camera->is_online ? 'online' : 'unknown')) === 'online' ? 'bg-emerald-500' : (($camera->status ?: ($camera->is_online ? 'online' : 'unknown')) === 'offline' ? 'bg-red-500' : 'bg-slate-500') }}"></span>
                                    {{ ucfirst($camera->status ?: ($camera->is_online ? 'online' : 'unknown')) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-600">{{ optional($camera->last_seen_at)->diffForHumans() ?? 'Never' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</x-layouts.app>
