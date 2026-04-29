<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? config('app.name') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen">
        <div class="relative flex min-h-screen items-center justify-center overflow-hidden px-6 py-16">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,_rgba(34,197,94,0.1),_transparent_20%),radial-gradient(circle_at_80%_10%,_rgba(220,38,38,0.12),_transparent_18%),radial-gradient(circle_at_50%_100%,_rgba(62,111,153,0.18),_transparent_30%)]"></div>
            <div class="panel relative w-full max-w-md p-8">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
