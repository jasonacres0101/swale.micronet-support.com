@php
    $status = $camera->status ?: ($camera->is_online ? 'online' : 'unknown');
    $statusPalette = [
        'online' => [
            'pill' => 'bg-emerald-100 text-emerald-700',
            'dot' => 'bg-emerald-500',
            'panel' => 'from-emerald-500/20 to-emerald-400/5 border-emerald-300/30',
        ],
        'offline' => [
            'pill' => 'bg-red-100 text-red-700',
            'dot' => 'bg-red-500',
            'panel' => 'from-red-500/20 to-red-400/5 border-red-300/30',
        ],
        'unknown' => [
            'pill' => 'bg-amber-100 text-amber-700',
            'dot' => 'bg-amber-500',
            'panel' => 'from-amber-500/20 to-amber-400/5 border-amber-300/30',
        ],
    ][$status] ?? [
        'pill' => 'bg-slate-100 text-slate-700',
        'dot' => 'bg-slate-400',
        'panel' => 'from-slate-500/20 to-slate-400/5 border-slate-300/30',
    ];
    $organisationName = $camera->site?->organisation?->name ?: 'Unassigned organisation';
    $siteName = $camera->site?->name ?: $camera->site_name;
    $connectivityType = str($camera->connectivity_type ?: 'unknown')->replace('_', ' ')->title();
    $latestEvent = $camera->latestHikvisionEvent;
    $latestSnapshotUrl = $latestEmailSnapshot?->attachmentUrl();
@endphp

<x-layouts.app
    :title="$camera->name.' | '.config('app.name')"
    :heading="$camera->name"
    :subheading="$organisationName.' · '.$siteName.' · '.$camera->location_name"
