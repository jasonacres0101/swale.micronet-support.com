<div>
    <label for="name" class="mb-2 block text-sm font-semibold text-slate-700">Camera name</label>
    <input id="name" name="name" type="text" value="{{ old('name', $camera->name) }}" required class="field-control">
    @error('name') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
</div>

<div>
    <label for="site_id" class="mb-2 block text-sm font-semibold text-slate-700">Site</label>
    @php($currentSiteId = old('site_id', $camera->site_id))
    <select id="site_id" name="site_id" required class="field-control">
        <option value="">Select a site</option>
        @foreach ($filterSites->groupBy(fn ($siteOption) => $siteOption->organisation?->name ?? 'Unassigned organisation') as $organisationName => $siteOptions)
            <optgroup label="{{ $organisationName }}">
                @foreach ($siteOptions as $siteOption)
                    <option value="{{ $siteOption->id }}" @selected((string) $currentSiteId === (string) $siteOption->id)>{{ $siteOption->name }}</option>
                @endforeach
            </optgroup>
        @endforeach
    </select>
    @error('site_id') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
</div>

<div>
    <label for="location_name" class="mb-2 block text-sm font-semibold text-slate-700">Location</label>
    <input id="location_name" name="location_name" type="text" value="{{ old('location_name', $camera->location_name) }}" required class="field-control">
    @error('location_name') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
</div>

<div>
    <label for="ownership_type" class="mb-2 block text-sm font-semibold text-slate-700">Ownership type</label>
    @php($ownershipType = old('ownership_type', $camera->ownership_type ?: 'council'))
    <select id="ownership_type" name="ownership_type" class="field-control">
        <option value="council" @selected($ownershipType === 'council')>Council</option>
        <option value="client" @selected($ownershipType === 'client')>Client</option>
    </select>
    @error('ownership_type') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
</div>

<div>
    <label for="ip_address" class="mb-2 block text-sm font-semibold text-slate-700">IP address</label>
    <input id="ip_address" name="ip_address" type="text" value="{{ old('ip_address', $camera->ip_address) }}" required class="field-control">
    @error('ip_address') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
</div>

<div>
    <label for="mac_address" class="mb-2 block text-sm font-semibold text-slate-700">MAC address</label>
    <input id="mac_address" name="mac_address" type="text" value="{{ old('mac_address', $camera->mac_address) }}" class="field-control">
    <p class="mt-2 text-xs text-slate-500">Used as the primary Hikvision event matching key.</p>
    @error('mac_address') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
</div>

<div>
    <label for="serial_number" class="mb-2 block text-sm font-semibold text-slate-700">Camera serial number</label>
    <input id="serial_number" name="serial_number" type="text" value="{{ old('serial_number', $camera->serial_number) }}" class="field-control">
    <p class="mt-2 text-xs text-slate-500">Used to match snapshot emails when the sender address starts with this serial number.</p>
    @error('serial_number') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
</div>

<div>
    <label for="web_ui_url" class="mb-2 block text-sm font-semibold text-slate-700">Camera web UI URL</label>
    <input id="web_ui_url" name="web_ui_url" type="url" value="{{ old('web_ui_url', $camera->web_ui_url) }}" required class="field-control">
    @error('web_ui_url') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
</div>

<div>
    <label for="latitude" class="mb-2 block text-sm font-semibold text-slate-700">Latitude</label>
    <input id="latitude" name="latitude" type="number" step="0.0000001" value="{{ old('latitude', $camera->latitude) }}" class="field-control">
    @error('latitude') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
</div>

<div>
    <label for="longitude" class="mb-2 block text-sm font-semibold text-slate-700">Longitude</label>
    <input id="longitude" name="longitude" type="number" step="0.0000001" value="{{ old('longitude', $camera->longitude) }}" class="field-control">
    @error('longitude') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
</div>

<div>
    <label for="what3words" class="mb-2 block text-sm font-semibold text-slate-700">what3words</label>
    <input id="what3words" name="what3words" type="text" value="{{ old('what3words', $camera->what3words) }}" placeholder="index.home.raft" class="field-control">
    <p class="mt-2 text-xs text-slate-500">Enter a three-word address like <span class="font-semibold">filled.count.soap</span>.</p>
    @error('what3words') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
</div>

<div>
    <label for="status" class="mb-2 block text-sm font-semibold text-slate-700">Status</label>
    @php($currentStatus = old('status', $camera->status ?: ($camera->is_online ? 'online' : 'unknown')))
    <select id="status" name="status" class="field-control">
        <option value="online" @selected($currentStatus === 'online')>Online</option>
        <option value="offline" @selected($currentStatus === 'offline')>Offline</option>
        <option value="unknown" @selected($currentStatus === 'unknown')>Unknown</option>
    </select>
    @error('status') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
</div>

<div>
    <label class="mb-2 block text-sm font-semibold text-slate-700">Council managed</label>
    <label class="flex items-center gap-3 rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700">
        <input type="checkbox" name="managed_by_council" value="1" {{ old('managed_by_council', $camera->managed_by_council ?? true) ? 'checked' : '' }} class="h-4 w-4 rounded border-slate-300 text-brand-700">
        Managed by council operations
    </label>
</div>

