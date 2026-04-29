<x-layouts.app
    :title="'Event report | '.config('app.name')"
    heading="Event report"
    subheading="Hikvision alarm and motion event reporting by organisation, site, camera, event type, and date range."
>
    @include('reports.partials.filters', [
        'action' => 'reports.events',
        'showCamera' => true,
        'showEventType' => true,
    ])

    <section class="panel overflow-hidden">
        <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="section-title">Hikvision events</h2>
                <p class="text-sm text-slate-500">{{ $rows->count() }} events found for {{ $range['label'] }}.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('reports.events.export', [...request()->query(), 'format' => 'csv']) }}" class="btn-secondary">Export CSV</a>
                <a href="{{ route('reports.events.export', [...request()->query(), 'format' => 'pdf']) }}" class="btn-primary">Export PDF</a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-slate-500">
                    <tr>
                        <th class="px-5 py-3 font-semibold">Event time</th>
                        <th class="px-5 py-3 font-semibold">Camera</th>
                        <th class="px-5 py-3 font-semibold">Site</th>
                        <th class="px-5 py-3 font-semibold">Organisation</th>
                        <th class="px-5 py-3 font-semibold">Type</th>
                        <th class="px-5 py-3 font-semibold">State</th>
                        <th class="px-5 py-3 font-semibold">Description</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($rows as $row)
                        <tr>
                            <td class="whitespace-nowrap px-5 py-4 font-semibold text-slate-900">{{ $row['event_time_display'] }}</td>
                            <td class="px-5 py-4 text-slate-600">{{ $row['camera'] }}</td>
                            <td class="px-5 py-4 text-slate-600">{{ $row['site'] }}</td>
                            <td class="px-5 py-4 text-slate-600">{{ $row['organisation'] }}</td>
                            <td class="px-5 py-4">
                                <span class="rounded-md bg-brand-50 px-2.5 py-1 text-xs font-semibold text-brand-700">{{ $row['event_type'] }}</span>
                            </td>
                            <td class="px-5 py-4 text-slate-600">{{ $row['event_state'] }}</td>
                            <td class="min-w-72 px-5 py-4 text-slate-600">{{ $row['event_description'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-8 text-center text-sm text-slate-500">No Hikvision events matched the selected filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-layouts.app>
