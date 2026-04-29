<form method="GET" action="{{ $action }}" class="panel mb-6 p-4">
    <div class="flex flex-col gap-4">
        <div class="flex items-center justify-between border-b border-slate-200 pb-3">
            <div>
                <p class="text-sm font-semibold text-slate-950">Filters</p>
                <p class="text-xs text-slate-500">Narrow the estate by owner, location, status, connectivity, or ownership.</p>
            </div>
        </div>

        <div class="flex flex-col gap-4 lg:flex-row lg:items-end">
            <div class="grid flex-1 gap-3 sm:grid-cols-2 xl:grid-cols-5">
            <div>
                <label for="organisation" class="mb-2 block text-sm font-semibold text-slate-700">Organisation / client</label>
                <select id="organisation" name="organisation" class="field-control">
                    <option value="">All organisations</option>
                    @foreach ($filterOrganisations as $organisation)
                        <option value="{{ $organisation->id }}" @selected(($filters['organisation'] ?? '') === (string) $organisation->id)>{{ $organisation->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="site" class="mb-2 block text-sm font-semibold text-slate-700">Site</label>
                <select id="site" name="site" class="field-control">
                    <option value="">All sites</option>
                    @foreach ($filterSites as $siteOption)
                        <option value="{{ $siteOption->id }}" @selected(($filters['site'] ?? '') === (string) $siteOption->id)>
                            {{ $siteOption->name }}{{ $siteOption->organisation ? ' · '.$siteOption->organisation->name : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="status" class="mb-2 block text-sm font-semibold text-slate-700">Camera status</label>
                <select id="status" name="status" class="field-control">
                    <option value="">Any status</option>
                    <option value="online" @selected(($filters['status'] ?? '') === 'online')>Online</option>
                    <option value="offline" @selected(($filters['status'] ?? '') === 'offline')>Offline</option>
                    <option value="unknown" @selected(($filters['status'] ?? '') === 'unknown')>Unknown</option>
                </select>
            </div>

            <div>
                <label for="connectivity_type" class="mb-2 block text-sm font-semibold text-slate-700">Connectivity type</label>
                <select id="connectivity_type" name="connectivity_type" class="field-control">
                    <option value="">Any connectivity</option>
                    <option value="sim" @selected(($filters['connectivity_type'] ?? '') === 'sim')>SIM</option>
                    <option value="fibre" @selected(($filters['connectivity_type'] ?? '') === 'fibre')>Fibre</option>
                    <option value="broadband" @selected(($filters['connectivity_type'] ?? '') === 'broadband')>Broadband</option>
                    <option value="leased_line" @selected(($filters['connectivity_type'] ?? '') === 'leased_line')>Leased line</option>
                    <option value="lan" @selected(($filters['connectivity_type'] ?? '') === 'lan')>LAN</option>
                    <option value="unknown" @selected(($filters['connectivity_type'] ?? '') === 'unknown')>Unknown</option>
                </select>
            </div>

            <div>
                <label for="ownership_type" class="mb-2 block text-sm font-semibold text-slate-700">Ownership type</label>
                <select id="ownership_type" name="ownership_type" class="field-control">
                    <option value="">Any ownership</option>
                    <option value="council" @selected(($filters['ownership_type'] ?? '') === 'council')>Council</option>
                    <option value="client" @selected(($filters['ownership_type'] ?? '') === 'client')>Client</option>
                </select>
            </div>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="btn-primary">Apply</button>
                <a href="{{ $action }}" class="btn-secondary">Reset</a>
            </div>
        </div>
    </div>
</form>
