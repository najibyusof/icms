<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $resolvedTitle = trim($__env->yieldContent('title'));
        $routeName = request()->route()?->getName() ?? 'dashboard';
        $primarySegment = str($routeName)->before('.')->replace('-', ' ')->headline()->toString();
        $pageTitle =
            $resolvedTitle !== ''
                ? $resolvedTitle
                : match ($primarySegment) {
                    'Courses' => 'Course Management',
                    'Programmes' => 'Programme Management',
                    'Groups' => 'Group Management',
                    'Users' => 'User Management',
                    default => $primarySegment,
                };

        $currentAction = str($routeName)->after('.')->replace('.', ' ')->headline()->toString();
        $breadcrumbModuleRoute = match (str($routeName)->before('.')->toString()) {
            'courses' => Route::has('courses.index') ? route('courses.index') : null,
            'programmes' => Route::has('programmes.index') ? route('programmes.index') : null,
            'groups' => Route::has('groups.index') ? route('groups.index') : null,
            'users' => Route::has('users.index') ? route('users.index') : null,
            default => null,
        };

        $breadcrumbModuleLabel = match (str($routeName)->before('.')->toString()) {
            'courses' => 'Course Management',
            'programmes' => 'Programme Management',
            'groups' => 'Group Management',
            'users' => 'User Management',
            default => null,
        };

        $isGuestAuthPage = !auth()->check() && request()->routeIs('login', 'password.*');
        $isGuestDashboardPage = !auth()->check() && request()->routeIs('dashboard');
        $showGuestLoginLink = !auth()->check() && !$isGuestAuthPage && !$isGuestDashboardPage && Route::has('login');
    @endphp
    <title>{{ $pageTitle }} | {{ config('app.name', 'Academic Management System') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>

<body class="min-h-screen">
    <div class="absolute inset-0 -z-10 overflow-hidden">
        <div class="absolute -left-24 -top-16 h-56 w-56 rounded-full bg-red-200/70 blur-3xl"></div>
        <div class="absolute -right-32 top-28 h-72 w-72 rounded-full bg-rose-300/60 blur-3xl"></div>
    </div>

    <div class="mx-auto flex min-h-screen max-w-[1600px] gap-6 px-4 py-6 sm:px-6 lg:px-8">
        @auth
            <aside class="hidden w-80 shrink-0 lg:flex lg:flex-col">
                <div
                    class="sticky top-6 flex h-[calc(100vh-3rem)] flex-col overflow-hidden rounded-[2rem] bg-[linear-gradient(170deg,rgba(47,6,6,0.99),rgba(90,14,18,0.96))] p-4 text-white shadow-[0_30px_80px_-30px_rgba(47,6,6,0.8)]">
                    <a href="{{ route('dashboard') }}"
                        class="flex items-center gap-3 rounded-2xl border border-white/10 bg-white/6 px-4 py-3.5 transition hover:bg-white/10">
                        <div
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white/15 text-xs font-black tracking-wider text-white">
                            IC
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-[0.28em] text-red-200/60">ICMS</p>
                            <p class="text-sm font-bold tracking-tight text-white">Academic Control</p>
                        </div>
                    </a>

                    <nav class="mt-3 flex-1 space-y-3 overflow-hidden">
                        <div class="space-y-0.5">
                            <p class="ams-sidebar-section-title mb-1.5">Main Navigation</p>
                            <a href="{{ route('dashboard') }}"
                                class="ams-sidebar-link {{ request()->routeIs('dashboard') ? 'ams-sidebar-link-active' : '' }}">
                                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 12l8-8 8 8M5 10v10h14V10" />
                                </svg>
                                <span>Dashboard</span>
                            </a>

                            @hasanyrole('Admin|admin|Lecturer|lecturer|Programme Coordinator|coordinator')
                                <a href="{{ route('courses.index') }}"
                                    class="ams-sidebar-link {{ request()->routeIs('courses.*') ? 'ams-sidebar-link-active' : '' }}">
                                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                    <span>Course Management</span>
                                </a>

                                <a href="{{ route('programmes.index') }}"
                                    class="ams-sidebar-link {{ request()->routeIs('programmes.*') ? 'ams-sidebar-link-active' : '' }}">
                                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <span>Programmes</span>
                                </a>

                                <a href="{{ route('groups.index') }}"
                                    class="ams-sidebar-link {{ request()->routeIs('groups.*') ? 'ams-sidebar-link-active' : '' }}">
                                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <span>Group Management</span>
                                </a>
                            @endhasanyrole

                            @hasanyrole('Admin|admin')
                                @if (Route::has('users.index'))
                                    <a href="{{ route('users.index') }}"
                                        class="ams-sidebar-link {{ request()->routeIs('users.*') ? 'ams-sidebar-link-active' : '' }}">
                                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        <span>User Management</span>
                                    </a>
                                @endif
                            @endhasanyrole
                        </div>

                        @auth
                            <div class="space-y-0.5">
                                <p class="ams-sidebar-section-title mb-1.5">Administration</p>

                                @role('Admin|admin')
                                    <a href="{{ route('workflows.manage.definitions') }}"
                                        class="ams-sidebar-link {{ request()->routeIs('workflows.manage.*') ? 'ams-sidebar-link-active' : '' }}">
                                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                        <span>Workflow Setup</span>
                                    </a>

                                    <a href="{{ route('notifications.settings') }}"
                                        class="ams-sidebar-link {{ request()->routeIs('notifications.*') ? 'ams-sidebar-link-active' : '' }}">
                                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m-1 0v1a3 3 0 006 0v-1" />
                                        </svg>
                                        <span>Notifications</span>
                                    </a>

                                    <a href="{{ route('integration.sso.settings') }}"
                                        class="ams-sidebar-link {{ request()->routeIs('integration.*') ? 'ams-sidebar-link-active' : '' }}">
                                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                                        </svg>
                                        <span>SSO Settings</span>
                                    </a>
                                @endrole

                                @hasanyrole('Admin|admin|Lecturer|lecturer|Reviewer|reviewer|Approver|approver|Programme
                                    Coordinator|coordinator')
                                    <a href="{{ route('jsu.manage.index') }}"
                                        class="ams-sidebar-link {{ request()->routeIs('jsu.*') ? 'ams-sidebar-link-active' : '' }}">
                                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 17v-2m3 2v-4m3 4V7m4 10H5a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2z" />
                                        </svg>
                                        <span>JSU</span>
                                    </a>
                                @endhasanyrole
                            </div>
                        @endauth
                    </nav>

                    <div class="mt-3 rounded-2xl border border-white/10 bg-white/6 p-3.5">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-white/20 text-xs font-bold text-white">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <p class="truncate text-[13px] font-semibold leading-tight text-white">
                                    {{ auth()->user()->name }}</p>
                                <p class="truncate text-[10px] leading-snug text-red-200/55">
                                    {{ auth()->user()->email }}</p>
                            </div>
                        </div>
                        <div class="mt-3 flex gap-2">
                            <a href="{{ route('password.change') }}"
                                class="flex-1 rounded-lg border border-white/15 py-1.5 text-center text-[11px] font-semibold text-red-100/80 transition hover:bg-white/10">Password</a>
                            <form method="POST" action="{{ route('logout') }}" class="flex-1">
                                @csrf
                                <button type="submit"
                                    class="w-full rounded-lg bg-white/90 py-1.5 text-[11px] font-semibold text-red-950 transition hover:bg-white">Logout</button>
                            </form>
                        </div>
                    </div>
                </div>
            </aside>
        @endauth

        <div class="min-w-0 flex-1">
            @auth
                <header class="ams-card p-4 sm:p-5 lg:hidden">
                    <div class="flex flex-col gap-4">
                        <div class="flex items-center justify-between gap-4">
                            <a href="{{ route('dashboard') }}"
                                class="text-lg font-semibold tracking-tight text-red-950">ICMS</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="ams-button-secondary px-3 py-2 text-xs">Logout</button>
                            </form>
                        </div>

                        <nav class="flex flex-wrap gap-2 text-sm">
                            <a href="{{ route('dashboard') }}"
                                class="rounded-full border px-3 py-2 font-semibold {{ request()->routeIs('dashboard') ? 'border-red-900 bg-red-900 text-white' : 'border-red-200 bg-white text-red-800' }}">Dashboard</a>
                            @hasanyrole('Admin|admin|Lecturer|lecturer|Programme Coordinator|coordinator')
                                <a href="{{ route('courses.index') }}"
                                    class="rounded-full border px-3 py-2 font-semibold {{ request()->routeIs('courses.*') ? 'border-red-900 bg-red-900 text-white' : 'border-red-200 bg-white text-red-800' }}">Courses</a>
                                <a href="{{ route('programmes.index') }}"
                                    class="rounded-full border px-3 py-2 font-semibold {{ request()->routeIs('programmes.*') ? 'border-red-900 bg-red-900 text-white' : 'border-red-200 bg-white text-red-800' }}">Programmes</a>
                                <a href="{{ route('groups.index') }}"
                                    class="rounded-full border px-3 py-2 font-semibold {{ request()->routeIs('groups.*') ? 'border-red-900 bg-red-900 text-white' : 'border-red-200 bg-white text-red-800' }}">Groups</a>
                            @endhasanyrole
                            @hasanyrole('Admin|admin')
                                @if (Route::has('users.index'))
                                    <a href="{{ route('users.index') }}"
                                        class="rounded-full border px-3 py-2 font-semibold {{ request()->routeIs('users.*') ? 'border-red-900 bg-red-900 text-white' : 'border-red-200 bg-white text-red-800' }}">Users</a>
                                @endif
                            @endhasanyrole
                        </nav>
                    </div>
                </header>
            @endauth

            <main class="space-y-6 py-2 lg:py-0">
                @if ($showGuestLoginLink)
                    <div class="flex justify-end">
                        <a href="{{ route('login') }}"
                            class="inline-flex items-center rounded-xl border border-red-200 bg-white/90 px-4 py-2 text-sm font-semibold text-red-800 transition hover:border-red-300 hover:bg-white">Login</a>
                    </div>
                @endif

                @if (!$isGuestAuthPage && !$isGuestDashboardPage)
                    <section class="ams-card p-5 sm:p-6">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                            <div>
                                <nav class="flex flex-wrap items-center gap-2 text-sm">
                                    <a href="{{ route('dashboard') }}" class="ams-breadcrumb-link">Dashboard</a>

                                    @if ($breadcrumbModuleLabel && $breadcrumbModuleLabel !== 'Dashboard')
                                        <span class="text-red-300">/</span>
                                        @if ($breadcrumbModuleRoute)
                                            <a href="{{ $breadcrumbModuleRoute }}"
                                                class="ams-breadcrumb-link">{{ $breadcrumbModuleLabel }}</a>
                                        @else
                                            <span class="text-red-700">{{ $breadcrumbModuleLabel }}</span>
                                        @endif
                                    @endif

                                    @if (!request()->routeIs('dashboard') && !str($routeName)->endsWith('.index'))
                                        <span class="text-red-300">/</span>
                                        <span class="font-semibold text-red-900">{{ $currentAction }}</span>
                                    @endif
                                </nav>
                                <h1 class="mt-3 text-2xl font-semibold tracking-tight text-red-950 sm:text-3xl">
                                    {{ $pageTitle }}</h1>
                                <p class="mt-1 text-sm text-red-700">Module workspace and operational navigation.</p>
                            </div>

                            @auth
                                <div class="rounded-2xl border border-red-100 bg-red-50/70 px-4 py-3 text-right">
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-red-600">Signed in</p>
                                    <p class="mt-1 text-sm font-semibold text-red-950">{{ auth()->user()->name }}</p>
                                </div>
                            @endauth
                        </div>
                    </section>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>

</html>
