<x-layouts.app
    :title="'Hikvision setup | '.config('app.name')"
    heading="Hikvision camera setup"
    subheading="Reference details for configuring Hikvision HTTP/HTTPS Alarm Server events."
>
    <div class="grid gap-5 xl:grid-cols-[1.1fr_0.9fr]">
        <section class="panel p-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-brand-500">Alarm receiver</p>
                    <h2 class="mt-2 text-xl font-semibold text-slate-950">Server details to add to each camera</h2>
                    <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">
                        Configure the camera in HTTP/HTTPS Alarm Server or HTTP Listening mode so it posts alarm events to this Laravel app.
                    </p>
                </div>

                <a href="{{ route('cameras.events') }}" class="btn-secondary shrink-0">View alarm admin</a>
            </div>

            <div class="mt-6 overflow-hidden rounded-lg border border-slate-200">
                <dl class="divide-y divide-slate-200 text-sm">
                    <div class="grid gap-1 bg-slate-50 px-4 py-3 sm:grid-cols-3 sm:gap-4">
                        <dt class="font-semibold text-slate-700">Mode</dt>
                        <dd class="font-medium text-slate-950 sm:col-span-2">HTTP/HTTPS Alarm Server, HTTP Listening, or ISAPI alarm upload</dd>
                    </div>
                    <div class="grid gap-1 px-4 py-3 sm:grid-cols-3 sm:gap-4">
                        <dt class="font-semibold text-slate-700">Request method</dt>
                        <dd class="font-medium text-slate-950 sm:col-span-2">POST</dd>
                    </div>
                    <div class="grid gap-1 bg-slate-50 px-4 py-3 sm:grid-cols-3 sm:gap-4">
                        <dt class="font-semibold text-slate-700">Alarm URL</dt>
                        <dd class="break-all font-mono text-xs text-slate-950 sm:col-span-2">{{ $alarmEndpoint }}</dd>
                    </div>
                    <div class="grid gap-1 px-4 py-3 sm:grid-cols-3 sm:gap-4">
                        <dt class="font-semibold text-slate-700">URL path only</dt>
                        <dd class="font-mono text-xs text-slate-950 sm:col-span-2">{{ $alarmPath }}</dd>
                    </div>
                    <div class="grid gap-1 bg-slate-50 px-4 py-3 sm:grid-cols-3 sm:gap-4">
                        <dt class="font-semibold text-slate-700">URL with token</dt>
                        <dd class="text-slate-700 sm:col-span-2">
                            If the camera cannot send custom headers, use
                            <span class="font-mono text-xs text-slate-950">{{ $alarmPath }}?token=YOUR_TOKEN</span>
                            in the Hikvision URL field.
                        </dd>
                    </div>
                    <div class="grid gap-1 bg-slate-50 px-4 py-3 sm:grid-cols-3 sm:gap-4">
                        <dt class="font-semibold text-slate-700">Server address</dt>
                        <dd class="text-slate-700 sm:col-span-2">Use the IP address or DNS name that the camera can reach for this app.</dd>
                    </div>
                    <div class="grid gap-1 px-4 py-3 sm:grid-cols-3 sm:gap-4">
                        <dt class="font-semibold text-slate-700">Port</dt>
                        <dd class="text-slate-700 sm:col-span-2">80 for HTTP, 443 for HTTPS, or the forwarded port if using NAT.</dd>
                    </div>
                    <div class="grid gap-1 bg-slate-50 px-4 py-3 sm:grid-cols-3 sm:gap-4">
                        <dt class="font-semibold text-slate-700">Payload type</dt>
                        <dd class="text-slate-700 sm:col-span-2">XML, JSON, or multipart form data are accepted.</dd>
                    </div>
                    <div class="grid gap-1 px-4 py-3 sm:grid-cols-3 sm:gap-4">
                        <dt class="font-semibold text-slate-700">Security token</dt>
                        <dd class="text-slate-700 sm:col-span-2">
                            Header name: <span class="font-mono text-xs text-slate-950">X-Hikvision-Token</span>.
                            @if ($tokenEnabled)
                                Send this header with the configured token value.
                            @else
                                Token is not currently enabled. Set <span class="font-mono text-xs text-slate-950">HIKVISION_ALARM_TOKEN</span> in the environment before production use.
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>
        </section>

        <aside class="panel p-6">
            <p class="text-xs font-semibold uppercase tracking-wide text-brand-500">Camera record</p>
            <h2 class="mt-2 text-xl font-semibold text-slate-950">Fields to add in this app</h2>
            <p class="mt-2 text-sm leading-6 text-slate-600">
                These values help the receiver match incoming Hikvision events to the correct camera.
            </p>

            <div class="mt-5 space-y-3">
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4">
                    <p class="text-sm font-semibold text-emerald-950">MAC address</p>
                    <p class="mt-1 text-sm leading-6 text-emerald-800">
                        Most important. Add the camera MAC address on the camera edit page. The app normalises it automatically, for example 44:19:B6:12:34:56 becomes 4419B6123456.
                    </p>
                </div>
                <div class="rounded-lg border border-slate-200 bg-white p-4">
                    <p class="text-sm font-semibold text-slate-950">IP address</p>
                    <p class="mt-1 text-sm leading-6 text-slate-600">
                        Used as a fallback if the event does not include a MAC address. If a MAC match is found, the app trusts the MAC and can update the stored IP if it changed.
                    </p>
                </div>
                <div class="rounded-lg border border-slate-200 bg-white p-4">
                    <p class="text-sm font-semibold text-slate-950">Site and organisation</p>
                    <p class="mt-1 text-sm leading-6 text-slate-600">
                        Assign the camera to the correct site and client/council so dashboards, reports, and permissions stay accurate.
                    </p>
                </div>
            </div>
        </aside>
    </div>

    <div class="mt-5 grid gap-5 lg:grid-cols-3">
        <section class="panel p-6">
            <p class="text-xs font-semibold uppercase tracking-wide text-brand-500">Required event data</p>
            <h2 class="mt-2 text-lg font-semibold text-slate-950">Useful Hikvision fields</h2>
            <ul class="mt-4 space-y-2 text-sm leading-6 text-slate-600">
                <li><span class="font-mono text-xs text-slate-950">macAddress</span> for primary camera matching.</li>
                <li><span class="font-mono text-xs text-slate-950">ipAddress</span> or <span class="font-mono text-xs text-slate-950">ipv4Address</span> for fallback matching.</li>
                <li><span class="font-mono text-xs text-slate-950">eventType</span> such as VMD, line crossing, or tamper.</li>
                <li><span class="font-mono text-xs text-slate-950">eventState</span> such as active or inactive.</li>
                <li><span class="font-mono text-xs text-slate-950">dateTime</span> for the event timestamp.</li>
                <li><span class="font-mono text-xs text-slate-950">channelID</span> and <span class="font-mono text-xs text-slate-950">eventDescription</span> where available.</li>
            </ul>
        </section>

        <section class="panel p-6">
            <p class="text-xs font-semibold uppercase tracking-wide text-brand-500">Camera checklist</p>
            <h2 class="mt-2 text-lg font-semibold text-slate-950">Before testing</h2>
            <ul class="mt-4 space-y-2 text-sm leading-6 text-slate-600">
                <li>Enable the required alarm type on the camera, such as motion detection or smart event.</li>
                <li>Enable alarm upload, notify surveillance centre, or HTTP listening action for that alarm.</li>
                <li>Confirm the camera can reach this server address and port across LAN, VPN, SIM, or firewall rules.</li>
                <li>Set camera time and NTP correctly so event times and reports are reliable.</li>
                <li>Use HTTPS if possible. If using HTTPS, make sure the camera trusts the certificate.</li>
            </ul>
        </section>

        <section class="panel p-6">
            <p class="text-xs font-semibold uppercase tracking-wide text-brand-500">Testing</p>
            <h2 class="mt-2 text-lg font-semibold text-slate-950">How to confirm it works</h2>
            <ul class="mt-4 space-y-2 text-sm leading-6 text-slate-600">
                <li>Trigger a real event, for example motion detection in front of the camera.</li>
                <li>Open Alarm admin and confirm a new Hikvision event appears.</li>
                <li>If the event is unmatched, check the MAC address and IP address on the camera record.</li>
                <li>If nothing appears, check firewall routing, token header, server URL, and Laravel logs.</li>
                <li>The camera should change to online after a valid event is received.</li>
            </ul>
        </section>
    </div>

    <div class="mt-5 panel p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-brand-500">Production security</p>
                <h2 class="mt-2 text-lg font-semibold text-slate-950">Set the Hikvision alarm token</h2>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">
                    Before using real cameras in production, add a shared token so only approved Hikvision devices can post alarm events into the app.
                </p>
            </div>

            <span @class([
                'inline-flex rounded-full px-3 py-1 text-xs font-semibold',
                'bg-emerald-100 text-emerald-700' => $tokenEnabled,
                'bg-amber-100 text-amber-700' => ! $tokenEnabled,
            ])>
                {{ $tokenEnabled ? 'Token enabled' : 'Token not enabled' }}
            </span>
        </div>

        <div class="mt-5 grid gap-4 lg:grid-cols-3">
            <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                <p class="text-sm font-semibold text-slate-950">1. Add this to .env</p>
                <pre class="mt-3 overflow-x-auto rounded-md bg-slate-950 p-3 text-xs text-slate-100"><code>HIKVISION_ALARM_TOKEN=replace-with-a-long-random-token</code></pre>
                <p class="mt-3 text-sm leading-6 text-slate-600">
                    Use a long random value. Do not use a customer name, site name, or simple password.
                </p>
            </div>

            <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                <p class="text-sm font-semibold text-slate-950">2. Send the token</p>
                <pre class="mt-3 overflow-x-auto rounded-md bg-slate-950 p-3 text-xs text-slate-100"><code>X-Hikvision-Token: same-token-value</code></pre>
                <p class="mt-3 text-sm leading-6 text-slate-600">
                    If the camera UI does not support custom headers, put it in the URL field instead:
                    <span class="font-mono text-xs text-slate-950">/api/hikvision/events?token=same-token-value</span>.
                </p>
            </div>

            <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                <p class="text-sm font-semibold text-slate-950">3. Refresh Laravel config</p>
                <pre class="mt-3 overflow-x-auto rounded-md bg-slate-950 p-3 text-xs text-slate-100"><code>php artisan config:clear
