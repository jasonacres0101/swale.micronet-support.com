<x-layouts.app
    :title="'Settings | '.config('app.name')"
    heading="Settings"
    subheading="Administrative tools, user access, and monitoring configuration pages."
>
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        @if (auth()->user()?->canViewAlarmAdmin())
            <a href="{{ route('cameras.events') }}" class="panel block p-5 transition hover:border-brand-300 hover:shadow-[0_14px_34px_rgba(15,23,42,0.08)]">
                <p class="text-xs font-semibold uppercase tracking-wide text-brand-500">Monitoring</p>
                <h2 class="mt-2 text-lg font-semibold text-slate-950">Alarm admin</h2>
                <p class="mt-3 text-sm leading-6 text-slate-600">
                    Review recent Hikvision events, inspect unmatched alarms, and inspect incoming alarm payloads.
                </p>
                <p class="mt-4 text-sm font-semibold text-brand-700">Open alarm administration</p>
            </a>

            <a href="{{ route('settings.hikvision-setup') }}" class="panel block p-5 transition hover:border-brand-300 hover:shadow-[0_14px_34px_rgba(15,23,42,0.08)]">
                <p class="text-xs font-semibold uppercase tracking-wide text-brand-500">Hikvision</p>
                <h2 class="mt-2 text-lg font-semibold text-slate-950">Camera setup guide</h2>
                <p class="mt-3 text-sm leading-6 text-slate-600">
                    View the HTTP/HTTPS Alarm Server settings, required camera fields, token header, and test checklist.
                </p>
                <p class="mt-4 text-sm font-semibold text-brand-700">Open setup guide</p>
            </a>

            <a href="{{ route('settings.camera-email') }}" class="panel block p-5 transition hover:border-brand-300 hover:shadow-[0_14px_34px_rgba(15,23,42,0.08)]">
                <p class="text-xs font-semibold uppercase tracking-wide text-brand-500">Email ingest</p>
                <h2 class="mt-2 text-lg font-semibold text-slate-950">Camera snapshot mailbox</h2>
                <p class="mt-3 text-sm leading-6 text-slate-600">
                    Configure the mailbox used to read camera snapshot emails, test IMAP access, and enable scheduled imports.
                </p>
                <p class="mt-4 text-sm font-semibold text-brand-700">Open email settings</p>
            </a>
        @endif

        @if (auth()->user()?->canCreateMaintenance() || auth()->user()?->canUpdateMaintenance())
            <a href="{{ route('settings.maintenance-task-types.index') }}" class="panel block p-5 transition hover:border-brand-300 hover:shadow-[0_14px_34px_rgba(15,23,42,0.08)]">
                <p class="text-xs font-semibold uppercase tracking-wide text-brand-500">Maintenance</p>
                <h2 class="mt-2 text-lg font-semibold text-slate-950">Maintenance task types</h2>
                <p class="mt-3 text-sm leading-6 text-slate-600">
                    Add and edit the task types used when scheduling maintenance, inspections, router checks, and service reports.
                </p>
                <p class="mt-4 text-sm font-semibold text-brand-700">Manage maintenance task types</p>
            </a>
        @endif

        @if (auth()->user()?->canManageUsers())
            <a href="{{ route('users.index') }}" class="panel block p-5 transition hover:border-brand-300 hover:shadow-[0_14px_34px_rgba(15,23,42,0.08)]">
                <p class="text-xs font-semibold uppercase tracking-wide text-brand-500">Access</p>
                <h2 class="mt-2 text-lg font-semibold text-slate-950">Users</h2>
                <p class="mt-3 text-sm leading-6 text-slate-600">
                    Create and manage user profiles, assign roles, and control who can access admin actions.
                </p>
                <p class="mt-4 text-sm font-semibold text-brand-700">Open user management</p>
            </a>
        @endif

        @if (auth()->user()?->canViewOrganisationDirectory())
            <a href="{{ route('organisations.index') }}" class="panel block p-5 transition hover:border-brand-300 hover:shadow-[0_14px_34px_rgba(15,23,42,0.08)]">
                <p class="text-xs font-semibold uppercase tracking-wide text-brand-500">Ownership</p>
                <h2 class="mt-2 text-lg font-semibold text-slate-950">Clients</h2>
                <p class="mt-3 text-sm leading-6 text-slate-600">
                    {{ auth()->user()?->canManageOrganisations() ? 'Create and manage councils, clients, contractors, and other organisations that own or oversee CCTV sites.' : 'View councils, clients, contractors, and other organisations that own or oversee CCTV sites.' }}
                </p>
                <p class="mt-4 text-sm font-semibold text-brand-700">Open client directory</p>
            </a>
        @endif

        @if (auth()->user()?->canViewSiteDirectory())
            <a href="{{ route('sites.index') }}" class="panel block p-5 transition hover:border-brand-300 hover:shadow-[0_14px_34px_rgba(15,23,42,0.08)]">
                <p class="text-xs font-semibold uppercase tracking-wide text-brand-500">Locations</p>
                <h2 class="mt-2 text-lg font-semibold text-slate-950">Sites</h2>
                <p class="mt-3 text-sm leading-6 text-slate-600">
                    {{ auth()->user()?->canManageSites() ? 'Manage site records, addresses, map coordinates, permits, and which organisation each site belongs to.' : 'View site records, addresses, map coordinates, permits, and site ownership.' }}
                </p>
                <p class="mt-4 text-sm font-semibold text-brand-700">Open site directory</p>
            </a>
        @endif
    </div>
</x-layouts.app>
