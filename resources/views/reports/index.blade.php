<x-layouts.app
    :title="'Reports | '.config('app.name')"
    heading="Reports"
    subheading="Generate council, client, site, camera, uptime, and Hikvision event reports from the monitoring data already captured by the platform."
>
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <a href="{{ route('reports.uptime') }}" class="panel block overflow-hidden transition hover:border-brand-300 hover:shadow-[0_14px_34px_rgba(15,23,42,0.08)]">
            <div class="bg-brand-900 px-5 py-4 text-white">
                <p class="text-xs font-semibold uppercase tracking-wide text-brand-100">Availability</p>
                <h2 class="mt-2 text-xl font-semibold">Uptime report</h2>
            </div>
            <div class="p-5">
                <p class="text-sm leading-6 text-slate-600">Review monitored time, online/offline duration, incident counts, and longest outages for selected cameras.</p>
                <p class="mt-5 text-sm font-semibold text-brand-700">Open uptime report</p>
            </div>
        </a>

        <a href="{{ route('reports.events') }}" class="panel block overflow-hidden transition hover:border-brand-300 hover:shadow-[0_14px_34px_rgba(15,23,42,0.08)]">
            <div class="bg-slate-900 px-5 py-4 text-white">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-300">Hikvision</p>
                <h2 class="mt-2 text-xl font-semibold">Event report</h2>
            </div>
            <div class="p-5">
                <p class="text-sm leading-6 text-slate-600">Filter alarm and motion events by date, organisation, site, camera, and event type.</p>
                <p class="mt-5 text-sm font-semibold text-brand-700">Open event report</p>
            </div>
        </a>

        <a href="{{ route('reports.sites') }}" class="panel block overflow-hidden transition hover:border-brand-300 hover:shadow-[0_14px_34px_rgba(15,23,42,0.08)]">
            <div class="bg-emerald-900 px-5 py-4 text-white">
                <p class="text-xs font-semibold uppercase tracking-wide text-emerald-100">Estate</p>
                <h2 class="mt-2 text-xl font-semibold">Site summary</h2>
            </div>
            <div class="p-5">
                <p class="text-sm leading-6 text-slate-600">Summarise site health, camera totals, current status, latest event time, and connectivity split.</p>
                <p class="mt-5 text-sm font-semibold text-brand-700">Open site summary</p>
            </div>
        </a>

        <a href="{{ route('reports.clients') }}" class="panel block overflow-hidden transition hover:border-brand-300 hover:shadow-[0_14px_34px_rgba(15,23,42,0.08)]">
            <div class="bg-amber-700 px-5 py-4 text-white">
                <p class="text-xs font-semibold uppercase tracking-wide text-amber-100">Client owned</p>
                <h2 class="mt-2 text-xl font-semibold">Client report</h2>
            </div>
            <div class="p-5">
                <p class="text-sm leading-6 text-slate-600">Group client-owned camera performance by organisation with uptime, incident, site, and event summaries.</p>
                <p class="mt-5 text-sm font-semibold text-brand-700">Open client report</p>
            </div>
        </a>
    </div>

    <section class="panel mt-5 p-5">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Reporting note</p>
        <p class="mt-2 text-sm leading-6 text-slate-600">
            Uptime is calculated from camera status logs. Where a camera has no status log before the selected start date,
            the row is clearly marked as estimated from its current status so missing historical data is not hidden.
        </p>
    </section>
</x-layouts.app>
