<x-layouts.app
    :title="'Site summary report | '.config('app.name')"
    heading="Site summary report"
    subheading="Grouped site health, camera counts, latest activity, and connectivity mix across the filtered estate."
>
    @include('reports.partials.filters', [
        'action' => 'reports.sites',
        'showOwnership' => true,
        'showConnectivity' => true,
    ])

    <section class="panel overflow-hidden">
        <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="section-title">Sites</h2>
                <p class="text-sm text-slate-500">{{ $rows->count() }} sites in this summary.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('reports.sites.export', [...request()->query(), 'format' => 'csv']) }}" class="btn-secondary">Export CSV</a>
                <a href="{{ route('reports.sites.export', [...request()->query(), 'format' => 'pdf']) }}" class="btn-primary">Export PDF</a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-slate-500">
                    <tr>
                        <th class="px-5 py-3 font-semibold">Site</th>
                        <th class="px-5 py-3 font-semibold">Organisation</th>
                        <th class="px-5 py-3 font-semibold">Cameras</th>
                        <th class="px-5 py-3 font-semibold">Online</th>
                        <th class="px-5 py-3 font-semibold">Offline</th>
                        <th class="px-5 py-3 font-semibold">Unknown</th>
                        <th class="px-5 py-3 font-semibold">Site status</th>
                        <th class="px-5 py-3 font-semibold">Last event</th>
                        <th class="px-5 py-3 font-semibold">Connectivity</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($rows as $row)
                        <tr>
                            <td class="px-5 py-4 font-semibold text-slate-950">{{ $row['site'] }}</td>
                            <td class="px-5 py-4 text-slate-600">{{ $row['organisation'] }}</td>
                            <td class="px-5 py-4 text-slate-600">{{ $row['total_cameras'] }}</td>
                            <td class="px-5 py-4 text-emerald-700">{{ $row['online_cameras'] }}</td>
                            <td class="px-5 py-4 text-red-700">{{ $row['offline_cameras'] }}</td>
                            <td class="px-5 py-4 text-slate-600">{{ $row['unknown_cameras'] }}</td>
                            <td class="px-5 py-4">
                                <span @class([
                                    'status-pill',
                                    'bg-emerald-100 text-emerald-700' => $row['site_status'] === 'online',
                                    'bg-amber-100 text-amber-700' => $row['site_status'] === 'degraded',
                                    'bg-red-100 text-red-700' => $row['site_status'] === 'offline',
                                    'bg-slate-200 text-slate-700' => $row['site_status'] === 'unknown',
                                ])>{{ ucfirst($row['site_status']) }}</span>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-slate-600">{{ $row['last_event_time_display'] }}</td>
                            <td class="min-w-64 px-5 py-4 text-slate-600">{{ $row['connectivity_summary'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-5 py-8 text-center text-sm text-slate-500">No sites matched the selected filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-layouts.app>