<div>
    <label for="last_seen_at" class="mb-2 block text-sm font-semibold text-slate-700">Last seen</label>
    <input id="last_seen_at" name="last_seen_at" type="datetime-local" value="{{ old('last_seen_at', optional($camera->last_seen_at)->format('Y-m-d\\TH:i')) }}" class="field-control">
    @error('last_seen_at') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
</div>

<div>
    <label for="last_event_at" class="mb-2 block text-sm font-semibold text-slate-700">Last event</label>
    <input id="last_event_at" name="last_event_at" type="datetime-local" value="{{ old('last_event_at', optional($camera->last_event_at)->format('Y-m-d\\TH:i')) }}" class="field-control">
    @error('last_event_at') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
</div>

<div class="lg:col-span-2 rounded-lg border border-slate-200 bg-slate-50/70 p-5">
    <div class="mb-5">
        <h2 class="text-lg font-bold text-slate-950">Connectivity</h2>
        <p class="mt-1 text-sm text-slate-500">Capture WAN type, provider, SIM, router, and private APN details for field support.</p>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div>
            <label for="connectivity_type" class="mb-2 block text-sm font-semibold text-slate-700">Connectivity type</label>
            @php($connectivityType = old('connectivity_type', $camera->connectivity_type ?: 'unknown'))
            <select id="connectivity_type" name="connectivity_type" class="field-control">
                <option value="sim" @selected($connectivityType === 'sim')>SIM</option>
                <option value="fibre" @selected($connectivityType === 'fibre')>Fibre</option>
                <option value="broadband" @selected($connectivityType === 'broadband')>Broadband</option>
                <option value="leased_line" @selected($connectivityType === 'leased_line')>Leased line</option>
                <option value="lan" @selected($connectivityType === 'lan')>LAN</option>
                <option value="unknown" @selected($connectivityType === 'unknown')>Unknown</option>
            </select>
            @error('connectivity_type') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="connectivity_provider" class="mb-2 block text-sm font-semibold text-slate-700">Provider</label>
            <input id="connectivity_provider" name="connectivity_provider" type="text" value="{{ old('connectivity_provider', $camera->connectivity_provider) }}" class="field-control">
            @error('connectivity_provider') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="sim_number" class="mb-2 block text-sm font-semibold text-slate-700">SIM number</label>
            <input id="sim_number" name="sim_number" type="text" value="{{ old('sim_number', $camera->sim_number) }}" class="field-control">
            @error('sim_number') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="sim_iccid" class="mb-2 block text-sm font-semibold text-slate-700">SIM ICCID</label>
            <input id="sim_iccid" name="sim_iccid" type="text" value="{{ old('sim_iccid', $camera->sim_iccid) }}" class="field-control">
            @error('sim_iccid') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="sim_static_ip" class="mb-2 block text-sm font-semibold text-slate-700">SIM static/private IP</label>
            <input id="sim_static_ip" name="sim_static_ip" type="text" value="{{ old('sim_static_ip', $camera->sim_static_ip) }}" class="field-control">
            @error('sim_static_ip') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="apn_name" class="mb-2 block text-sm font-semibold text-slate-700">APN name</label>
            <input id="apn_name" name="apn_name" type="text" value="{{ old('apn_name', $camera->apn_name) }}" class="field-control">
            @error('apn_name') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="router_model" class="mb-2 block text-sm font-semibold text-slate-700">Router model</label>
            <input id="router_model" name="router_model" type="text" value="{{ old('router_model', $camera->router_model) }}" class="field-control">
            @error('router_model') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="router_serial" class="mb-2 block text-sm font-semibold text-slate-700">Router serial</label>
            <input id="router_serial" name="router_serial" type="text" value="{{ old('router_serial', $camera->router_serial) }}" class="field-control">
            @error('router_serial') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="router_ip_address" class="mb-2 block text-sm font-semibold text-slate-700">Router IP address</label>
            <input id="router_ip_address" name="router_ip_address" type="text" value="{{ old('router_ip_address', $camera->router_ip_address) }}" class="field-control">
            @error('router_ip_address') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="wan_ip_address" class="mb-2 block text-sm font-semibold text-slate-700">WAN IP address</label>
            <input id="wan_ip_address" name="wan_ip_address" type="text" value="{{ old('wan_ip_address', $camera->wan_ip_address) }}" class="field-control">
            @error('wan_ip_address') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="lg:col-span-2">
            <label class="flex items-center gap-3 rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700">
                <input type="checkbox" name="private_apn" value="1" {{ old('private_apn', $camera->private_apn) ? 'checked' : '' }} class="h-4 w-4 rounded border-slate-300 text-brand-700">
                Private APN
            </label>
        </div>

        <div class="lg:col-span-2">
            <label for="connectivity_notes" class="mb-2 block text-sm font-semibold text-slate-700">Connectivity notes</label>
            <textarea id="connectivity_notes" name="connectivity_notes" rows="4" class="field-control">{{ old('connectivity_notes', $camera->connectivity_notes) }}</textarea>
            @error('connectivity_notes') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
    </div>
</div>

<div class="lg:col-span-2">
    <label for="description" class="mb-2 block text-sm font-semibold text-slate-700">Description</label>
    <textarea id="description" name="description" rows="5" class="field-control">{{ old('description', $camera->description) }}</textarea>
    @error('description') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
</div>
