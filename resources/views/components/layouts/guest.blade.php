@props([
    'title' => config('app.name'),
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen">
        <div class="app-shell relative flex min-h-screen items-center justify-center overflow-hidden px-6 py-16">
            <div class="absolute inset-0 bg-[linear-gradient(180deg,_rgba(255,165,0,0.08),_rgba(255,255,255,0)_240px)]"></div>
            <div class="panel relative w-full max-w-md p-8">
                <div class="mb-6 flex justify-center">
                    <img
                        src="{{ asset('images/micronet-logo.svg') }}"
                        alt="Micronet Solutions"
                        class="h-14 w-auto"
                    >
                </div>
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
