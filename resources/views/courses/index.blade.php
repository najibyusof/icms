@extends('layouts.app')

@section('title', 'Course Management')

@section('content')
    @php
        $visibleCourses = $courses->getCollection();
    @endphp

    <div class="ams-shell">
        <section
            class="relative overflow-hidden rounded-[32px] border border-white/70 bg-[linear-gradient(135deg,rgba(95,15,19,0.96),rgba(191,48,57,0.84))] px-6 py-7 text-white shadow-[0_28px_80px_-34px_rgba(47,6,6,0.7)] sm:px-8">
            <div class="absolute right-0 top-0 h-44 w-44 rounded-full bg-white/10 blur-3xl"></div>
            <div class="relative flex flex-wrap items-end justify-between gap-4">
                <div class="max-w-3xl">
                    <span
                        class="inline-flex items-center rounded-full border border-white/20 bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.24em] text-red-50/90">Curriculum
                        Studio</span>
                    <h1 class="mt-4 text-3xl font-semibold tracking-tight sm:text-4xl">Course management workspace</h1>
                    <p class="mt-2 text-sm text-red-50/80 sm:text-base">Review delivery status, programme alignment, and
                        authoring progress across the course portfolio.</p>
                </div>
                @can('create', Modules\Course\Models\Course::class)
                    <a href="{{ route('courses.create') }}"
                        class="inline-flex items-center justify-center rounded-2xl bg-white px-4 py-3 text-sm font-semibold text-red-900 transition hover:bg-red-50">
                        New course
                    </a>
                @endcan
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <article class="ams-card p-5">
                <p class="ams-stat-label">Portfolio Size</p>
                <p class="ams-stat-value mt-3">{{ $courses->total() }}</p>
                <p class="mt-2 text-sm text-red-700">Total records matching the current filter set.</p>
            </article>
            <article class="ams-card p-5">
                <p class="ams-stat-label">Visible On Page</p>
                <p class="ams-stat-value mt-3">{{ $visibleCourses->count() }}</p>
                <p class="mt-2 text-sm text-red-700">Current page slice from the filtered results.</p>
            </article>
            <article class="ams-card p-5">
                <p class="ams-stat-label">Active</p>
                <p class="ams-stat-value mt-3">{{ $visibleCourses->where('is_active', true)->count() }}</p>
                <p class="mt-2 text-sm text-red-700">Courses currently enabled for delivery.</p>
            </article>
            <article class="ams-card p-5">
                <p class="ams-stat-label">In Review</p>
                <p class="ams-stat-value mt-3">{{ $visibleCourses->where('status', 'in_review')->count() }}</p>
                <p class="mt-2 text-sm text-red-700">Items waiting on workflow action.</p>
            </article>
        </section>

        <section class="ams-toolbar">
            <form method="GET" action="{{ route('courses.index') }}"
                class="grid flex-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                <label class="space-y-2 text-sm font-medium text-red-900">
                    <span>Search</span>
                    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="ams-field"
                        placeholder="Search by code, course, or programme">
                </label>
                <label class="space-y-2 text-sm font-medium text-red-900">
                    <span>Status</span>
                    <select name="status" class="ams-select">
                        <option value="">All statuses</option>
                        @foreach (['draft', 'submitted', 'in_review', 'approved', 'rejected'] as $status)
                            <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ str($status)->headline() }}
                            </option>
                        @endforeach
                    </select>
                </label>
                <label class="space-y-2 text-sm font-medium text-red-900">
                    <span>Programme</span>
                    <select name="programme_id" class="ams-select">
                        <option value="">All programmes</option>
                        @foreach ($programmes as $programme)
                            <option value="{{ $programme->id }}" @selected((string) ($filters['programme_id'] ?? '') === (string) $programme->id)>{{ $programme->name }}
                            </option>
                        @endforeach
                    </select>
                </label>
                <label class="space-y-2 text-sm font-medium text-red-900">
                    <span>Activity</span>
                    <select name="active" class="ams-select">
                        <option value="">All</option>
                        <option value="1" @selected(($filters['active'] ?? '') === '1')>Active</option>
                        <option value="0" @selected(($filters['active'] ?? '') === '0')>Inactive</option>
                    </select>
                </label>

                <div class="flex items-end gap-3 md:col-span-2 xl:col-span-4">
                    <button type="submit" class="ams-button-primary">Apply filters</button>
                    <a href="{{ route('courses.index') }}" class="ams-button-secondary">Reset</a>
                </div>
            </form>
        </section>

        @if (session('success'))
            <div class="rounded-2xl border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        <section class="ams-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="ams-table">
                    <thead>
                        <tr>
                            <th>Course</th>
                            <th>Programme</th>
                            <th>People</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($courses as $course)
                            @php
                                $statusClasses = match ($course->status) {
                                    'approved' => 'bg-emerald-100 text-emerald-700',
                                    'submitted' => 'bg-sky-100 text-sky-700',
                                    'in_review' => 'bg-amber-100 text-amber-700',
                                    'rejected' => 'bg-rose-100 text-rose-700',
                                    default => 'bg-red-100 text-red-700',
                                };
                            @endphp
                            <tr>
                                <td>
                                    <div>
                                        <div class="flex items-center gap-3">
                                            <span
                                                class="inline-flex rounded-xl bg-red-100 px-3 py-1 text-xs font-semibold text-red-800">{{ $course->code }}</span>
                                            <div>
                                                <p class="font-semibold text-red-950">{{ $course->name }}</p>
                                                <p class="text-xs text-red-600">{{ $course->credit_hours }} credit hours
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="space-y-1">
                                        <p class="font-medium text-red-900">{{ $course->programme?->name ?? '-' }}</p>
                                        <p class="text-xs text-red-600">
                                            {{ $course->programme?->code ?? 'No programme linked' }}</p>
                                    </div>
                                </td>
                                <td>
                                    <div class="space-y-1 text-xs text-red-700">
                                        <p><span class="font-semibold text-red-900">Lecturer:</span>
                                            {{ $course->lecturer?->name ?? 'Unassigned' }}</p>
                                        <p><span class="font-semibold text-red-900">Vetter:</span>
                                            {{ $course->vetter?->name ?? 'Unassigned' }}</p>
                                    </div>
                                </td>
                                <td>
                                    <div class="space-y-2">
                                        <span
                                            class="ams-badge {{ $statusClasses }}">{{ str($course->status ?? 'draft')->headline() }}</span>
                                        <div>
                                            <span
                                                class="ams-badge {{ $course->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700' }}">
                                                {{ $course->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('courses.edit', $course) }}"
                                        class="ams-button-secondary px-3 py-2 text-xs">
                                        Open
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-12 text-center text-sm text-red-400">No courses found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-red-100 px-4 py-3">
                {{ $courses->links() }}
            </div>
        </section>
    </div>
@endsection