>
    <div id="camera-live-warning" class="mb-4 hidden rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
        Live update connection issue
    </div>

    <section class="panel overflow-hidden">
        <div class="grid lg:grid-cols-[minmax(0,1.1fr)_24rem]">
            <div class="relative overflow-hidden bg-brand-900 px-5 py-5 text-white sm:px-6">
                <div class="absolute -right-16 -top-24 h-56 w-56 rounded-full bg-brand-400/20 blur-3xl"></div>
                <div class="absolute -bottom-28 left-1/3 h-64 w-64 rounded-full bg-white/10 blur-3xl"></div>

                <div class="relative flex flex-wrap items-center gap-2">
                    <span id="camera-status-pill" class="status-pill {{ $statusPalette['pill'] }}">
                        <span id="camera-status-dot" class="h-2.5 w-2.5 rounded-full {{ $statusPalette['dot'] }}"></span>
                        <span id="camera-status-label">{{ ucfirst($status) }}</span>
                    </span>
                    <span class="rounded-md border border-white/15 bg-white/10 px-2.5 py-1 text-xs font-semibold uppercase tracking-wide text-brand-50">
                        {{ ucfirst($camera->ownership_type ?: 'council') }} owned
                    </span>
                    <span class="rounded-md border border-white/15 bg-white/10 px-2.5 py-1 text-xs font-semibold uppercase tracking-wide text-brand-50">
                        {{ $camera->managed_by_council ? 'Council managed' : 'Client managed' }}
                    </span>
                </div>

                <div class="relative mt-6 max-w-3xl">
                    <p class="text-xs font-semibold uppercase tracking-wide text-brand-100">Camera overview</p>
                    <p class="mt-3 text-2xl font-semibold tracking-tight text-white sm:text-3xl">{{ $camera->location_name }}</p>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-brand-50/85">
                        {{ $camera->description ?: 'No camera description has been added yet.' }}
                    </p>
                </div>

                <div id="camera-overview-latest-snapshot" class="relative mt-6 max-w-md">
                    @if ($latestSnapshotUrl)
                        <a href="{{ $latestSnapshotUrl }}" target="_blank" rel="noreferrer" class="block overflow-hidden rounded-lg border border-white/10 bg-white/10">
                            <img src="{{ $latestSnapshotUrl }}" alt="Latest screenshot from {{ optional($latestEmailSnapshot->received_at)->format('d M Y H:i') ?? $camera->name }}" class="aspect-video w-full object-cover">
                            <div class="flex flex-col gap-1 border-t border-white/10 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                                <span class="text-xs font-semibold uppercase tracking-wide text-brand-100">Latest screenshot</span>
                                <span class="text-sm font-semibold text-white">{{ optional($latestEmailSnapshot->received_at)->format('d M Y H:i') ?? 'Unknown time' }}</span>
                            </div>
                        </a>
                    @else
                        <div class="rounded-lg border border-white/10 bg-white/10 px-4 py-5 text-sm font-semibold text-brand-50">
                            No latest screenshot has been imported yet.
                        </div>
                    @endif
                </div>

                <div class="relative mt-6 grid gap-3 md:grid-cols-3">
                    <div class="rounded-lg border border-white/10 bg-white/10 p-4 backdrop-blur">
                        <p class="text-xs font-semibold uppercase tracking-wide text-brand-100">Last seen</p>
                        <p id="camera-last-seen" class="mt-2 text-sm font-semibold text-white">{{ optional($camera->last_seen_at)->format('d M Y H:i') ?? 'Never' }}</p>
                    </div>
                    <div class="rounded-lg border border-white/10 bg-white/10 p-4 backdrop-blur">
                        <p class="text-xs font-semibold uppercase tracking-wide text-brand-100">Last event</p>
                        <p id="camera-last-event" class="mt-2 text-sm font-semibold text-white">{{ optional($camera->last_event_at)->format('d M Y H:i') ?? 'Never' }}</p>
                    </div>
                    <div class="rounded-lg border border-white/10 bg-white/10 p-4 backdrop-blur">
                        <p class="text-xs font-semibold uppercase tracking-wide text-brand-100">Live IP</p>
                        <p id="camera-ip-address" class="mt-2 text-sm font-semibold text-white">{{ $camera->ip_address ?: 'Not set' }}</p>
                    </div>
                </div>
            </div>

            <div class="flex flex-col justify-between gap-5 border-t border-slate-200 bg-white p-5 lg:border-l lg:border-t-0">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Quick access</p>
                    <div class="mt-3 grid gap-2 sm:grid-cols-3 lg:grid-cols-1">
                        @if (auth()->user()?->canUpdateCamera($camera))
                            <a href="{{ route('cameras.edit', $camera) }}" class="btn-primary">Edit camera</a>
                        @endif
                        <a href="{{ $camera->web_ui_url }}" target="_blank" rel="noreferrer" class="btn-secondary">Open web UI</a>
                        @if (auth()->user()?->canUpdateCamera($camera))
                            <button type="button" data-psa-placeholder class="btn-secondary">Create PSA ticket</button>
                        @endif
                    </div>
                </div>

                <div class="rounded-lg border bg-gradient-to-br {{ $statusPalette['panel'] }} p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Device identity</p>
                    <div class="mt-4 grid grid-cols-2 gap-3">
                        <div class="rounded-lg bg-white/80 p-3">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Status</p>
                            <p id="camera-status-text" class="mt-1 text-sm font-semibold text-slate-950">{{ ucfirst($status) }}</p>
                        </div>
                        <div class="rounded-lg bg-white/80 p-3">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">MAC</p>
                            <p id="camera-mac-address" class="mt-1 truncate text-sm font-semibold text-slate-950">{{ $camera->mac_address ?: 'Not set' }}</p>
                        </div>
                        <div class="col-span-2 rounded-lg bg-white/80 p-3">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Normalized MAC</p>
                            <p class="mt-1 break-all text-sm font-semibold text-slate-950">{{ $camera->mac_address_normalized ?: 'Not set' }}</p>
                        </div>
                        <div class="col-span-2 rounded-lg bg-white/80 p-3">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Serial number</p>
                            <p class="mt-1 break-all text-sm font-semibold text-slate-950">{{ $camera->serial_number ?: 'Not set' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="mt-4 grid gap-4 xl:grid-cols-[minmax(0,1fr)_22rem]">
        <main class="space-y-4">
            <div class="grid gap-4 lg:grid-cols-[0.95fr_1.05fr]">
                <section class="panel p-5">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Estate context</p>
                            <h2 class="mt-1 text-lg font-semibold text-slate-950">Client and site</h2>
                        </div>
                        <span class="rounded-md bg-brand-50 px-2.5 py-1 text-xs font-semibold uppercase tracking-wide text-brand-700">
                            {{ $camera->site?->organisation?->type ? ucfirst($camera->site->organisation->type) : 'Unassigned' }}
                        </span>
                    </div>

                    <div class="mt-5 grid gap-3 sm:grid-cols-2">
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Organisation</p>
                            <p class="mt-2 text-base font-semibold text-slate-950">{{ $organisationName }}</p>
                        </div>
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Site</p>
                            <p class="mt-2 text-base font-semibold text-slate-950">{{ $siteName }}</p>
                        </div>
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Location</p>
                            <p class="mt-2 text-base font-semibold text-slate-950">{{ $camera->location_name }}</p>
                        </div>
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Ownership</p>
                            <p class="mt-2 text-base font-semibold text-slate-950">{{ ucfirst($camera->ownership_type ?: 'council') }}</p>
                        </div>
                    </div>
                </section>

                <section class="panel overflow-hidden">
                    <div class="border-b border-slate-200 bg-slate-50 px-5 py-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Latest Hikvision event</p>
                        <h2 id="camera-latest-event-type" class="mt-1 text-xl font-semibold text-slate-950">{{ $latestEvent?->event_type ?: 'No events yet' }}</h2>
                    </div>

                    <div class="grid gap-3 p-5 sm:grid-cols-3">
                        <div class="rounded-lg border border-slate-200 p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">State</p>
                            <p id="camera-latest-event-state" class="mt-2 text-base font-semibold text-slate-950">{{ $latestEvent?->event_state ?: 'Unknown' }}</p>
                        </div>
                        <div class="rounded-lg border border-slate-200 p-4 sm:col-span-2">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Event time</p>
                            <p id="camera-latest-event-time" class="mt-2 text-base font-semibold text-slate-950">{{ optional($latestEvent?->event_time)->format('d M Y H:i:s') ?? 'Never' }}</p>
                        </div>
                    </div>

                    <div class="px-5 pb-5">
                        <div class="rounded-lg bg-brand-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-brand-700">Description</p>
                            <p id="camera-latest-event-description" class="mt-2 text-sm leading-6 text-slate-700">{{ $latestEvent?->event_description ?: 'No event description available.' }}</p>
                        </div>
                    </div>
                </section>
            </div>

            <section class="panel p-5">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Connectivity</p>
                        <h2 class="mt-1 text-lg font-semibold text-slate-950">{{ $connectivityType }}{{ $camera->connectivity_provider ? ' · '.$camera->connectivity_provider : '' }}</h2>
                    </div>
                    <span class="inline-flex rounded-md bg-slate-100 px-2.5 py-1 text-xs font-semibold uppercase tracking-wide text-slate-600">
                        {{ $camera->private_apn ? 'Private APN' : 'Standard network' }}
                    </span>
                </div>

                <div class="mt-5 grid gap-3 md:grid-cols-3">
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Provider</p>
                        <p class="mt-2 text-sm font-semibold text-slate-950">{{ $camera->connectivity_provider ?: 'Not set' }}</p>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">SIM number</p>
                        <p class="mt-2 break-all text-sm font-semibold text-slate-950">{{ $camera->sim_number ?: 'Not set' }}</p>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">ICCID</p>
                        <p class="mt-2 break-all text-sm font-semibold text-slate-950">{{ $camera->sim_iccid ?: 'Not set' }}</p>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Static IP</p>
                        <p class="mt-2 text-sm font-semibold text-slate-950">{{ $camera->sim_static_ip ?: 'Not set' }}</p>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">APN</p>
                        <p class="mt-2 text-sm font-semibold text-slate-950">{{ $camera->apn_name ?: 'Not set' }}</p>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Router</p>
                        <p class="mt-2 text-sm font-semibold text-slate-950">{{ $camera->router_model ?: 'Not set' }}{{ $camera->router_serial ? ' · '.$camera->router_serial : '' }}</p>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Router IP</p>
                        <p class="mt-2 text-sm font-semibold text-slate-950">{{ $camera->router_ip_address ?: 'Not set' }}</p>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">WAN IP</p>
                        <p class="mt-2 text-sm font-semibold text-slate-950">{{ $camera->wan_ip_address ?: 'Not set' }}</p>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4 md:col-span-1">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Notes</p>
                        <p class="mt-2 text-sm leading-6 text-slate-700">{{ $camera->connectivity_notes ?: 'No connectivity notes recorded.' }}</p>
                    </div>
                </div>
            </section>

            <section class="panel overflow-hidden">
                <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Maintenance</p>
                        <h2 class="mt-1 text-lg font-semibold text-slate-950">Service schedule</h2>
                    </div>
                    <a href="{{ route('maintenance.index', ['camera' => $camera->id]) }}" class="btn-secondary">View all maintenance</a>
                </div>

                <div class="grid gap-4 p-5 lg:grid-cols-3">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-950">Upcoming</h3>
                        <div class="mt-3 space-y-2">
                            @forelse ($upcomingMaintenance as $task)
                                <a href="{{ route('maintenance.show', $task) }}" class="block rounded-lg border border-slate-200 bg-slate-50 p-3 text-sm hover:border-brand-200 hover:bg-white">
                                    <p class="font-semibold text-slate-900">{{ $task->title }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $task->statusLabel() }} · {{ optional($task->due_at)->format('d M Y') ?? 'No due date' }}</p>
                                </a>
                            @empty
                                <p class="rounded-lg bg-slate-50 p-3 text-sm text-slate-600">No upcoming tasks.</p>
                            @endforelse
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-semibold text-slate-950">Latest completed</h3>
                        <div class="mt-3 space-y-2">
                            @forelse ($latestCompletedMaintenance as $task)
                                <a href="{{ route('maintenance.show', $task) }}" class="block rounded-lg border border-slate-200 bg-slate-50 p-3 text-sm hover:border-brand-200 hover:bg-white">
                                    <p class="font-semibold text-slate-900">{{ $task->title }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ optional($task->completed_at)->format('d M Y') ?? 'Completed' }}</p>
                                </a>
                            @empty
                                <p class="rounded-lg bg-slate-50 p-3 text-sm text-slate-600">No completed tasks.</p>
                            @endforelse
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-semibold text-slate-950">Annual reports</h3>
                        <div class="mt-3 space-y-2">
                            @forelse ($annualServiceReports as $task)
                                <a href="{{ route('maintenance.show', $task) }}" class="block rounded-lg border border-slate-200 bg-slate-50 p-3 text-sm hover:border-brand-200 hover:bg-white">
                                    <p class="font-semibold text-slate-900">{{ $task->title }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $task->statusLabel() }} · {{ optional($task->due_at)->format('d M Y') ?? 'No due date' }}</p>
                                </a>
                            @empty
                                <p class="rounded-lg bg-slate-50 p-3 text-sm text-slate-600">No annual service history.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </section>

            <section class="panel overflow-hidden">
                <div class="border-b border-slate-200 px-5 py-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Email snapshots</p>
                    <div class="mt-1 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <h2 class="text-lg font-semibold text-slate-950">Latest camera screenshots</h2>
                        <p id="camera-snapshots-refresh" class="text-xs font-semibold text-slate-500">Auto-refreshing</p>
                    </div>
                </div>

                <div id="camera-snapshots-grid" class="grid gap-4 p-5 md:grid-cols-2 xl:grid-cols-3">
                    @forelse ($latestEmailSnapshots as $snapshot)
                        <article class="overflow-hidden rounded-lg border border-slate-200 bg-slate-50">
                            @if ($snapshot->attachmentUrl())
                                <a href="{{ $snapshot->attachmentUrl() }}" target="_blank" rel="noreferrer" class="block bg-slate-200">
                                    <img src="{{ $snapshot->attachmentUrl() }}" alt="Camera snapshot from {{ optional($snapshot->received_at)->format('d M Y H:i') ?? 'email' }}" class="aspect-video w-full object-cover">
                                </a>
                            @else
                                <div class="flex aspect-video items-center justify-center bg-slate-200 text-sm font-semibold text-slate-500">
                                    No image attachment
                                </div>
                            @endif

                            <div class="space-y-2 p-4 text-sm">
                                <p class="font-semibold text-slate-950">{{ optional($snapshot->received_at)->format('d M Y H:i') ?? 'Unknown time' }}</p>
                                <p class="truncate text-slate-600">{{ $snapshot->subject ?: 'No subject' }}</p>
                                <p class="truncate text-xs text-slate-500">{{ $snapshot->from_email ?: 'Unknown sender' }}</p>
                            </div>
                        </article>
                    @empty
                        <p id="camera-snapshots-empty" class="rounded-lg bg-slate-50 px-4 py-5 text-sm text-slate-600 md:col-span-2 xl:col-span-3">No snapshot emails have been imported for this camera yet.</p>
                    @endforelse
                </div>
            </section>
        </main>

        <aside class="space-y-4">
            <section class="panel overflow-hidden">
                <div class="bg-brand-900 p-5 text-white">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-brand-100">Location</p>
                            <h2 class="mt-1 text-lg font-semibold">Map position</h2>
                        </div>
                        <a href="{{ route('cameras.map', ['camera' => $camera->id]) }}" class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-brand-800 transition hover:bg-brand-50">Open full map</a>
                    </div>

                    @if (filled($camera->latitude) && filled($camera->longitude))
                        <div class="mt-5 rounded-lg border border-white/10 bg-white/10 p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-brand-100">Coordinates</p>
                            <p class="mt-2 text-lg font-semibold">{{ number_format($camera->latitude, 5) }}, {{ number_format($camera->longitude, 5) }}</p>
                        </div>
                    @else
                        <div class="mt-5 rounded-lg border border-white/10 bg-white/10 p-4">
                            <p class="text-sm font-semibold">Add coordinates on the edit page to place this camera on the map.</p>
                        </div>
                    @endif
                </div>

                <div class="space-y-3 p-5">
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">what3words</p>
                        <p class="mt-2 text-sm font-semibold text-slate-950">
                            @if ($camera->what3words)
                                <a href="{{ 'https://what3words.com/'.strtolower($camera->what3words) }}" target="_blank" rel="noreferrer" class="text-brand-700 hover:text-brand-800 hover:underline">
                                    {{ strtolower($camera->what3words) }}
                                </a>
                            @else
                                Not set
                            @endif
                        </p>
                    </div>

                    @if ($camera->site)
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Site notes</p>
                            <p class="mt-2 text-sm leading-6 text-slate-700">{{ $camera->site->notes ?: 'No site notes recorded.' }}</p>
                        </div>
                    @endif
                </div>
            </section>

            <section class="panel p-5">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Same site</p>
                        <h2 class="mt-1 text-lg font-semibold text-slate-950">Nearby cameras</h2>
                    </div>
                    <span class="rounded-md bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">{{ $nearbyCameras->count() }}</span>
                </div>

                <div class="mt-4 grid gap-2">
                    @forelse ($nearbyCameras as $nearbyCamera)
                        <a href="{{ route('cameras.show', $nearbyCamera) }}" class="group rounded-lg border border-slate-200 bg-slate-50 px-3 py-3 transition hover:border-brand-200 hover:bg-white">
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate font-semibold text-slate-900 group-hover:text-brand-700">{{ $nearbyCamera->name }}</p>
                                    <p class="truncate text-sm text-slate-500">{{ $nearbyCamera->location_name }}</p>
                                </div>
                                <span class="h-2.5 w-2.5 shrink-0 rounded-full {{ $nearbyCamera->is_online ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                            </div>
                        </a>
                    @empty
                        <p class="rounded-lg bg-slate-50 px-4 py-4 text-sm text-slate-600">No additional cameras found at this site.</p>
                    @endforelse
                </div>
            </section>
        </aside>
    </div>

    @push('scripts')
        <script>
            (() => {
                const endpoint = @json(route('api.cameras.live-status.show', $camera));
                const snapshotsEndpoint = @json(route('api.cameras.snapshots', $camera));
                const warning = document.getElementById('camera-live-warning');
                const overviewLatestSnapshot = document.getElementById('camera-overview-latest-snapshot');
                const snapshotsGrid = document.getElementById('camera-snapshots-grid');
                const snapshotsRefresh = document.getElementById('camera-snapshots-refresh');
                const statusPill = document.getElementById('camera-status-pill');
                const statusDot = document.getElementById('camera-status-dot');
                const statusLabel = document.getElementById('camera-status-label');
                const statusText = document.getElementById('camera-status-text');
                const ipAddress = document.getElementById('camera-ip-address');
                const macAddress = document.getElementById('camera-mac-address');
                const lastSeen = document.getElementById('camera-last-seen');
                const lastEvent = document.getElementById('camera-last-event');
                const eventType = document.getElementById('camera-latest-event-type');
                const eventState = document.getElementById('camera-latest-event-state');
                const eventTime = document.getElementById('camera-latest-event-time');
                const eventDescription = document.getElementById('camera-latest-event-description');

                const statusClasses = {
                    online: ['bg-emerald-100', 'text-emerald-700', 'bg-emerald-500'],
                    offline: ['bg-red-100', 'text-red-700', 'bg-red-500'],
                    unknown: ['bg-amber-100', 'text-amber-700', 'bg-amber-500'],
                };

                const formatDate = (value) => value ? new Date(value).toLocaleString() : 'Never';
                const escapeHtml = (value) => (value ?? '').toString()
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;')
                    .replaceAll("'", '&#039;');

                const apply = (camera) => {
                    const [pillBg, pillText, dotBg] = statusClasses[camera.status] || statusClasses.unknown;
                    statusPill.className = `status-pill ${pillBg} ${pillText}`;
                    statusDot.className = `h-2.5 w-2.5 rounded-full ${dotBg}`;
                    statusLabel.textContent = camera.status.charAt(0).toUpperCase() + camera.status.slice(1);
                    statusText.textContent = statusLabel.textContent;
                    ipAddress.textContent = camera.ip_address || 'Not set';
                    macAddress.textContent = camera.mac_address || 'Not set';
                    lastSeen.textContent = formatDate(camera.last_seen_at);
                    lastEvent.textContent = formatDate(camera.last_event_at);
                    eventType.textContent = camera.latest_event_type || 'No events yet';
                    eventState.textContent = camera.latest_event_state || 'Unknown';
                    eventTime.textContent = formatDate(camera.latest_event_time);
                    eventDescription.textContent = camera.latest_event_description || 'No event description available.';
                };

                const poll = async () => {
                    try {
                        const response = await fetch(endpoint, {
                            headers: { 'Accept': 'application/json' },
                            credentials: 'same-origin',
                        });

                        if (!response.ok) throw new Error('Polling failed');

                        const camera = await response.json();
                        apply(camera);
                        warning.classList.add('hidden');
                    } catch (error) {
                        warning.classList.remove('hidden');
                    }
                };

                const snapshotCard = (snapshot) => {
                    const image = snapshot.attachment_url
                        ? `<a href="${escapeHtml(snapshot.attachment_url)}" target="_blank" rel="noreferrer" class="block bg-slate-200">
                            <img src="${escapeHtml(snapshot.attachment_url)}" alt="Camera snapshot from ${escapeHtml(snapshot.received_label)}" class="aspect-video w-full object-cover">
                        </a>`
                        : `<div class="flex aspect-video items-center justify-center bg-slate-200 text-sm font-semibold text-slate-500">
                            No image attachment
                        </div>`;

                    return `<article class="overflow-hidden rounded-lg border border-slate-200 bg-slate-50">
                        ${image}
                        <div class="space-y-2 p-4 text-sm">
                            <p class="font-semibold text-slate-950">${escapeHtml(snapshot.received_label)}</p>
                            <p class="truncate text-slate-600">${escapeHtml(snapshot.subject)}</p>
                            <p class="truncate text-xs text-slate-500">${escapeHtml(snapshot.from_email)}</p>
                        </div>
                    </article>`;
                };

                const renderOverviewSnapshot = (snapshot) => {
                    if (!overviewLatestSnapshot) return;

                    if (!snapshot || !snapshot.attachment_url) {
                        overviewLatestSnapshot.innerHTML = `<div class="rounded-lg border border-white/10 bg-white/10 px-4 py-5 text-sm font-semibold text-brand-50">
                            No latest screenshot has been imported yet.
                        </div>`;
                        return;
                    }

                    overviewLatestSnapshot.innerHTML = `<a href="${escapeHtml(snapshot.attachment_url)}" target="_blank" rel="noreferrer" class="block overflow-hidden rounded-lg border border-white/10 bg-white/10">
                        <img src="${escapeHtml(snapshot.attachment_url)}" alt="Latest screenshot from ${escapeHtml(snapshot.received_label)}" class="aspect-video w-full object-cover">
                        <div class="flex flex-col gap-1 border-t border-white/10 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                            <span class="text-xs font-semibold uppercase tracking-wide text-brand-100">Latest screenshot</span>
                            <span class="text-sm font-semibold text-white">${escapeHtml(snapshot.received_label)}</span>
                        </div>
                    </a>`;
                };

                const pollSnapshots = async () => {
                    try {
                        const response = await fetch(snapshotsEndpoint, {
                            headers: { 'Accept': 'application/json' },
                            credentials: 'same-origin',
                        });

                        if (!response.ok) throw new Error('Snapshot polling failed');

                        const data = await response.json();
                        const snapshots = data.snapshots || [];
                        renderOverviewSnapshot(snapshots[0]);
                        snapshotsGrid.innerHTML = snapshots.length
                            ? snapshots.slice(0, 6).map(snapshotCard).join('')
                            : '<p id="camera-snapshots-empty" class="rounded-lg bg-slate-50 px-4 py-5 text-sm text-slate-600 md:col-span-2 xl:col-span-3">No snapshot emails have been imported for this camera yet.</p>';
                        snapshotsRefresh.textContent = `Auto-refreshed ${new Date().toLocaleTimeString()}`;
                    } catch (error) {
                        snapshotsRefresh.textContent = 'Snapshot refresh issue';
                    }
                };

                poll();
                window.setInterval(poll, 5000);
                window.setInterval(pollSnapshots, 30000);
            })();
        </script>
    @endpush
</x-layouts.app>
