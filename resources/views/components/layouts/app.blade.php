@props([
    'title' => config('app.name'),
    'heading' => 'Control centre',
    'subheading' => null,
    'fullWidth' => false,
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('styles')
    </head>
    <body class="min-h-screen">
        <div @class([
            'app-shell flex min-h-screen flex-col px-4 py-4 sm:px-6 lg:px-8',
            'mx-auto max-w-7xl' => ! $fullWidth,
        ])>
            <header class="mb-6 overflow-hidden rounded-lg border border-[#020f40]/10 bg-white/95 shadow-sm backdrop-blur">
                <div class="flex flex-col border-b border-[#020f40]/10 px-4 py-3 lg:flex-row lg:items-center lg:justify-between lg:px-5">
                    <div class="flex min-w-0 items-center gap-4">
                        <a href="{{ route('dashboard') }}" class="flex shrink-0 items-center">
                            <img
                                src="{{ asset('images/micronet-logo.svg') }}"
                                alt="Micronet Solutions"
                                class="w-auto shrink-0"
                                style="height: 40px;"
                            >
                        </a>
                        <div class="hidden h-8 w-px bg-[#020f40]/10 sm:block"></div>
                        <p class="min-w-0 text-sm font-semibold text-[#020f40] sm:text-base">CCTV monitoring platform</p>
                    </div>

                    <div class="mt-3 flex flex-wrap items-center gap-3 lg:mt-0">
                        <div class="rounded-md border border-[#020f40]/10 bg-slate-50 px-3 py-2 text-left text-xs text-slate-600 sm:text-right">
                            <p class="font-semibold text-[#020f40]">{{ auth()->user()?->name }}</p>
                            <p>{{ \App\Models\User::availableRoles()[auth()->user()?->role ?? 'viewer'] ?? 'User' }}</p>
                            @if (auth()->user()?->isClient())
                                <p class="mt-1 text-slate-500">{{ auth()->user()?->organisation?->name ?: 'No client organisation set' }}</p>
                            @endif
                        </div>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn-secondary">Log out</button>
                        </form>
                    </div>
                </div>

                <div class="flex flex-col gap-4 px-4 py-4 lg:flex-row lg:items-center lg:justify-between lg:px-5">
                    <div class="min-w-0">
                        <h1 class="text-xl font-semibold leading-tight text-[#020f40] sm:text-2xl">{{ $heading }}</h1>
                        @if ($subheading)
                            <p class="mt-1 max-w-3xl text-sm leading-6 text-slate-600">{{ $subheading }}</p>
                        @endif
                    </div>

                    <nav class="flex w-full flex-wrap items-center gap-1 rounded-lg border border-[#020f40]/10 bg-slate-50 p-1 text-sm font-semibold lg:w-auto">
                        <a href="{{ route('dashboard') }}" @class([
                            'rounded-md px-3 py-1.5 transition',
                            'bg-[#020f40] text-white shadow-sm' => request()->routeIs('dashboard'),
                            'text-slate-600 hover:bg-orange-50 hover:text-[#020f40]' => ! request()->routeIs('dashboard'),
                        ])>Dashboard</a>
                        <a href="{{ route('cameras.index') }}" @class([
                            'rounded-md px-3 py-1.5 transition',
                            'bg-[#020f40] text-white shadow-sm' => request()->routeIs('cameras.index', 'cameras.create', 'cameras.show', 'cameras.edit'),
                            'text-slate-600 hover:bg-orange-50 hover:text-[#020f40]' => ! request()->routeIs('cameras.index', 'cameras.create', 'cameras.show', 'cameras.edit'),
                        ])>Cameras</a>
                        <a href="{{ route('cameras.map') }}" @class([
                            'rounded-md px-3 py-1.5 transition',
                            'bg-[#020f40] text-white shadow-sm' => request()->routeIs('cameras.map'),
                            'text-slate-600 hover:bg-orange-50 hover:text-[#020f40]' => ! request()->routeIs('cameras.map'),
                        ])>Map</a>
                        <a href="{{ route('reports.index') }}" @class([
                            'rounded-md px-3 py-1.5 transition',
                            'bg-[#020f40] text-white shadow-sm' => request()->routeIs('reports.*'),
                            'text-slate-600 hover:bg-orange-50 hover:text-[#020f40]' => ! request()->routeIs('reports.*'),
                        ])>Reports</a>
                        <a href="{{ route('maintenance.index') }}" @class([
                            'rounded-md px-3 py-1.5 transition',
                            'bg-[#020f40] text-white shadow-sm' => request()->routeIs('maintenance.*'),
                            'text-slate-600 hover:bg-orange-50 hover:text-[#020f40]' => ! request()->routeIs('maintenance.*'),
                        ])>Maintenance</a>
                        @if (auth()->user()?->canViewSettings())
                            <a href="{{ route('settings.index') }}" @class([
                                'rounded-md px-3 py-1.5 transition',
                                'bg-[#020f40] text-white shadow-sm' => request()->routeIs('settings.index', 'settings.hikvision-setup', 'settings.maintenance-task-types.*', 'users.*', 'cameras.events', 'organisations.*', 'sites.*'),
                                'text-slate-600 hover:bg-orange-50 hover:text-[#020f40]' => ! request()->routeIs('settings.index', 'settings.hikvision-setup', 'settings.maintenance-task-types.*', 'users.*', 'cameras.events', 'organisations.*', 'sites.*'),
                            ])>Settings</a>
                        @endif
                    </nav>
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
