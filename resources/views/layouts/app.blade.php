<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? config('app.name') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('styles')
    </head>
    <body class="min-h-screen">
        <div class="mx-auto flex min-h-screen max-w-7xl flex-col px-4 py-4 sm:px-6 lg:px-8">
            <header class="panel mb-6 flex flex-col gap-4 px-6 py-5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-brand-500">Micronet CCTV</p>
                    <h1 class="mt-2 text-2xl font-bold text-slate-950">{{ $heading ?? 'Control centre' }}</h1>
                    @isset($subheading)
                        <p class="mt-1 text-sm text-slate-600">{{ $subheading }}</p>
                    @endisset
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <nav class="flex flex-wrap items-center gap-2 text-sm font-medium">
                        <a href="{{ route('dashboard') }}" @class([
                            'rounded-full px-4 py-2 transition',
                            'bg-brand-700 text-white' => request()->routeIs('dashboard'),
                            'text-slate-700 hover:bg-white' => ! request()->routeIs('dashboard'),
                        ])>Dashboard</a>
                        <a href="{{ route('cameras.index') }}" @class([
                            'rounded-full px-4 py-2 transition',
                            'bg-brand-700 text-white' => request()->routeIs('cameras.index', 'cameras.show', 'cameras.edit'),
                            'text-slate-700 hover:bg-white' => ! request()->routeIs('cameras.index', 'cameras.show', 'cameras.edit'),
                        ])>Cameras</a>
                        <a href="{{ route('cameras.map') }}" @class([
                            'rounded-full px-4 py-2 transition',
                            'bg-brand-700 text-white' => request()->routeIs('cameras.map'),
                            'text-slate-700 hover:bg-white' => ! request()->routeIs('cameras.map'),
                        ])>Full-screen map</a>
                    </nav>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn-secondary">Log out</button>
                    </form>
                </div>
            </header>

            @if (session('status'))
                <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('status') }}
                </div>
            @endif

            <main class="flex-1">
                {{ $slot }}
            </main>
        </div>

        @stack('scripts')
    </body>
</html>
