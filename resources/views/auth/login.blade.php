<x-layouts.guest :title="'Login | '.config('app.name')">
    <div class="space-y-6">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-brand-500">Secure access</p>
            <h1 class="mt-2 text-3xl font-bold text-slate-950">CCTV monitoring dashboard</h1>
            <p class="mt-3 text-sm text-slate-600">
                Sign in to manage sites, review status, open camera web interfaces, and prepare PSA escalations.
            </p>
        </div>

        <div class="rounded-lg border border-brand-100 bg-brand-50 px-4 py-3 text-sm text-brand-800">
            Demo login: <span class="font-semibold">admin@micronet.local</span> / <span class="font-semibold">password</span>
        </div>

        <form method="POST" action="{{ route('login.store') }}" class="space-y-4">
            @csrf

            <div>
                <label for="email" class="mb-2 block text-sm font-semibold text-slate-700">Email address</label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email', 'admin@micronet.local') }}"
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
                    value="password"
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
</x-layouts.guest>
