<x-layouts.app
    :title="'Client report | '.config('app.name')"
    heading="Client report"
    subheading="Client-owned camera performance grouped by organisation, with uptime, incidents, sites, cameras, and latest events."
>
    @include('reports.partials.filters', [
        'action' => 'reports.clients',
        'showConnectivity' => true,
    ])

    <section class="panel overflow-hidden">
        <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="section-title">Client-owned cameras</h2>
                <p class="text-sm text-slate-500">{{ $rows->count() }} client organisations in this report.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('reports.clients.export', [...request()->query(), 'format' => 'csv']) }}" class="btn-secondary">Export CSV</a>
                <a href="{{ route('reports.clients.export', [...request()->query(), 'format' => 'pdf']) }}" class="btn-primary">Export PDF</a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-slate-500">
                    <tr>
                        <th class="px-5 py-3 font-semibold">Client</th>
                        <th class="px-5 py-3 font-semibold">Sites</th>
                        <th class="px-5 py-3 font-semibold">Cameras</th>
                        <th class="px-5 py-3 font-semibold">Uptime</th>
                        <th class="px-5 py-3 font-semibold">Incidents</th>
                        <th class="px-5 py-3 font-semibold">Latest event</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($rows as $row)
                        <tr>
                            <td class="px-5 py-4 font-semibold text-slate-950">{{ $row['client_name'] }}</td>
                            <td class="min-w-64 px-5 py-4 text-slate-600">{{ $row['sites'] ?: 'No sites' }}</td>
                            <td class="min-w-64 px-5 py-4 text-slate-600">
                                <p>{{ $row['camera_count'] }} cameras</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $row['cameras'] }}</p>
                            </td>
                            <td class="px-5 py-4">
                                <span @class([
                                    'rounded-md px-2.5 py-1 text-xs font-semibold',
                                    'bg-emerald-100 text-emerald-700' => $row['uptime_percentage'] >= 99,
                                    'bg-amber-100 text-amber-700' => $row['uptime_percentage'] < 99 && $row['uptime_percentage'] >= 90,
                                    'bg-red-100 text-red-700' => $row['uptime_percentage'] < 90,
                                ])>{{ number_format($row['uptime_percentage'], 2) }}%</span>
                            </td>
                            <td class="px-5 py-4 text-slate-600">{{ $row['incidents'] }}</td>
                            <td class="px-5 py-4 text-slate-600">
                                <p class="font-semibold text-slate-900">{{ $row['latest_event'] }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $row['latest_event_time'] }}</p>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-sm text-slate-500">No client-owned cameras matched the selected filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-layouts.app>
