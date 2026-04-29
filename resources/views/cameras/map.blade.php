<x-layouts.app
    :title="'Map | '.config('app.name')"
    heading="Full-screen map"
    subheading="Site-wide visual coverage with site health and individual camera markers."
    :fullWidth="true"
>
    @push('styles')
        <link
            rel="stylesheet"
            href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
            integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
            crossorigin=""
        >
    @endpush

    <div class="mx-auto max-w-7xl">
        @include('partials.camera-filters', ['action' => route('cameras.map')])
    </div>

    <div id="map-live-warning" class="mb-6 hidden rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
        Live update connection issue
    </div>

    <section class="panel relative overflow-hidden">
        <div class="absolute left-5 top-5 z-[500] max-w-md rounded-lg border border-white/70 bg-white/85 p-4 shadow-xl backdrop-blur">
            <h2 class="text-lg font-bold text-slate-950">Status legend</h2>
            <div class="mt-3 flex flex-wrap gap-3 text-sm text-slate-600">
                <span class="inline-flex items-center gap-2"><span class="h-3 w-3 rounded-full border-4 border-emerald-200 bg-emerald-500"></span> Site online</span>
                <span class="inline-flex items-center gap-2"><span class="h-3 w-3 rounded-full border-4 border-amber-200 bg-amber-500"></span> Site degraded</span>
                <span class="inline-flex items-center gap-2"><span class="h-3 w-3 rounded-full border-4 border-red-200 bg-red-500"></span> Site offline</span>
                <span class="inline-flex items-center gap-2"><span class="h-3 w-3 rounded-full bg-emerald-500"></span> Camera online</span>
                <span class="inline-flex items-center gap-2"><span class="h-3 w-3 rounded-full bg-red-500"></span> Camera offline</span>
                <span class="inline-flex items-center gap-2"><span class="h-3 w-3 rounded-full bg-slate-500"></span> Camera unknown</span>
            </div>
        </div>

        <div id="camera-map" class="h-[calc(100vh-16rem)] min-h-[38rem] w-full"></div>
    </section>

    @php
        $mapSites = $cameras
            ->filter(fn ($camera) => $camera->site !== null)
            ->groupBy('site_id')
            ->map(function ($siteCameras) {
                $site = $siteCameras->first()->site;

                if (! filled($site?->latitude) || ! filled($site?->longitude)) {
                    return null;
                }

                return [
                    'id' => $site->id,
                    'name' => $site->name,
                    'organisation_name' => $site->organisation?->name,
                    'latitude' => $site->latitude,
                    'longitude' => $site->longitude,
                    'status' => \App\Models\Site::statusForCameras($siteCameras),
                    'what3words' => $site->what3words,
                    'camera_count' => $siteCameras->count(),
                ];
            })
            ->filter()
            ->values();

        $mapCameras = $cameras
            ->filter(fn ($camera) => filled($camera->latitude) && filled($camera->longitude))
            ->map(function ($camera) {
                return [
                    'id' => $camera->id,
                    'name' => $camera->name,
                    'site_id' => $camera->site_id,
                    'site_name' => $camera->site?->name ?: $camera->site_name,
                    'organisation_name' => $camera->site?->organisation?->name,
                    'location_name' => $camera->location_name,
                    'latitude' => $camera->latitude,
                    'longitude' => $camera->longitude,
                    'status' => $camera->status ?: ($camera->is_online ? 'online' : 'unknown'),
                    'show_url' => route('cameras.show', $camera),
                    'web_ui_url' => $camera->web_ui_url,
                    'last_seen_at' => $camera->last_seen_at?->toIso8601String(),
                    'ownership_type' => $camera->ownership_type,
                ];
            })
            ->values();
    @endphp

    @push('scripts')
        <script
            src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
            crossorigin=""
        ></script>
        <script>
            const initialSites = @json($mapSites);
            const initialCameras = @json($mapCameras);
            const focusCameraId = @json($focusCameraId);
            const endpoint = @json(route('api.cameras.live-status', request()->query()));
            const warning = document.getElementById('map-live-warning');

            const map = L.map('camera-map', { zoomControl: true });

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            const siteMarkers = new Map();
            const cameraMarkers = new Map();
            let hasInitialView = false;

            const siteColor = (status) => ({
                online: '#16a34a',
                degraded: '#d97706',
                offline: '#dc2626',
                unknown: '#64748b',
            }[status] || '#64748b');

            const cameraColor = (status) => ({
                online: '#16a34a',
                offline: '#dc2626',
                unknown: '#64748b',
            }[status] || '#64748b');

            const sitePopupMarkup = (site) => `
                <div style="min-width: 220px;">
                    <strong>${site.name}</strong><br>
                    <span>${site.organisation_name || 'Unassigned organisation'}</span><br>
                    <span>Status: ${site.status}</span><br>
                    <span>Cameras: ${site.camera_count ?? 0}</span><br>
                    <span>${site.what3words ? `what3words: ${site.what3words}` : ''}</span>
                </div>
            `;

            const cameraPopupMarkup = (camera) => `
                <div style="min-width: 220px;">
                    <strong>${camera.name}</strong><br>
                    <span>${camera.organisation_name || ''}${camera.site_name ? ' · ' + camera.site_name : ''}</span><br>
                    <span>${camera.location_name || ''}</span><br>
                    <span>Status: ${camera.status}</span><br>
                    <span>Last seen: ${camera.last_seen_at ? new Date(camera.last_seen_at).toLocaleString() : 'Never'}</span><br><br>
                    <a href="${camera.show_url}">View details</a><br>
                    <a href="${camera.web_ui_url}" target="_blank" rel="noreferrer">Open web UI</a>
                </div>
            `;

            const syncMarkers = (sites, cameras) => {
                const bounds = [];

                sites.forEach((site) => {
                    if (site.latitude == null || site.longitude == null) return;

                    let marker = siteMarkers.get(site.id);

                    if (!marker) {
                        marker = L.circleMarker([site.latitude, site.longitude], {
                            radius: 15,
                            fillColor: siteColor(site.status),
                            color: '#ffffff',
                            weight: 5,
                            fillOpacity: 0.9,
                        }).addTo(map);

                        siteMarkers.set(site.id, marker);
                    }

                    marker.setLatLng([site.latitude, site.longitude]);
                    marker.setStyle({ fillColor: siteColor(site.status) });
                    marker.bindPopup(sitePopupMarkup(site));
                    bounds.push([site.latitude, site.longitude]);
                });

                cameras.forEach((camera) => {
                    if (camera.latitude == null || camera.longitude == null) return;

                    let marker = cameraMarkers.get(camera.id);

                    if (!marker) {
                        marker = L.circleMarker([camera.latitude, camera.longitude], {
                            radius: 9,
                            fillColor: cameraColor(camera.status),
                            color: '#ffffff',
                            weight: 3,
                            fillOpacity: 0.95,
                        }).addTo(map);

                        cameraMarkers.set(camera.id, marker);
                    }

                    marker.setLatLng([camera.latitude, camera.longitude]);
                    marker.setStyle({ fillColor: cameraColor(camera.status) });
                    marker.bindPopup(cameraPopupMarkup(camera));
                    bounds.push([camera.latitude, camera.longitude]);
                });

                if (!hasInitialView && focusCameraId && cameraMarkers.has(focusCameraId)) {
                    const marker = cameraMarkers.get(focusCameraId);
                    map.setView(marker.getLatLng(), 17);
                    marker.openPopup();
                    hasInitialView = true;
                } else if (!hasInitialView && bounds.length) {
                    map.fitBounds(bounds, { padding: [60, 60] });
                    hasInitialView = true;
                } else if (!hasInitialView) {
                    map.setView([54.5, -2.5], 6);
                    hasInitialView = true;
                }
            };

            syncMarkers(initialSites, initialCameras);

            const poll = async () => {
                try {
                    const response = await fetch(endpoint, {
                        headers: { 'Accept': 'application/json' },
                        credentials: 'same-origin',
                    });

                    if (!response.ok) throw new Error('Polling failed');

                    const data = await response.json();
                    const sites = data.sites || [];
                    const cameras = (data.cameras || []).filter((camera) => camera.latitude != null && camera.longitude != null);

                    syncMarkers(sites, cameras);
                    warning.classList.add('hidden');
                } catch (error) {
                    warning.classList.remove('hidden');
                }
            };

            poll();
            window.setInterval(poll, 5000);
        </script>
    @endpush
</x-layouts.app>