php artisan config:cache</code></pre>
                <p class="mt-3 text-sm leading-6 text-slate-600">
                    Run this after changing production environment variables so Laravel reads the new token.
                </p>
            </div>
        </div>
    </div>

    <div class="mt-5 panel p-6">
        <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-brand-500">Example payload</p>
                <h2 class="mt-2 text-lg font-semibold text-slate-950">Typical XML event body</h2>
                <p class="mt-2 text-sm leading-6 text-slate-600">
                    Hikvision models vary, but the receiver looks for these common fields and stores the full raw payload for troubleshooting.
                </p>
            </div>
            <a href="{{ route('cameras.index') }}" class="btn-secondary shrink-0">Open cameras</a>
        </div>

        <pre class="mt-5 overflow-x-auto rounded-lg border border-slate-200 bg-slate-950 p-4 text-xs leading-6 text-slate-100"><code>&lt;EventNotificationAlert&gt;
    &lt;ipAddress&gt;10.0.0.20&lt;/ipAddress&gt;
    &lt;macAddress&gt;44:19:B6:12:34:56&lt;/macAddress&gt;
    &lt;eventType&gt;VMD&lt;/eventType&gt;
    &lt;eventState&gt;active&lt;/eventState&gt;
    &lt;eventDescription&gt;Motion detected&lt;/eventDescription&gt;
    &lt;dateTime&gt;2026-04-26T10:30:00+01:00&lt;/dateTime&gt;
    &lt;channelID&gt;1&lt;/channelID&gt;
