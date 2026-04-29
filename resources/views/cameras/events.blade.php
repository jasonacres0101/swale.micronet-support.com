<x-layouts.app
    :title="'Alarm Admin | '.config('app.name')"
    heading="Hikvision alarm admin"
    subheading="Browse recent Hikvision events, inspect payload details, and focus on unmatched alarms."
>
    <div class="grid gap-6 xl:grid-cols-[1.35fr_0.85fr]">
        <section class="panel overflow-hidden">
            <div class="border-b border-slate-200 px-6 py-5">
                <h2 class="text-xl font-bold text-slate-950">Recent Hikvision events</h2>
                <p class="mt-1 text-sm text-slate-500">Newest events first, including camera match results and raw source metadata.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50/90 text-left text-slate-500">
                        <tr>
                            <th class="px-6 py-4 font-semibold">Time</th>
                            <th class="px-6 py-4 font-semibold">Camera</th>
                            <th class="px-6 py-4 font-semibold">Event</th>
                            <th class="px-6 py-4 font-semibold">Source</th>
                            <th class="px-6 py-4 font-semibold">Details</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($recentEvents as $event)
                            <tr class="bg-white/70 align-top">
                                <td class="px-6 py-4 text-slate-600">
                                    {{ optional($event->event_time)->format('d M Y H:i:s') ?? $event->created_at->format('d M Y H:i:s') }}
                                </td>
                                <td class="px-6 py-4">
                                    @if ($event->camera)
                                        <a href="{{ route('cameras.show', $event->camera) }}" class="font-semibold text-slate-900 hover:text-brand-700">
                                            {{ $event->camera->name }}
                                        </a>
                                        <p class="mt-1 text-xs text-slate-500">{{ $event->camera->site_name }}</p>
                                    @else
                                        <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-amber-700">
                                            Unmatched
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <p class="font-semibold text-slate-900">{{ $event->event_type ?: 'Unknown type' }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $event->event_state ?: 'Unknown state' }}</p>
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-600">
                                    <p>Payload IP: {{ $event->ip_address ?: 'N/A' }}</p>
                                    <p class="mt-1">Source IP: {{ $event->source_ip ?: 'N/A' }}</p>
                                    <p class="mt-1">MAC: {{ $event->mac_address ?: 'N/A' }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="max-w-sm text-sm text-slate-600">{{ $event->event_description ?: 'No description provided.' }}</p>
                                    @if ($event->parsed_payload)
                                        <details class="mt-3 rounded-lg bg-slate-50 p-3">
                                            <summary class="cursor-pointer text-xs font-semibold uppercase tracking-wide text-slate-500">Parsed payload</summary>
                                            <pre class="mt-3 overflow-x-auto text-xs text-slate-700">{{ json_encode($event->parsed_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                        </details>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-sm text-slate-500">No Hikvision events have been received yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <aside class="space-y-6">
            <section class="panel p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-slate-950">Unmatched alarms</h2>
                        <p class="text-sm text-slate-500">Events that could not be matched by MAC, payload IP, or source IP.</p>
                    </div>
                    <span class="rounded-full bg-amber-100 px-3 py-1 text-sm font-semibold text-amber-700">{{ $unmatchedEvents->count() }}</span>
                </div>

                <div class="mt-5 space-y-3">
                    @forelse ($unmatchedEvents as $event)
                        <article class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $event->event_type ?: 'Unknown event' }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ optional($event->event_time)->format('d M Y H:i:s') ?? $event->created_at->format('d M Y H:i:s') }}</p>
                                </div>
                                <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold uppercase tracking-wide text-amber-700">
                                    Unmatched
                                </span>
                            </div>

                            <div class="mt-3 space-y-1 text-sm text-slate-600">
                                <p>MAC: {{ $event->mac_address ?: 'N/A' }}</p>
                                <p>Payload IP: {{ $event->ip_address ?: 'N/A' }}</p>
                                <p>Source IP: {{ $event->source_ip ?: 'N/A' }}</p>
                                <p>State: {{ $event->event_state ?: 'Unknown' }}</p>
                            </div>

                            @if ($event->event_description)
                                <p class="mt-3 text-sm text-slate-700">{{ $event->event_description }}</p>
                            @endif
                        </article>
                    @empty
                        <p class="rounded-lg bg-emerald-50 px-4 py-5 text-sm text-emerald-700">No unmatched Hikvision alarms right now.</p>
                    @endforelse
                </div>
            </section>

            <section class="panel p-6">
                <h2 class="text-xl font-bold text-slate-950">Matching rules</h2>
                <div class="mt-4 space-y-3 text-sm text-slate-600">
                    <p class="rounded-lg bg-slate-50 px-4 py-4">1. Match by normalized MAC address.</p>
                    <p class="rounded-lg bg-slate-50 px-4 py-4">2. Fallback to payload IP address.</p>
                    <p class="rounded-lg bg-slate-50 px-4 py-4">3. Fallback to request source IP.</p>
                </div>
            </section>
        </aside>
    </div>
</x-layouts.app>
