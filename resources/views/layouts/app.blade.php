<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Academic Management System') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>

<body class="min-h-screen">
    <div class="absolute inset-0 -z-10 overflow-hidden">
        <div class="absolute -left-24 -top-16 h-56 w-56 rounded-full bg-red-200/70 blur-3xl"></div>
        <div class="absolute -right-32 top-28 h-72 w-72 rounded-full bg-rose-300/60 blur-3xl"></div>
    </div>

    <header class="mx-auto max-w-7xl px-4 pt-6 sm:px-6 lg:px-8">
        <div
            class="flex items-center justify-between rounded-2xl border border-red-200/70 bg-white/75 px-4 py-3 backdrop-blur">
            <a href="{{ route('dashboard') }}" class="text-sm font-bold tracking-wide text-red-950">ICMS</a>

            <div class="flex items-center gap-3 text-sm">
                @auth
                    @if (auth()->user()->hasAnyRole(['Admin', 'admin']))
                        <a href="{{ route('workflows.manage.definitions') }}"
                            class="rounded-lg border border-red-300 px-3 py-1.5 font-medium text-red-900 hover:bg-red-50">Workflow
                            Setup</a>
                    @endif
                    <a href="{{ route('password.change') }}"
                        class="rounded-lg border border-red-300 px-3 py-1.5 font-medium text-red-900 hover:bg-red-50">Change
                        Password</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="rounded-lg bg-red-900 px-3 py-1.5 font-medium text-white hover:bg-red-800">Logout</button>
                    </form>
                @else
                    <a href="{{ route('login') }}"
                        class="rounded-lg bg-red-900 px-3 py-1.5 font-medium text-white hover:bg-red-800">Login</a>
                @endauth
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @yield('content')
    </main>
</body>

</html>
