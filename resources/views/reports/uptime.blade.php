<x-layouts.app
    :title="'Uptime report | '.config('app.name')"
    heading="Uptime report"
    subheading="Availability reporting for council-owned, client-owned, site, organisation, and connectivity filtered camera estates."
>
    @include('reports.partials.filters', [
        'action' => 'reports.uptime',
        'showOwnership' => true,
        'showConnectivity' => true,
    ])

    <section class="panel overflow-hidden">
        <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="section-title">Camera availability</h2>
                <p class="text-sm text-slate-500">{{ $rows->count() }} cameras in this report.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('reports.uptime.export', [...request()->query(), 'format' => 'csv']) }}" class="btn-secondary">Export CSV</a>
                <a href="{{ route('reports.uptime.export', [...request()->query(), 'format' => 'pdf']) }}" class="btn-primary">Export PDF</a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-slate-500">
                    <tr>
                        <th class="px-5 py-3 font-semibold">Camera</th>
                        <th class="px-5 py-3 font-semibold">Organisation</th>
                        <th class="px-5 py-3 font-semibold">Site</th>
                        <th class="px-5 py-3 font-semibold">Connectivity</th>
                        <th class="px-5 py-3 font-semibold">Total</th>
                        <th class="px-5 py-3 font-semibold">Online</th>
                        <th class="px-5 py-3 font-semibold">Offline</th>
                        <th class="px-5 py-3 font-semibold">Uptime</th>
                        <th class="px-5 py-3 font-semibold">Incidents</th>
                        <th class="px-5 py-3 font-semibold">Longest offline</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($rows as $row)
                        <tr>
                            <td class="px-5 py-4">
                                <a href="{{ route('cameras.show', $row['camera_id']) }}" class="font-semibold text-slate-950 hover:text-brand-700">{{ $row['camera'] }}</a>
                                <p class="mt-1 text-xs text-slate-500">{{ $row['data_quality'] }}</p>
                            </td>
                            <td class="px-5 py-4 text-slate-600">{{ $row['organisation'] }}</td>
                            <td class="px-5 py-4 text-slate-600">{{ $row['site'] }}</td>
                            <td class="px-5 py-4 text-slate-600">{{ $row['connectivity_type'] }}</td>
                            <td class="px-5 py-4 font-semibold text-slate-900">{{ $row['total_monitored_time'] }}</td>
                            <td class="px-5 py-4 text-emerald-700">{{ $row['online_time'] }}</td>
                            <td class="px-5 py-4 text-red-700">{{ $row['offline_time'] }}</td>
                            <td class="px-5 py-4">
                                <span @class([
                                    'rounded-md px-2.5 py-1 text-xs font-semibold',
                                    'bg-emerald-100 text-emerald-700' => $row['uptime_percentage'] >= 99,
                                    'bg-amber-100 text-amber-700' => $row['uptime_percentage'] < 99 && $row['uptime_percentage'] >= 90,
                                    'bg-red-100 text-red-700' => $row['uptime_percentage'] < 90,
                                ])>{{ number_format($row['uptime_percentage'], 2) }}%</span>
                            </td>
                            <td class="px-5 py-4 text-slate-600">{{ $row['offline_incidents'] }}</td>
                            <td class="px-5 py-4 text-slate-600">{{ $row['longest_offline_period'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-5 py-8 text-center text-sm text-slate-500">No cameras matched the selected filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-layouts.app>
