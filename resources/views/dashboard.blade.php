<x-layouts.app
    :title="'Dashboard | '.config('app.name')"
    heading="Monitoring dashboard"
    subheading="Live site health, grouped camera coverage, and operational visibility across council and client estates."
>
    <div id="dashboard-live-warning" class="mb-6 hidden rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
        Live update connection issue
    </div>

    @include('partials.camera-filters', ['action' => route('dashboard')])

    <div class="grid gap-6 lg:grid-cols-[1.4fr_0.9fr]">
        <section class="space-y-6">
            <div class="grid gap-3 sm:grid-cols-4">
                <article class="metric-card">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total cameras</p>
                    <p id="dashboard-total-count" class="mt-2 text-3xl font-semibold text-slate-950">{{ $cameras->count() }}</p>
                </article>
                <article class="metric-card">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Sites in view</p>
                    <p id="dashboard-site-count" class="mt-2 text-3xl font-semibold text-brand-700">{{ $siteCount }}</p>
                </article>
                <article class="metric-card">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Online</p>
                    <p id="dashboard-online-count" class="mt-2 text-3xl font-semibold text-emerald-600">{{ $onlineCount }}</p>
                </article>
                <article class="metric-card">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Offline</p>
                    <p id="dashboard-offline-count" class="mt-2 text-3xl font-semibold text-red-600">{{ $offlineCount }}</p>
                </article>
            </div>

            <section class="panel overflow-hidden">
                <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                    <div>
                        <h2 class="section-title">Coverage by site</h2>
                        <p class="text-sm text-slate-500">Grouped operational view across organisations, sites, and individual cameras.</p>
                    </div>
                    <a href="{{ route('cameras.index', request()->query()) }}" class="btn-secondary">View full list</a>
                </div>

                <div id="dashboard-site-groups" class="space-y-3 p-4">
                    @foreach ($groupedSites as $group)
                        <article class="rounded-lg border border-slate-200 bg-slate-50/70 p-4">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div>
                                    <div class="flex flex-wrap items-center gap-3">
                                        <h3 class="text-lg font-bold text-slate-950">{{ $group['site']?->name ?: $group['site']?->site_name ?: 'Unassigned site' }}</h3>
                                        <span @class([
                                            'status-pill',
                                            'bg-emerald-100 text-emerald-700' => $group['status'] === 'online',
                                            'bg-amber-100 text-amber-700' => $group['status'] === 'degraded',
                                            'bg-red-100 text-red-700' => $group['status'] === 'offline',
                                            'bg-slate-200 text-slate-700' => $group['status'] === 'unknown',
                                        ])>
                                            <span class="h-2.5 w-2.5 rounded-full {{ $group['status'] === 'online' ? 'bg-emerald-500' : ($group['status'] === 'offline' ? 'bg-red-500' : ($group['status'] === 'degraded' ? 'bg-amber-500' : 'bg-slate-500')) }}"></span>
                                            {{ ucfirst($group['status']) }}
                                        </span>
                                    </div>
                                    <p class="mt-2 text-sm text-slate-600">{{ $group['organisation']?->name ?: 'Unassigned organisation' }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $group['camera_count'] }} cameras · {{ $group['online_count'] }} online</p>
                                </div>
                                <div class="text-sm text-slate-500">
                                    @if ($group['site']?->town || $group['site']?->postcode)
                                        <p>{{ trim(($group['site']?->town ?: '').' '.($group['site']?->postcode ?: '')) }}</p>
                                    @endif
                                    @if ($group['site']?->what3words)
                                        <p class="mt-1">{{ strtolower($group['site']->what3words) }}</p>
                                    @endif
                                </div>
                            </div>

                            <div class="mt-5 overflow-x-auto">
                                <table class="min-w-full divide-y divide-slate-200 text-sm">
                                    <thead class="text-left text-slate-500">
                                        <tr>
                                            <th class="pb-3 font-semibold">Camera</th>
                                            <th class="pb-3 font-semibold">Ownership</th>
                                            <th class="pb-3 font-semibold">Connectivity</th>
                                            <th class="pb-3 font-semibold">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-200/70">
                                        @foreach ($group['cameras'] as $camera)
                                            @php($cameraStatus = $camera->status ?: ($camera->is_online ? 'online' : 'unknown'))
                                            <tr data-camera-row="{{ $camera->id }}">
                                                <td class="py-4">
                                                    <a href="{{ route('cameras.show', $camera) }}" class="font-semibold text-slate-900 hover:text-brand-700">{{ $camera->name }}</a>
                                                    <p class="mt-1 text-xs text-slate-500">{{ $camera->location_name }}</p>
                                                    <p class="mt-2 text-xs text-slate-500" data-latest-event-text>
                                                        {{ $camera->latestHikvisionEvent?->event_type ? $camera->latestHikvisionEvent->event_type.' · '.($camera->latestHikvisionEvent->event_state ?: 'Unknown') : 'No events yet' }}
                                                    </p>
                                                </td>
                                                <td class="py-4 text-slate-600">
                                                    <p>{{ ucfirst($camera->ownership_type ?: 'council') }}</p>
                                                    <p class="mt-1 text-xs text-slate-500">{{ $camera->managed_by_council ? 'Council managed' : 'Externally managed' }}</p>
                                                </td>
                                                <td class="py-4 text-slate-600">
                                                    <p>{{ str($camera->connectivity_type ?: 'unknown')->replace('_', ' ')->title() }}</p>
                                                    <p class="mt-1 text-xs text-slate-500">{{ $camera->connectivity_provider ?: 'No provider set' }}</p>
                                                </td>
                                                <td class="py-4">
                                                    <span @class([
                                                        'status-pill',
                                                        'bg-emerald-100 text-emerald-700' => $cameraStatus === 'online',
                                                        'bg-red-100 text-red-700' => $cameraStatus === 'offline',
                                                        'bg-slate-200 text-slate-700' => $cameraStatus === 'unknown',
                                                    ]) data-status-pill>
                                                        <span class="h-2.5 w-2.5 rounded-full {{ $cameraStatus === 'online' ? 'bg-emerald-500' : ($cameraStatus === 'offline' ? 'bg-red-500' : 'bg-slate-500') }}" data-status-dot></span>
                                                        <span data-status-label>{{ ucfirst($cameraStatus) }}</span>
                                                    </span>
                                                    <p class="mt-2 text-xs text-slate-500" data-last-seen-text>
                                                        Last seen {{ optional($camera->last_seen_at)->diffForHumans() ?? 'never' }}
                                                    </p>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        </section>

        <aside class="space-y-6">
            <section class="panel p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="section-title">Maintenance</h2>
                        <p class="text-sm text-slate-500">Scheduled service and engineering workload.</p>
                    </div>
                    <a href="{{ route('maintenance.index') }}" class="btn-secondary">Open</a>
                </div>
                <div class="mt-4 grid grid-cols-2 gap-3">
                    <div class="rounded-lg bg-red-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-red-600">Overdue</p>
                        <p class="mt-1 text-2xl font-semibold text-red-700">{{ $maintenanceSummary['overdue'] }}</p>
                    </div>
                    <div class="rounded-lg bg-amber-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-amber-600">Due this week</p>
                        <p class="mt-1 text-2xl font-semibold text-amber-700">{{ $maintenanceSummary['dueThisWeek'] }}</p>
                    </div>
                    <div class="rounded-lg bg-brand-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-brand-600">Annual due soon</p>
                        <p class="mt-1 text-2xl font-semibold text-brand-700">{{ $maintenanceSummary['annualDueSoon'] }}</p>
                    </div>
                    <div class="rounded-lg bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Assigned to me</p>
                        <p class="mt-1 text-2xl font-semibold text-slate-950">{{ $maintenanceSummary['assignedToMe'] }}</p>
                    </div>
                </div>
            </section>

            <section class="panel p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="section-title">Needs attention</h2>
                        <p class="text-sm text-slate-500">Cameras currently offline or unknown.</p>
                    </div>
                    <span class="rounded-md bg-red-100 px-2.5 py-1 text-sm font-semibold text-red-700">{{ $offlineCameras->count() }}</span>
                </div>

                <div id="dashboard-offline-list" class="mt-4 space-y-2">
                    @forelse ($offlineCameras as $camera)
                        <a href="{{ route('cameras.show', $camera) }}" class="block rounded-lg border border-red-100 bg-red-50 px-3.5 py-3 transition hover:border-red-200 hover:bg-red-100/60">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $camera->name }}</p>
                                    <p class="text-sm text-slate-600">{{ $camera->site?->name ?: $camera->site_name }} · {{ $camera->location_name }}</p>
                                </div>
                                <span class="text-xs font-semibold uppercase tracking-wide text-red-600">{{ ucfirst($camera->status ?: ($camera->is_online ? 'online' : 'unknown')) }}</span>
                            </div>
                            <p class="mt-3 text-xs text-slate-500">Last seen {{ optional($camera->last_seen_at)->diffForHumans() ?? 'never' }}</p>
                        </a>
                    @empty
                        <p class="rounded-lg bg-emerald-50 px-4 py-4 text-sm text-emerald-700">All filtered cameras are currently online.</p>
                    @endforelse
                </div>
            </section>

            <section class="panel p-5">
                <h2 class="section-title">Coverage notes</h2>
                <div class="mt-5 space-y-4 text-sm text-slate-600">
                    <div class="rounded-lg bg-slate-50 px-4 py-4">
                        <p class="font-semibold text-slate-900">{{ $mappedCount }} cameras mapped</p>
                        <p class="mt-1">Open the full-screen map to review sites and camera spread across the filtered estate.</p>
                    </div>
                    <div class="rounded-lg bg-slate-50 px-4 py-4">
                        <p class="font-semibold text-slate-900">Recently seen</p>
                        <ul id="dashboard-recently-seen" class="mt-3 space-y-2">
                            @foreach ($recentlySeenCameras as $camera)
                                <li class="flex items-center justify-between gap-4">
                                    <span>{{ $camera->name }}</span>
                                    <span class="text-xs text-slate-500">{{ optional($camera->last_seen_at)->diffForHumans() ?? 'never' }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </section>
        </aside>
    </div>

    @push('scripts')
        <script>
            (() => {
                const warning = document.getElementById('dashboard-live-warning');
                const siteGroups = document.getElementById('dashboard-site-groups');
                const offlineList = document.getElementById('dashboard-offline-list');
                const recentList = document.getElementById('dashboard-recently-seen');
                const totalCount = document.getElementById('dashboard-total-count');
                const siteCount = document.getElementById('dashboard-site-count');
                const onlineCount = document.getElementById('dashboard-online-count');
                const offlineCount = document.getElementById('dashboard-offline-count');
                const endpoint = @json(route('api.cameras.live-status', request()->query()));

                const escapeHtml = (value) => (value ?? '')
                    .toString()
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;')
                    .replaceAll("'", '&#039;');

                const relativeTime = (value) => {
                    if (!value) return 'never';
                    const seconds = Math.max(0, Math.floor((Date.now() - new Date(value).getTime()) / 1000));
                    if (seconds < 60) return `${seconds || 1} second${seconds === 1 ? '' : 's'} ago`;
                    const minutes = Math.floor(seconds / 60);
                    if (minutes < 60) return `${minutes} minute${minutes === 1 ? '' : 's'} ago`;
                    const hours = Math.floor(minutes / 60);
                    if (hours < 24) return `${hours} hour${hours === 1 ? '' : 's'} ago`;
                    const days = Math.floor(hours / 24);
                    return `${days} day${days === 1 ? '' : 's'} ago`;
                };

                const latestEventText = (camera) => {
                    if (!camera.latest_event_type) return 'No events yet';
                    return `${camera.latest_event_type} · ${camera.latest_event_state || 'Unknown'}`;
                };

                const siteStatus = (cameras) => {
                    if (!cameras.length) return 'unknown';
                    const statuses = cameras.map((camera) => camera.status || 'unknown');
                    if (statuses.every((status) => status === 'online')) return 'online';
                    if (statuses.every((status) => status === 'offline')) return 'offline';
                    return 'degraded';
                };

                const statusPillClass = (status) => ({
                    online: 'status-pill bg-emerald-100 text-emerald-700',
                    offline: 'status-pill bg-red-100 text-red-700',
                    degraded: 'status-pill bg-amber-100 text-amber-700',
                    unknown: 'status-pill bg-slate-200 text-slate-700',
                }[status] || 'status-pill bg-slate-200 text-slate-700');

                const statusDotClass = (status) => ({
                    online: 'h-2.5 w-2.5 rounded-full bg-emerald-500',
                    offline: 'h-2.5 w-2.5 rounded-full bg-red-500',
                    degraded: 'h-2.5 w-2.5 rounded-full bg-amber-500',
                    unknown: 'h-2.5 w-2.5 rounded-full bg-slate-500',
                }[status] || 'h-2.5 w-2.5 rounded-full bg-slate-500');

                const renderSiteGroups = (cameras) => {
                    const grouped = Object.values(cameras.reduce((carry, camera) => {
                        const key = camera.site?.id || `legacy-${camera.site_name || 'unassigned'}`;
                        if (!carry[key]) {
                            carry[key] = {
                                site: camera.site,
                                organisation: camera.organisation,
                                cameras: [],
                            };
                        }

                        carry[key].cameras.push(camera);
                        return carry;
                    }, {}));

                    siteGroups.innerHTML = grouped.map((group) => {
                        const status = siteStatus(group.cameras);
                        const online = group.cameras.filter((camera) => camera.status === 'online').length;
                        const site = group.site || {};
                        const organisation = group.organisation || {};

                        return `
                            <article class="rounded-lg border border-slate-200 bg-slate-50/70 p-4">
                                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                    <div>
                                        <div class="flex flex-wrap items-center gap-3">
                                            <h3 class="text-lg font-bold text-slate-950">${escapeHtml(site.name || group.cameras[0]?.site_name || 'Unassigned site')}</h3>
                                            <span class="${statusPillClass(status)}">
                                                <span class="${statusDotClass(status)}"></span>
                                                ${escapeHtml(status.charAt(0).toUpperCase() + status.slice(1))}
                                            </span>
                                        </div>
                                        <p class="mt-2 text-sm text-slate-600">${escapeHtml(organisation.name || 'Unassigned organisation')}</p>
                                        <p class="mt-1 text-xs text-slate-500">${group.cameras.length} cameras · ${online} online</p>
                                    </div>
                                    <div class="text-sm text-slate-500">
                                        ${(site.town || site.postcode) ? `<p>${escapeHtml(`${site.town || ''} ${site.postcode || ''}`.trim())}</p>` : ''}
                                        ${site.what3words ? `<p class="mt-1">${escapeHtml(String(site.what3words).toLowerCase())}</p>` : ''}
                                    </div>
                                </div>
                                <div class="mt-5 overflow-x-auto">
                                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                                        <thead class="text-left text-slate-500">
                                            <tr>
                                                <th class="pb-3 font-semibold">Camera</th>
                                                <th class="pb-3 font-semibold">Ownership</th>
                                                <th class="pb-3 font-semibold">Connectivity</th>
                                                <th class="pb-3 font-semibold">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-200/70">
                                            ${group.cameras.map((camera) => `
                                                <tr data-camera-row="${camera.id}">
                                                    <td class="py-4">
                                                        <a href="/cameras/${camera.id}" class="font-semibold text-slate-900 hover:text-brand-700">${escapeHtml(camera.name)}</a>
                                                        <p class="mt-1 text-xs text-slate-500">${escapeHtml(camera.location_name || '')}</p>
                                                        <p class="mt-2 text-xs text-slate-500" data-latest-event-text>${escapeHtml(latestEventText(camera))}</p>
                                                    </td>
                                                    <td class="py-4 text-slate-600">
                                                        <p>${escapeHtml((camera.ownership_type || 'council').charAt(0).toUpperCase() + (camera.ownership_type || 'council').slice(1))}</p>
                                                        <p class="mt-1 text-xs text-slate-500">${camera.managed_by_council ? 'Council managed' : 'Externally managed'}</p>
                                                    </td>
                                                    <td class="py-4 text-slate-600">
                                                        <p>${escapeHtml((camera.connectivity_type || 'unknown').replaceAll('_', ' '))}</p>
                                                        <p class="mt-1 text-xs text-slate-500">${escapeHtml(camera.connectivity_provider || 'No provider set')}</p>
                                                    </td>
                                                    <td class="py-4">
                                                        <span class="${statusPillClass(camera.status || 'unknown')}" data-status-pill>
                                                            <span class="${statusDotClass(camera.status || 'unknown')}" data-status-dot></span>
                                                            <span data-status-label>${escapeHtml((camera.status || 'unknown').charAt(0).toUpperCase() + (camera.status || 'unknown').slice(1))}</span>
                                                        </span>
                                                        <p class="mt-2 text-xs text-slate-500" data-last-seen-text>Last seen ${escapeHtml(relativeTime(camera.last_seen_at))}</p>
                                                    </td>
                                                </tr>
                                            `).join('')}
                                        </tbody>
                                    </table>
                                </div>
                            </article>
                        `;
                    }).join('');
                };

                const updateSummary = (cameras) => {
                    totalCount.textContent = cameras.length;
                    siteCount.textContent = new Set(cameras.map((camera) => camera.site?.id || `legacy-${camera.site_name || 'unassigned'}`)).size;
                    onlineCount.textContent = cameras.filter((camera) => camera.status === 'online').length;
                    offlineCount.textContent = cameras.filter((camera) => camera.status === 'offline').length;

                    const attention = cameras.filter((camera) => camera.status !== 'online');
                    offlineList.innerHTML = attention.length
                        ? attention.map((camera) => `
                            <a href="/cameras/${camera.id}" class="block rounded-lg border border-red-100 bg-red-50 px-3.5 py-3 transition hover:border-red-200 hover:bg-red-100/60">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="font-semibold text-slate-900">${escapeHtml(camera.name)}</p>
                                        <p class="text-sm text-slate-600">${escapeHtml((camera.site?.name || camera.site_name || 'Unassigned site'))} · ${escapeHtml(camera.location_name || '')}</p>
                                    </div>
                                    <span class="text-xs font-semibold uppercase tracking-wide text-red-600">${escapeHtml((camera.status || 'unknown').charAt(0).toUpperCase() + (camera.status || 'unknown').slice(1))}</span>
                                </div>
                                <p class="mt-3 text-xs text-slate-500">Last seen ${escapeHtml(relativeTime(camera.last_seen_at))}</p>
                            </a>
                        `).join('')
                        : '<p class="rounded-lg bg-emerald-50 px-4 py-4 text-sm text-emerald-700">All filtered cameras are currently online.</p>';

                    const recent = [...cameras]
                        .sort((a, b) => new Date(b.last_seen_at || 0) - new Date(a.last_seen_at || 0))
                        .slice(0, 5);

                    recentList.innerHTML = recent.map((camera) => `
                        <li class="flex items-center justify-between gap-4">
                            <span>${escapeHtml(camera.name)}</span>
                            <span class="text-xs text-slate-500">${escapeHtml(relativeTime(camera.last_seen_at))}</span>
                        </li>
                    `).join('');

                    renderSiteGroups(cameras);
                };

                const poll = async () => {
                    try {
                        const response = await fetch(endpoint, {
                            headers: { 'Accept': 'application/json' },
                            credentials: 'same-origin',
                        });

                        if (!response.ok) throw new Error('Polling failed');

                        const data = await response.json();
                        updateSummary(data.cameras || []);
                        warning.classList.add('hidden');
                    } catch (error) {
                        warning.classList.remove('hidden');
                    }
                };

                poll();
                window.setInterval(poll, 5000);
            })();
        </script>
    @endpush
</x-layouts.app>