&lt;/EventNotificationAlert&gt;</code></pre>
    </div>

    <div class="mt-5 panel p-6">
        <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-brand-500">Connection test</p>
                <h2 class="mt-2 text-lg font-semibold text-slate-950">PowerShell test command</h2>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">
                    Enter a camera MAC address and optional token, then copy the generated command into PowerShell to simulate a Hikvision camera alarm.
                </p>
            </div>
        </div>

        <div class="mt-5 grid gap-4 md:grid-cols-2">
            <label class="block">
                <span class="text-sm font-semibold text-slate-700">Camera MAC address</span>
                <input
                    type="text"
                    value="44:19:B6:12:34:56"
                    placeholder="44:19:B6:12:34:56"
                    data-powershell-mac
                    class="mt-2 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-950 shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
                >
                <span class="mt-1 block text-xs text-slate-500">Use the MAC address from the camera record or Hikvision device label.</span>
            </label>

            <label class="block">
                <span class="text-sm font-semibold text-slate-700">Alarm token</span>
                <input
                    type="text"
                    value=""
                    placeholder="Leave blank if token is not enabled"
                    data-powershell-token
                    class="mt-2 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-950 shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
                >
                <span class="mt-1 block text-xs text-slate-500">This is only used in your browser to build the command. It is not saved.</span>
            </label>
        </div>

        <pre class="mt-5 overflow-x-auto rounded-lg border border-slate-200 bg-slate-950 p-4 text-xs leading-6 text-slate-100"><code data-powershell-command>$AlarmUrl = '{{ $alarmEndpoint }}'
