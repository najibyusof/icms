@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    @guest
        <section
            class="relative overflow-hidden rounded-[2rem] border border-red-200/70 bg-white/85 p-8 shadow-[0_32px_80px_-40px_rgba(127,29,29,0.45)] backdrop-blur sm:p-10 lg:p-12">
            <div class="pointer-events-none absolute -right-16 -top-20 h-56 w-56 rounded-full bg-red-200/60 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-20 -left-12 h-48 w-48 rounded-full bg-rose-300/55 blur-3xl"></div>

            <div class="relative grid gap-8 lg:grid-cols-5 lg:items-end">
                <div class="lg:col-span-3">
                    <p
                        class="inline-flex rounded-full border border-red-200 bg-red-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-red-700">
                        Academic Control Hub
                    </p>
                    <p class="mt-4 text-sm font-semibold uppercase tracking-[0.18em] text-red-700">Academic Management System</p>
                    <h1 class="mt-5 text-4xl font-extrabold tracking-tight text-red-950 sm:text-5xl">
                        Modern Academic Governance, Simplified.
                    </h1>
                    <p class="mt-4 max-w-2xl text-base leading-7 text-red-900/80 sm:text-lg">
                        Manage curriculum operations, programme workflows, group coordination, and assessment governance in
                        one premium workspace.
                    </p>

                    <div class="mt-8 flex flex-wrap gap-3">
                        <a href="{{ route('login') }}"
                            class="inline-flex items-center justify-center rounded-xl bg-red-900 px-6 py-3 text-sm font-semibold text-white transition hover:bg-red-800">
                            Login
                        </a>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}"
                                class="inline-flex items-center justify-center rounded-xl border border-red-200 bg-white px-6 py-3 text-sm font-semibold text-red-800 transition hover:border-red-300 hover:bg-red-50/60">
                                Forgot Password
                            </a>
                        @endif
                    </div>
                </div>

                <div class="relative lg:col-span-2 space-y-4">
                    <div class="rounded-2xl border border-red-100 bg-red-950 p-6 text-red-50 shadow-xl shadow-red-900/25">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-red-200">Platform Highlights</p>
                        <ul class="mt-4 space-y-3 text-sm text-red-100/95">
                            <li>Programme and course lifecycle visibility</li>
                            <li>Workflow-based review and approval controls</li>
                            <li>Group, JSU, and examination module integration</li>
                        </ul>
                        <p class="mt-5 text-xs text-red-200/80">
                            Access requires authenticated institutional credentials.
                        </p>
                    </div>

                    <div class="rounded-2xl border border-red-200 bg-white/95 p-5 shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-red-700">At a Glance</p>
                        <svg viewBox="0 0 320 200" class="mt-4 w-full" aria-label="Academic workflow infographic"
                            role="img">

                            {{-- ── SECTION 1: WORKFLOW PIPELINE ── --}}
                            <text x="4" y="13" font-size="8.5" font-weight="700" fill="#7f1d1d" letter-spacing="0.12em">ACADEMIC
                                WORKFLOW</text>

                            {{-- Connector lines: completed (solid dark), in-progress (medium), pending (dashed light) --}}
                            <line x1="62" y1="44" x2="114" y2="44" stroke="#7f1d1d"
                                stroke-width="2.5" stroke-linecap="round" />
                            <line x1="142" y1="44" x2="194" y2="44" stroke="#991b1b"
                                stroke-width="2.5" stroke-linecap="round" />
                            <line x1="224" y1="44" x2="274" y2="44" stroke="#fca5a5" stroke-width="2"
                                stroke-linecap="round" stroke-dasharray="4 3" />

                            {{-- Node 1 – completed --}}
                            <circle cx="48" cy="44" r="14" fill="#7f1d1d" />
                            <text x="48" y="49" font-size="12" font-weight="700" fill="white" text-anchor="middle">✓</text>

                            {{-- Node 2 – completed --}}
                            <circle cx="128" cy="44" r="14" fill="#991b1b" />
                            <text x="128" y="49" font-size="12" font-weight="700" fill="white" text-anchor="middle">✓</text>

                            {{-- Node 3 – active (outer ring + inner fill) --}}
                            <circle cx="208" cy="44" r="17" fill="none" stroke="#b91c1c" stroke-width="2.5" />
                            <circle cx="208" cy="44" r="11" fill="#b91c1c" />
                            <text x="208" y="49" font-size="10" font-weight="700" fill="white" text-anchor="middle">3</text>

                            {{-- Node 4 – pending --}}
                            <circle cx="288" cy="44" r="14" fill="#fef2f2" stroke="#fca5a5" stroke-width="2" />
                            <text x="288" y="49" font-size="10" font-weight="600" fill="#b91c1c" text-anchor="middle">4</text>

                            {{-- Node labels --}}
                            <text x="48" y="70" font-size="7.5" font-weight="600" fill="#7f1d1d"
                                text-anchor="middle">Course</text>
                            <text x="48" y="80" font-size="7.5" font-weight="600" fill="#7f1d1d"
                                text-anchor="middle">Authoring</text>
                            <text x="128" y="70" font-size="7.5" font-weight="600" fill="#991b1b"
                                text-anchor="middle">Review</text>
                            <text x="208" y="72" font-size="7.5" font-weight="600" fill="#b91c1c"
                                text-anchor="middle">Approval</text>
                            <text x="288" y="69" font-size="7.5" font-weight="600" fill="#9ca3af"
                                text-anchor="middle">Activation</text>
                            <text x="288" y="79" font-size="7" fill="#9ca3af" text-anchor="middle">(pending)</text>

                            {{-- Divider --}}
                            <rect x="4" y="95" width="312" height="0.75" fill="#fee2e2" />

                            {{-- ── SECTION 2: MODULE COVERAGE ── --}}
                            <text x="4" y="111" font-size="8.5" font-weight="700" fill="#7f1d1d"
                                letter-spacing="0.12em">MODULE COVERAGE</text>

                            {{-- Courses --}}
                            <text x="4" y="128" font-size="8" font-weight="600" fill="#7f1d1d">Courses</text>
                            <rect x="82" y="119" width="210" height="9" rx="4.5" fill="#fee2e2" />
                            <rect x="82" y="119" width="178" height="9" rx="4.5" fill="#7f1d1d" />
                            <text x="295" y="128" font-size="8" font-weight="600" fill="#7f1d1d"
                                text-anchor="end">85%</text>

                            {{-- Programmes --}}
                            <text x="4" y="144" font-size="8" font-weight="600" fill="#991b1b">Programmes</text>
                            <rect x="82" y="135" width="210" height="9" rx="4.5" fill="#fee2e2" />
                            <rect x="82" y="135" width="147" height="9" rx="4.5" fill="#991b1b" />
                            <text x="295" y="144" font-size="8" font-weight="600" fill="#991b1b"
                                text-anchor="end">70%</text>

                            {{-- Groups --}}
                            <text x="4" y="160" font-size="8" font-weight="600" fill="#b91c1c">Groups</text>
                            <rect x="82" y="151" width="210" height="9" rx="4.5" fill="#fee2e2" />
                            <rect x="82" y="151" width="126" height="9" rx="4.5" fill="#b91c1c" />
                            <text x="295" y="160" font-size="8" font-weight="600" fill="#b91c1c"
                                text-anchor="end">60%</text>

                            {{-- JSU --}}
                            <text x="4" y="176" font-size="8" font-weight="600" fill="#dc2626">JSU</text>
                            <rect x="82" y="167" width="210" height="9" rx="4.5" fill="#fee2e2" />
                            <rect x="82" y="167" width="94" height="9" rx="4.5" fill="#dc2626" />
                            <text x="295" y="176" font-size="8" font-weight="600" fill="#dc2626"
                                text-anchor="end">45%</text>

                            {{-- Examination --}}
                            <text x="4" y="192" font-size="8" font-weight="600" fill="#ef4444">Examination</text>
                            <rect x="82" y="183" width="210" height="9" rx="4.5" fill="#fee2e2" />
                            <rect x="82" y="183" width="73" height="9" rx="4.5" fill="#ef4444" />
                            <text x="295" y="192" font-size="8" font-weight="600" fill="#ef4444"
                                text-anchor="end">35%</text>
                        </svg>
                    </div>
                </div>
            </div>
        </section>
    @else
        @php
            $totals = $overview['totals'] ?? ['courses' => 0, 'programmes' => 0];
            $examStatus = $overview['exam_status'] ?? ['draft' => 0, 'approved' => 0];
            $workflowCounts = $overview['workflow_status_counts'] ?? [];
            $recentActivities = $overview['recent_activities'] ?? collect();
            $workflowStageSummary = $overview['workflow_stage_summary'] ?? collect();

            $examTotal = max(1, (int) $examStatus['draft'] + (int) $examStatus['approved']);
            $draftPct = (int) round(((int) $examStatus['draft'] / $examTotal) * 100);
            $approvedPct = 100 - $draftPct;

            $maxWorkflowCount = max(1, (int) collect($workflowCounts)->max());
        @endphp

        <section class="space-y-6">
            <div class="rounded-3xl border border-red-200/70 bg-white/80 p-6 shadow-xl shadow-red-100/40 backdrop-blur">
                <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                    <div>
                        <span class="sr-only">Academic Management System</span>
                        <p
                            class="inline-flex rounded-full bg-red-100 px-4 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-red-700">
                            Dashboard Module
                        </p>
                        <h1 class="mt-3 text-3xl font-extrabold tracking-tight text-red-950 md:text-4xl">Academic Management
                            System</h1>
                        <p class="mt-1 text-sm font-semibold uppercase tracking-[0.14em] text-red-600">Institution Snapshot</p>
                        <p class="mt-2 text-sm leading-6 text-red-900/75">Live academic metrics, workflow progress, and recent
                            activities.</p>
                    </div>
                    <div class="rounded-2xl border border-red-300/70 bg-red-950 px-5 py-4 text-right text-red-100">
                        <p class="text-xs uppercase tracking-[0.18em]">Data Window</p>
                        <p class="mt-1 text-lg font-bold">Cached for 5 minutes</p>
                    </div>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <article class="rounded-2xl border border-red-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-red-600">Total Courses</p>
                    <p class="mt-3 text-3xl font-extrabold text-red-950">{{ number_format((int) $totals['courses']) }}</p>
                </article>

                <article class="rounded-2xl border border-red-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-red-600">Total Programmes</p>
                    <p class="mt-3 text-3xl font-extrabold text-red-950">{{ number_format((int) $totals['programmes']) }}</p>
                </article>

                <article class="rounded-2xl border border-red-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-red-600">Draft Examinations</p>
                    <p class="mt-3 text-3xl font-extrabold text-amber-600">{{ number_format((int) $examStatus['draft']) }}</p>
                </article>

                <article class="rounded-2xl border border-red-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-red-600">Approved Examinations</p>
                    <p class="mt-3 text-3xl font-extrabold text-emerald-600">{{ number_format((int) $examStatus['approved']) }}
                    </p>
                </article>
            </div>

            @hasanyrole('Admin|admin|Lecturer|lecturer|Programme Coordinator|coordinator')
                <div class="grid gap-4 lg:grid-cols-3">
                    <a href="{{ route('courses.index') }}" class="ams-card p-5 transition hover:-translate-y-0.5">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="ams-stat-label">Quick Link</p>
                                <h2 class="mt-3 text-xl font-semibold text-red-950">Course Management</h2>
                                <p class="mt-2 text-sm text-red-700">Open course listing, filters, and authoring workflows.</p>
                            </div>
                            <div class="rounded-2xl bg-red-100 p-3 text-red-800">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                        d="M4 6.75A2.75 2.75 0 016.75 4h10.5A2.75 2.75 0 0120 6.75v10.5A2.75 2.75 0 0117.25 20H6.75A2.75 2.75 0 014 17.25V6.75z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                        d="M8 8h8M8 12h8M8 16h5" />
                                </svg>
                            </div>
                        </div>
                    </a>

                    <a href="{{ route('programmes.index') }}" class="ams-card p-5 transition hover:-translate-y-0.5">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="ams-stat-label">Quick Link</p>
                                <h2 class="mt-3 text-xl font-semibold text-red-950">Programme Management</h2>
                                <p class="mt-2 text-sm text-red-700">Review programme portfolios, governance, and study plans.</p>
                            </div>
                            <div class="rounded-2xl bg-red-100 p-3 text-red-800">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                        d="M4 5.75A1.75 1.75 0 015.75 4h12.5A1.75 1.75 0 0120 5.75v12.5A1.75 1.75 0 0118.25 20H5.75A1.75 1.75 0 014 18.25V5.75z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                        d="M8 8h8M8 12h8M8 16h4" />
                                </svg>
                            </div>
                        </div>
                    </a>

                    <a href="{{ route('groups.index') }}" class="ams-card p-5 transition hover:-translate-y-0.5">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="ams-stat-label">Quick Link</p>
                                <h2 class="mt-3 text-xl font-semibold text-red-950">Group Management</h2>
                                <p class="mt-2 text-sm text-red-700">Manage cohorts, coordinators, and assigned courses.</p>
                            </div>
                            <div class="rounded-2xl bg-red-100 p-3 text-red-800">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                        d="M16 11a4 4 0 10-8 0 4 4 0 008 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                        d="M3 20a7 7 0 0118 0" />
                                </svg>
                            </div>
                        </div>
                    </a>
                </div>
            @endhasanyrole

            <div class="grid gap-6 lg:grid-cols-5">
                <article class="rounded-2xl border border-red-200 bg-white p-5 shadow-sm lg:col-span-2">
                    <h2 class="text-sm font-bold uppercase tracking-wide text-red-700">Draft vs Approved</h2>

                    <div class="mt-5 flex items-center gap-5">
                        <div class="h-32 w-32 shrink-0 rounded-full"
                            style="background: conic-gradient(#b91c1c 0 {{ $draftPct }}%, #10b981 {{ $draftPct }}% 100%);">
                        </div>

                        <div class="space-y-2 text-sm">
                            <div class="flex items-center gap-2">
                                <span class="h-3 w-3 rounded-full bg-red-700"></span>
                                <span class="font-medium text-red-900">Draft: {{ $examStatus['draft'] }}
                                    ({{ $draftPct }}%)</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="h-3 w-3 rounded-full bg-emerald-500"></span>
                                <span class="font-medium text-red-900">Approved: {{ $examStatus['approved'] }}
                                    ({{ $approvedPct }}%)</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 h-2 w-full overflow-hidden rounded-full bg-red-100">
                        <div class="h-full bg-red-700" style="width: {{ $draftPct }}%"></div>
                    </div>
                    <div class="mt-1 h-2 w-full overflow-hidden rounded-full bg-red-100">
                        <div class="h-full bg-emerald-500" style="width: {{ $approvedPct }}%"></div>
                    </div>
                </article>

                <article class="rounded-2xl border border-red-200 bg-white p-5 shadow-sm lg:col-span-3">
                    <h2 class="text-sm font-bold uppercase tracking-wide text-red-700">Workflow Status Summary</h2>

                    <div class="mt-5 space-y-3">
                        @forelse ($workflowCounts as $status => $count)
                            @php
                                $barPct = (int) round(((int) $count / $maxWorkflowCount) * 100);
                            @endphp
                            <div>
                                <div class="mb-1 flex items-center justify-between text-xs font-semibold text-red-700">
                                    <span>{{ str($status)->headline() }}</span>
                                    <span>{{ $count }}</span>
                                </div>
                                <div class="h-2 w-full overflow-hidden rounded-full bg-red-100">
                                    <div class="h-full rounded-full bg-red-700" style="width: {{ $barPct }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-red-500">No workflow records available.</p>
                        @endforelse
                    </div>
                </article>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <article class="rounded-2xl border border-red-200 bg-white p-5 shadow-sm">
                    <h2 class="text-sm font-bold uppercase tracking-wide text-red-700">Recent Activities</h2>

                    <div class="mt-4 space-y-3">
                        @forelse ($recentActivities as $item)
                            <div class="rounded-xl border border-red-100 bg-red-50/40 px-4 py-3">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="text-sm font-semibold text-red-900">{{ $item['title'] }}</p>
                                    <span
                                        class="rounded-full bg-white px-2 py-0.5 text-xs font-semibold text-red-700">{{ $item['type'] }}</span>
                                </div>
                                <p class="mt-1 text-xs text-red-700">{{ $item['meta'] }}</p>
                                <p class="mt-1 text-xs text-red-500">{{ optional($item['at'])->diffForHumans() }}</p>
                            </div>
                        @empty
                            <p class="text-sm text-red-500">No recent activities found.</p>
                        @endforelse
                    </div>
                </article>

                <article class="rounded-2xl border border-red-200 bg-white p-5 shadow-sm">
                    <h2 class="text-sm font-bold uppercase tracking-wide text-red-700">Workflow Stage Matrix</h2>

                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full text-left text-sm">
                            <thead>
                                <tr class="border-b border-red-100 text-xs uppercase tracking-wide text-red-600">
                                    <th class="px-2 py-2">Role</th>
                                    <th class="px-2 py-2">Status</th>
                                    <th class="px-2 py-2 text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($workflowStageSummary as $row)
                                    <tr class="border-b border-red-50 text-red-900">
                                        <td class="px-2 py-2">{{ $row->role_name }}</td>
                                        <td class="px-2 py-2">{{ str($row->status)->headline() }}</td>
                                        <td class="px-2 py-2 text-right font-semibold">{{ $row->total }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-2 py-4 text-center text-red-500">No workflow stage data
                                            available.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </article>
            </div>
        </section>
    @endguest
@endsection
