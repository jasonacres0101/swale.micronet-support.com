<x-layouts.guest :title="'Login | '.config('app.name')">
    <div class="space-y-6">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-brand-500">Secure access</p>
            <h1 class="mt-2 text-3xl font-bold text-slate-950">CCTV monitoring dashboard</h1>
            <p class="mt-3 text-sm text-slate-600">
                Sign in to manage sites, review status, open camera web interfaces, and prepare PSA escalations.
            </p>
        </div>

        <form method="POST" action="{{ route('login.store') }}" class="space-y-4" autocomplete="off">
            @csrf

            <div>
                <label for="email" class="mb-2 block text-sm font-semibold text-slate-700">Email address</label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    value=""
                    autocomplete="off"
                    autocapitalize="none"
                    spellcheck="false"
                    required
                    autofocus
                    class="field-control"
                >
                @error('email')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="mb-2 block text-sm font-semibold text-slate-700">Password</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    autocomplete="off"
                    required
                    class="field-control"
                >
            </div>

            <label class="flex items-center gap-3 text-sm text-slate-600">
                <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-slate-300 text-brand-700">
                Keep me signed in
            </label>

            <button type="submit" class="btn-primary w-full">Sign in</button>
        </form>
    </div>

    <script>
        (() => {
            const clearDemoLogin = () => {
                const email = document.getElementById('email');
                const password = document.getElementById('password');

                if (email && email.value === 'admin@micronet.local') {
                    email.value = '';
                }

                if (password && password.value === 'password') {
                    password.value = '';
                }
            };

            clearDemoLogin();
            window.setTimeout(clearDemoLogin, 250);
            window.setTimeout(clearDemoLogin, 1000);
        })();
    </script>
</x-layouts.guest>
