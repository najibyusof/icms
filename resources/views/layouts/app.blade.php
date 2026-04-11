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
                    class="sticky top-6 flex h-[calc(100vh-3rem)] flex-col overflow-hidden rounded-[32px] bg-[linear-gradient(180deg,rgba(47,6,6,0.98),rgba(95,15,19,0.94))] p-5 text-white shadow-[0_30px_80px_-30px_rgba(47,6,6,0.8)]">
                    <div class="rounded-[28px] border border-white/10 bg-white/8 p-5 backdrop-blur">
                        <a href="{{ route('dashboard') }}" class="block">
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-red-200/80">ICMS</p>
                            <h1 class="mt-3 text-2xl font-semibold tracking-tight">Academic Control</h1>
                            <p class="mt-2 text-sm text-red-100/75">Navigate courses, programmes, groups, and administrative
                                workflows from a unified workspace.</p>
                        </a>
                    </div>

                    <nav class="mt-6 flex-1 space-y-6 overflow-y-auto pr-1">
                        <div class="space-y-2">
                            <p class="ams-sidebar-section-title">Main Navigation</p>
                            <a href="{{ route('dashboard') }}"
                                class="ams-sidebar-link {{ request()->routeIs('dashboard') ? 'ams-sidebar-link-active' : '' }}">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                        d="M3 12l8-8 8 8M5 10v10h14V10" />
                                </svg>
                                <span>Dashboard</span>
                            </a>

                            @hasanyrole('Admin|admin|Lecturer|lecturer|Programme Coordinator|coordinator')
                                <a href="{{ route('courses.index') }}"
                                    class="ams-sidebar-link {{ request()->routeIs('courses.*') ? 'ams-sidebar-link-active' : '' }}">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                            d="M4 6.75A2.75 2.75 0 016.75 4h10.5A2.75 2.75 0 0120 6.75v10.5A2.75 2.75 0 0117.25 20H6.75A2.75 2.75 0 014 17.25V6.75z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                            d="M8 8h8M8 12h8M8 16h5" />
                                    </svg>
                                    <span>Course Management</span>
                                </a>

                                <a href="{{ route('programmes.index') }}"
                                    class="ams-sidebar-link {{ request()->routeIs('programmes.*') ? 'ams-sidebar-link-active' : '' }}">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                            d="M4 5.75A1.75 1.75 0 015.75 4h12.5A1.75 1.75 0 0120 5.75v12.5A1.75 1.75 0 0118.25 20H5.75A1.75 1.75 0 014 18.25V5.75z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                            d="M8 8h8M8 12h8M8 16h4" />
                                    </svg>
                                    <span>Programme Management</span>
                                </a>

                                <a href="{{ route('groups.index') }}"
                                    class="ams-sidebar-link {{ request()->routeIs('groups.*') ? 'ams-sidebar-link-active' : '' }}">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                            d="M16 11a4 4 0 10-8 0 4 4 0 008 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                            d="M3 20a7 7 0 0118 0" />
                                    </svg>
                                    <span>Group Management</span>
                                </a>
                            @endhasanyrole

                            @hasanyrole('Admin|admin')
                                @if (Route::has('users.index'))
                                    <a href="{{ route('users.index') }}"
                                        class="ams-sidebar-link {{ request()->routeIs('users.*') ? 'ams-sidebar-link-active' : '' }}">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                d="M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                d="M4 20a8 8 0 0116 0" />
                                        </svg>
                                        <span>User Management</span>
                                    </a>
                                @endif
                            @endhasanyrole
                        </div>

                        @auth
                            <div class="space-y-2">
                                <p class="ams-sidebar-section-title">Administration</p>

                                @role('Admin|admin')
                                    <a href="{{ route('workflows.manage.definitions') }}"
                                        class="ams-sidebar-link {{ request()->routeIs('workflows.manage.*') ? 'ams-sidebar-link-active' : '' }}">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                d="M10.325 4.317a1 1 0 011.35-.936l1.89.756a1 1 0 00.77 0l1.89-.756a1 1 0 011.35.936l.2 2.042a1 1 0 00.572.82l1.73.965a1 1 0 01.28 1.53l-1.31 1.58a1 1 0 000 1.274l1.31 1.58a1 1 0 01-.28 1.53l-1.73.965a1 1 0 00-.572.82l-.2 2.042a1 1 0 01-1.35.936l-1.89-.756a1 1 0 00-.77 0l-1.89.756a1 1 0 01-1.35-.936l-.2-2.042a1 1 0 00-.572-.82l-1.73-.965a1 1 0 01-.28-1.53l1.31-1.58a1 1 0 000-1.274l-1.31-1.58a1 1 0 01.28-1.53l1.73-.965a1 1 0 00.572-.82l.2-2.042z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <span>Workflow Setup</span>
                                    </a>

                                    <a href="{{ route('notifications.settings') }}"
                                        class="ams-sidebar-link {{ request()->routeIs('notifications.*') ? 'ams-sidebar-link-active' : '' }}">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                d="M9 17v1a3 3 0 006 0v-1" />
                                        </svg>
                                        <span>Notification Settings</span>
                                    </a>

                                    <a href="{{ route('integration.sso.settings') }}"
                                        class="ams-sidebar-link {{ request()->routeIs('integration.*') ? 'ams-sidebar-link-active' : '' }}">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                                        </svg>
                                        <span>SSO Settings</span>
                                    </a>
                                @endrole

                                @hasanyrole('Admin|admin|Lecturer|lecturer|Reviewer|reviewer|Approver|approver|Programme
                                    Coordinator|coordinator')
                                    <a href="{{ route('jsu.manage.index') }}"
                                        class="ams-sidebar-link {{ request()->routeIs('jsu.*') ? 'ams-sidebar-link-active' : '' }}">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                d="M9 17v-2m3 2v-4m3 4V7m4 10H5a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2z" />
                                        </svg>
                                        <span>JSU</span>
                                    </a>
                                @endhasanyrole
                            </div>
                        @endauth
                    </nav>

                    <div class="mt-6 rounded-[28px] border border-white/10 bg-white/8 p-5 backdrop-blur">
                        <p class="text-sm font-semibold text-white">{{ auth()->user()->name }}</p>
                        <p class="mt-1 text-xs uppercase tracking-[0.18em] text-red-200/70">{{ auth()->user()->email }}
                        </p>
                        <div class="mt-4 flex flex-wrap gap-2">
                            <a href="{{ route('password.change') }}"
                                class="rounded-xl border border-white/15 px-3 py-2 text-xs font-semibold text-red-100/85 transition hover:bg-white/10">Change
                                Password</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="rounded-xl bg-white px-3 py-2 text-xs font-semibold text-red-950 transition hover:bg-red-50">Logout</button>
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
