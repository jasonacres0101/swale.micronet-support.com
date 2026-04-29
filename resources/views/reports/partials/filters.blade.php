@php
    $showOrganisation = $showOrganisation ?? true;
    $showSite = $showSite ?? true;
    $showCamera = $showCamera ?? false;
    $showOwnership = $showOwnership ?? false;
    $showConnectivity = $showConnectivity ?? false;
    $showEventType = $showEventType ?? false;
@endphp

<form method="GET" action="{{ route($action) }}" class="panel mb-5 p-4">
    <div class="flex flex-col gap-4">
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div>
                <label for="date_from" class="mb-2 block text-sm font-semibold text-slate-700">Date from</label>
                <input id="date_from" name="date_from" type="date" value="{{ $filters['date_from'] }}" class="field-control">
            </div>

            <div>
                <label for="date_to" class="mb-2 block text-sm font-semibold text-slate-700">Date to</label>
                <input id="date_to" name="date_to" type="date" value="{{ $filters['date_to'] }}" class="field-control">
            </div>

            @if ($showOrganisation)
                <div>
                    <label for="organisation" class="mb-2 block text-sm font-semibold text-slate-700">Organisation</label>
                    <select id="organisation" name="organisation" class="field-control">
                        <option value="">All organisations</option>
                        @foreach ($filterOrganisations as $organisation)
                            <option value="{{ $organisation->id }}" @selected((string) $filters['organisation'] === (string) $organisation->id)>
                                {{ $organisation->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            @if ($showSite)
                <div>
                    <label for="site" class="mb-2 block text-sm font-semibold text-slate-700">Site</label>
                    <select id="site" name="site" class="field-control">
                        <option value="">All sites</option>
                        @foreach ($filterSites as $site)
                            <option value="{{ $site->id }}" @selected((string) $filters['site'] === (string) $site->id)>
                                {{ $site->name }}{{ $site->organisation ? ' · '.$site->organisation->name : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            @if ($showCamera)
                <div>
                    <label for="camera" class="mb-2 block text-sm font-semibold text-slate-700">Camera</label>
                    <select id="camera" name="camera" class="field-control">
                        <option value="">All cameras</option>
                        @foreach ($filterCameras as $camera)
                            <option value="{{ $camera->id }}" @selected((string) $filters['camera'] === (string) $camera->id)>
                                {{ $camera->name }}{{ $camera->site ? ' · '.$camera->site->name : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            @if ($showOwnership)
                <div>
                    <label for="ownership_type" class="mb-2 block text-sm font-semibold text-slate-700">Ownership</label>
                    <select id="ownership_type" name="ownership_type" class="field-control">
                        <option value="">Council and client</option>
                        <option value="council" @selected($filters['ownership_type'] === 'council')>Council owned</option>
                        <option value="client" @selected($filters['ownership_type'] === 'client')>Client owned</option>
                    </select>
                </div>
            @endif

            @if ($showConnectivity)
                <div>
                    <label for="connectivity_type" class="mb-2 block text-sm font-semibold text-slate-700">Connectivity</label>
                    <select id="connectivity_type" name="connectivity_type" class="field-control">
                        <option value="">Any connectivity</option>
                        <option value="sim" @selected($filters['connectivity_type'] === 'sim')>SIM</option>
                        <option value="fibre" @selected($filters['connectivity_type'] === 'fibre')>Fibre</option>
                        <option value="broadband" @selected($filters['connectivity_type'] === 'broadband')>Broadband</option>
                        <option value="leased_line" @selected($filters['connectivity_type'] === 'leased_line')>Leased line</option>
                        <option value="lan" @selected($filters['connectivity_type'] === 'lan')>LAN</option>
                        <option value="unknown" @selected($filters['connectivity_type'] === 'unknown')>Unknown</option>
                    </select>
                </div>
            @endif

            @if ($showEventType)
                <div>
                    <label for="event_type" class="mb-2 block text-sm font-semibold text-slate-700">Event type</label>
                    <select id="event_type" name="event_type" class="field-control">
                        <option value="">All event types</option>
                        @foreach ($eventTypes as $eventType)
                            <option value="{{ $eventType }}" @selected($filters['event_type'] === $eventType)>{{ $eventType }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 pt-4">
            <div class="text-sm text-slate-500">
                Reporting window: <span class="font-semibold text-slate-700">{{ $range['label'] }}</span>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route($action) }}" class="btn-secondary">Reset</a>
                <button type="submit" class="btn-primary">Run report</button>
            </div>
        </div>
    </div>
</form>
