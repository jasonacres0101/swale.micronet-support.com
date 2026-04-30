<x-layouts.app
    :title="'Camera Email Settings | '.config('app.name')"
    heading="Camera email settings"
    subheading="Configure the IMAP mailbox used to import scheduled camera screenshot emails."
>
    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_22rem]">
        <section class="panel p-6">
            <div class="flex flex-col gap-3 border-b border-slate-200 pb-5 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h2 class="text-xl font-bold text-slate-950">Snapshot mailbox</h2>
                    <p class="mt-1 text-sm text-slate-500">The scheduler checks this mailbox every five minutes when ingest is enabled. POP3 does not require the PHP IMAP extension.</p>
                </div>

                @if ($imapEnabled)
                    <span class="rounded-md bg-emerald-100 px-3 py-1 text-sm font-semibold text-emerald-700">PHP IMAP available</span>
                @else
                    <span class="rounded-md bg-amber-100 px-3 py-1 text-sm font-semibold text-amber-700">Use POP3</span>
                @endif
            </div>

            @if (session('email_test_status'))
                <div class="mt-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('email_test_status') }}
                </div>
            @endif

            @if (session('email_test_error'))
                <div class="mt-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {{ session('email_test_error') }}
                </div>
            @endif

            <form id="camera-email-form" method="POST" action="{{ route('settings.camera-email.update') }}" class="mt-6 grid gap-5 lg:grid-cols-2">
                @csrf

                <div class="lg:col-span-2">
                    <label class="flex items-center gap-3 rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700">
                        <input type="checkbox" name="enabled" value="1" @checked(old('enabled', $settings['enabled'])) class="h-4 w-4 rounded border-slate-300 text-brand-700">
                        Enable scheduled email ingest
                    </label>
                </div>

                <div>
                    <label for="protocol" class="mb-2 block text-sm font-semibold text-slate-700">Protocol</label>
                    @php($protocol = old('protocol', $settings['protocol'] ?? 'pop3'))
                    <select id="protocol" name="protocol" class="field-control">
                        <option value="pop3" @selected($protocol === 'pop3')>POP3 - no PHP extension needed</option>
                        <option value="imap" @selected($protocol === 'imap')>IMAP - requires PHP IMAP extension</option>
                    </select>
                    @error('protocol') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="host" class="mb-2 block text-sm font-semibold text-slate-700">Mail server</label>
                    <input id="host" name="host" type="text" value="{{ old('host', $settings['host']) }}" required class="field-control" placeholder="mail.example.com">
                    @error('host') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="port" class="mb-2 block text-sm font-semibold text-slate-700">Port</label>
                    <input id="port" name="port" type="number" min="1" max="65535" value="{{ old('port', $settings['port']) }}" required class="field-control">
                    @error('port') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="encryption" class="mb-2 block text-sm font-semibold text-slate-700">Encryption</label>
                    @php($encryption = old('encryption', $settings['encryption']))
                    <select id="encryption" name="encryption" class="field-control">
                        <option value="ssl" @selected($encryption === 'ssl')>SSL</option>
                        <option value="tls" @selected($encryption === 'tls')>TLS</option>
                        <option value="none" @selected($encryption === 'none')>None</option>
                    </select>
                    @error('encryption') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="mailbox" class="mb-2 block text-sm font-semibold text-slate-700">Mailbox folder</label>
                    <input id="mailbox" name="mailbox" type="text" value="{{ old('mailbox', $settings['mailbox']) }}" required class="field-control">
                    @error('mailbox') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="username" class="mb-2 block text-sm font-semibold text-slate-700">Username</label>
                    <input id="username" name="username" type="text" value="{{ old('username', $settings['username']) }}" required class="field-control" autocomplete="off">
                    @error('username') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="password" class="mb-2 block text-sm font-semibold text-slate-700">Password</label>
                    <input id="password" name="password" type="password" class="field-control" autocomplete="new-password" placeholder="{{ filled($settings['password']) ? 'Leave blank to keep current password' : '' }}">
                    @error('password') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="lg:col-span-2 grid gap-3 sm:grid-cols-3">
                    <label class="flex items-center gap-3 rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700">
                        <input type="checkbox" name="validate_cert" value="1" @checked(old('validate_cert', $settings['validate_cert'])) class="h-4 w-4 rounded border-slate-300 text-brand-700">
                        Validate certificate
                    </label>

                    <label class="flex items-center gap-3 rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700">
                        <input type="checkbox" name="mark_seen_after_import" value="1" @checked(old('mark_seen_after_import', $settings['mark_seen_after_import'])) class="h-4 w-4 rounded border-slate-300 text-brand-700">
                        Mark imported emails seen
                    </label>

                    <label class="flex items-center gap-3 rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700">
                        <input type="checkbox" name="delete_after_import" value="1" @checked(old('delete_after_import', $settings['delete_after_import'])) class="h-4 w-4 rounded border-slate-300 text-brand-700">
                        Delete after import
                    </label>
                </div>

                <div class="lg:col-span-2 flex flex-wrap items-center gap-3 border-t border-slate-200 pt-5">
                    <button type="submit" class="btn-primary">Save settings</button>
                    <button type="submit" formaction="{{ route('settings.camera-email.test') }}" formmethod="POST" class="btn-secondary">Test connection</button>
                    <a href="{{ route('settings.index') }}" class="btn-secondary">Back to settings</a>
                </div>
            </form>
        </section>

        <aside class="space-y-6">
            <section class="panel p-6">
                <h2 class="text-xl font-bold text-slate-950">Camera sender format</h2>
                <div class="mt-4 space-y-3 text-sm text-slate-600">
                    <p class="rounded-lg bg-slate-50 px-4 py-4">The app matches the sender address before the @ symbol against the camera serial number.</p>
                    <p class="rounded-lg bg-slate-50 px-4 py-4">Example: <span class="font-semibold text-slate-950">DS123456@example.com</span> matches serial <span class="font-semibold text-slate-950">DS123456</span>.</p>
                </div>
            </section>

            <section class="panel p-6">
                <h2 class="text-xl font-bold text-slate-950">Recommended mode</h2>
                <p class="mt-3 text-sm leading-6 text-slate-600">Use POP3 with SSL on port 995 if cPanel cannot provide the PHP IMAP extension. Leave imported emails in the mailbox unless you explicitly want the app to delete them.</p>
                <pre class="mt-4 overflow-x-auto rounded-lg bg-slate-950 p-4 text-xs text-white">Protocol: POP3
Port: 995
Encryption: SSL</pre>
            </section>
        </aside>
    </div>
</x-layouts.app>