$Token = ''
$Headers = @{}

if ($Token) {
    $Headers['X-Hikvision-Token'] = $Token
}

$Body = @'
&lt;EventNotificationAlert&gt;
    &lt;ipAddress&gt;10.0.0.20&lt;/ipAddress&gt;
    &lt;macAddress&gt;44:19:B6:12:34:56&lt;/macAddress&gt;
    &lt;eventType&gt;VMD&lt;/eventType&gt;
    &lt;eventState&gt;active&lt;/eventState&gt;
    &lt;eventDescription&gt;PowerShell test alarm&lt;/eventDescription&gt;
    &lt;dateTime&gt;2026-04-26T10:30:00+01:00&lt;/dateTime&gt;
    &lt;channelID&gt;1&lt;/channelID&gt;
&lt;/EventNotificationAlert&gt;
'@

Invoke-RestMethod -Method Post -Uri $AlarmUrl -ContentType 'application/xml' -Headers $Headers -Body $Body</code></pre>

        <div class="mt-4 rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm leading-6 text-slate-600">
            Expected result: PowerShell should return <span class="font-mono text-xs text-slate-950">success = true</span> and <span class="font-mono text-xs text-slate-950">message = Event received</span>. If the MAC/IP does not match a camera record, the event will still be saved and shown as unmatched in Alarm admin.
        </div>
    </div>

    @push('scripts')
        <script>
            (() => {
                const macInput = document.querySelector('[data-powershell-mac]');
                const tokenInput = document.querySelector('[data-powershell-token]');
                const commandOutput = document.querySelector('[data-powershell-command]');

                if (! macInput || ! tokenInput || ! commandOutput) {
                    return;
                }

                const escapePowerShell = (value) => value.replaceAll("'", "''");
                const escapeXml = (value) => value
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;')
                    .replaceAll("'", '&apos;');

                const renderCommand = () => {
                    const macAddress = escapeXml(macInput.value.trim() || '44:19:B6:12:34:56');
                    const token = escapePowerShell(tokenInput.value.trim());

                    commandOutput.textContent = `$AlarmUrl = '{{ $alarmEndpoint }}'
$Token = '${token}'
$Headers = @{}

if ($Token) {
    $Headers['X-Hikvision-Token'] = $Token
}

$Body = @'
<EventNotificationAlert>
    <ipAddress>10.0.0.20</ipAddress>
    <macAddress>${macAddress}</macAddress>
    <eventType>VMD</eventType>
    <eventState>active</eventState>
    <eventDescription>PowerShell test alarm</eventDescription>
    <dateTime>2026-04-26T10:30:00+01:00</dateTime>
    <channelID>1</channelID>
</EventNotificationAlert>
'@

Invoke-RestMethod -Method Post -Uri $AlarmUrl -ContentType 'application/xml' -Headers $Headers -Body $Body`;
                };

                macInput.addEventListener('input', renderCommand);
                tokenInput.addEventListener('input', renderCommand);
                renderCommand();
            })();
        </script>
    @endpush
</x-layouts.app>
