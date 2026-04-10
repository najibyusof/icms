@extends('layouts.app')

@section('content')
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
@endsection
