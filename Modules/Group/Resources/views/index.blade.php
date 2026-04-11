@extends('layouts.app')

@section('title', 'Group Management')

@section('content')
    <div class="ams-shell">
        <section
            class="relative overflow-hidden rounded-4xl border border-white/70 bg-[linear-gradient(135deg,rgba(47,6,6,0.96),rgba(159,33,42,0.84))] px-6 py-7 text-white shadow-[0_28px_80px_-34px_rgba(47,6,6,0.7)] sm:px-8">
            <div class="absolute right-0 top-0 h-44 w-44 rounded-full bg-white/10 blur-3xl"></div>
            <div class="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-3xl space-y-3">
                    <span
                        class="inline-flex items-center rounded-full border border-white/20 bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.24em] text-red-50/90">Cohort
                        Operations</span>
                    <div>
                        <h1 class="text-3xl font-semibold tracking-tight sm:text-4xl">Group management workspace</h1>
                        <p class="mt-2 text-sm text-red-50/80 sm:text-base">Track academic cohorts, coordinators, members,
                            and course assignments in one place.</p>
                    </div>
                </div>

                @can('create', Modules\Group\Models\AcademicGroup::class)
                    <a href="{{ route('groups.create') }}"
                        class="inline-flex items-center justify-center rounded-2xl bg-white px-4 py-3 text-sm font-semibold text-red-900 transition hover:bg-red-50">Create
                        group</a>
                @endcan
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <article class="ams-card p-5">
                <p class="ams-stat-label">Total Groups</p>
                <p class="ams-stat-value mt-3">{{ $stats['total'] }}</p>
                <p class="mt-2 text-sm text-red-700">Academic cohorts currently listed.</p>
            </article>
            <article class="ams-card p-5">
                <p class="ams-stat-label">Active Groups</p>
                <p class="ams-stat-value mt-3">{{ $stats['active'] }}</p>
                <p class="mt-2 text-sm text-red-700">Groups marked as operational.</p>
            </article>
            <article class="ams-card p-5">
                <p class="ams-stat-label">Members</p>
                <p class="ams-stat-value mt-3">{{ $stats['members'] }}</p>
                <p class="mt-2 text-sm text-red-700">Total users assigned across all groups.</p>
            </article>
            <article class="ams-card p-5">
                <p class="ams-stat-label">Assigned Courses</p>
                <p class="ams-stat-value mt-3">{{ $stats['courses'] }}</p>
                <p class="mt-2 text-sm text-red-700">Course placements across cohorts.</p>
            </article>
        </section>

        <section class="ams-toolbar">
            <form method="GET" action="{{ route('groups.index') }}"
                class="grid flex-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                <label class="space-y-2 text-sm font-medium text-red-900">
                    <span>Search</span>
                    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="ams-field"
                        placeholder="Search group, programme, or coordinator">
                </label>
                <label class="space-y-2 text-sm font-medium text-red-900">
                    <span>Programme</span>
                    <select name="programme_id" class="ams-select">
                        <option value="">All programmes</option>
                        @foreach ($programmeOptions as $programme)
                            <option value="{{ $programme->id }}" @selected((string) ($filters['programme_id'] ?? '') === (string) $programme->id)>
                                {{ $programme->code }} - {{ $programme->name }}
                            </option>
                        @endforeach
                    </select>
                </label>
                <label class="space-y-2 text-sm font-medium text-red-900">
                    <span>Intake Year</span>
                    <input type="number" name="intake_year" min="2000" max="2100"
                        value="{{ $filters['intake_year'] ?? '' }}" class="ams-field" placeholder="e.g. 2026">
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
                    <a href="{{ route('groups.index') }}" class="ams-button-secondary">Reset</a>
                </div>
            </form>
        </section>

        <section class="ams-card overflow-hidden">
            @if ($groups->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="ams-table">
                        <thead>
                            <tr>
                                <th>Group</th>
                                <th>Programme</th>
                                <th>Cycle</th>
                                <th>Coordinator</th>
                                <th>Capacity</th>
                                <th>Status</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($groups as $group)
                                <tr>
                                    <td>
                                        <div>
                                            <p class="font-semibold text-red-950">{{ $group->name }}</p>
                                            <p class="text-xs text-red-600">Cohort {{ $group->intake_year }}</p>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="space-y-1">
                                            <span
                                                class="inline-flex rounded-xl bg-red-100 px-3 py-1 text-xs font-semibold text-red-800">{{ $group->programme->code ?? 'N/A' }}</span>
                                            <p class="text-xs text-red-600">
                                                {{ $group->programme->name ?? 'No programme linked' }}</p>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="space-y-1">
                                            <p class="font-medium text-red-900">Semester {{ $group->semester }}</p>
                                            <p class="text-xs text-red-600">Intake {{ $group->intake_year }}</p>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="space-y-1">
                                            <p class="font-medium text-red-900">
                                                {{ $group->coordinator?->name ?? 'Not assigned' }}</p>
                                            <p class="text-xs text-red-600">
                                                {{ $group->coordinator?->email ?? 'Coordinator pending' }}</p>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="grid gap-2 text-xs text-red-700 sm:grid-cols-2">
                                            <div class="rounded-2xl border border-red-100 bg-red-50/70 px-3 py-2">
                                                <span class="block text-[11px] uppercase tracking-[0.16em]">Members</span>
                                                <span
                                                    class="mt-1 block text-sm font-semibold text-red-900">{{ $group->users()->count() }}</span>
                                            </div>
                                            <div class="rounded-2xl border border-red-100 bg-red-50/70 px-3 py-2">
                                                <span class="block text-[11px] uppercase tracking-[0.16em]">Courses</span>
                                                <span
                                                    class="mt-1 block text-sm font-semibold text-red-900">{{ $group->courses()->count() }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span
                                            class="ams-badge {{ $group->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700' }}">
                                            {{ $group->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('groups.show', $group) }}"
                                                class="ams-button-secondary px-3 py-2 text-xs">View</a>
                                            @can('update', $group)
                                                <a href="{{ route('groups.edit', $group) }}"
                                                    class="ams-button-secondary px-3 py-2 text-xs">Edit</a>
                                            @endcan
                                            @can('delete', $group)
                                                <form action="{{ route('groups.destroy', $group) }}" method="POST"
                                                    onsubmit="return confirm('Are you sure you want to delete this group?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="ams-button-danger px-3 py-2 text-xs">Delete</button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="px-6 py-16 text-center">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-red-100 text-red-700">
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M17 20h5V4H2v16h5m10 0v-3.5A2.5 2.5 0 0014.5 14h-5A2.5 2.5 0 007 16.5V20m10 0H7m7-10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h2 class="mt-5 text-xl font-semibold text-red-950">No groups available yet</h2>
                    <p class="mt-2 text-sm text-red-700">Create a group to start assigning programmes, coordinators, and
                        course loads.</p>
                </div>
            @endif
        </section>
    </div>
@endsection
