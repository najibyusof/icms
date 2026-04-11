@extends('layouts.app')

@section('title', 'Programme Management')

@section('content')
    <div class="ams-shell">
        <section
            class="relative overflow-hidden rounded-[32px] border border-white/70 bg-[linear-gradient(135deg,rgba(47,6,6,0.96),rgba(126,23,29,0.86))] px-6 py-7 text-white shadow-[0_28px_80px_-34px_rgba(47,6,6,0.68)] sm:px-8">
            <div class="absolute -right-10 top-0 h-40 w-40 rounded-full bg-white/10 blur-3xl"></div>
            <div class="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-3xl space-y-3">
                    <span
                        class="inline-flex items-center rounded-full border border-white/20 bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.24em] text-red-50/90">
                        Academic Portfolio
                    </span>
                    <div>
                        <h1 class="text-3xl font-semibold tracking-tight sm:text-4xl">Programme command centre</h1>
                        <p class="mt-2 max-w-2xl text-sm text-red-50/80 sm:text-base">
                            Review programme health, governance readiness, and workflow posture from a single premium table
                            workspace.
                        </p>
                    </div>
                </div>

                @can('programme.create')
                    <a href="{{ route('programmes.create') }}"
                        class="inline-flex items-center justify-center rounded-2xl bg-white px-4 py-3 text-sm font-semibold text-red-900 transition hover:bg-red-50">
                        Create programme
                    </a>
                @endcan
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <article class="ams-card p-5">
                <p class="ams-stat-label">Visible Programmes</p>
                <p class="ams-stat-value mt-3">{{ $programmes->count() }}</p>
                <p class="mt-2 text-sm text-red-700">Current results after filters.</p>
            </article>
            <article class="ams-card p-5">
                <p class="ams-stat-label">Active</p>
                <p class="ams-stat-value mt-3">{{ $programmes->where('is_active', true)->count() }}</p>
                <p class="mt-2 text-sm text-red-700">Programmes open for operational use.</p>
            </article>
            <article class="ams-card p-5">
                <p class="ams-stat-label">Approved</p>
                <p class="ams-stat-value mt-3">{{ $programmes->where('status', 'approved')->count() }}</p>
                <p class="mt-2 text-sm text-red-700">Curricula cleared by the workflow chain.</p>
            </article>
            <article class="ams-card p-5">
                <p class="ams-stat-label">In Review</p>
                <p class="ams-stat-value mt-3">{{ $programmes->where('status', 'in_review')->count() }}</p>
                <p class="mt-2 text-sm text-red-700">Items awaiting current-step decisions.</p>
            </article>
        </section>

        <section class="ams-toolbar">
            <form method="GET" action="{{ route('programmes.index') }}"
                class="grid flex-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                <label class="space-y-2 text-sm font-medium text-red-900">
                    <span>Search</span>
                    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="ams-field"
                        placeholder="Search by code, title, or accreditor">
                </label>
                <label class="space-y-2 text-sm font-medium text-red-900">
                    <span>Status</span>
                    <select name="status" class="ams-select">
                        <option value="">All statuses</option>
                        @foreach (\Modules\Programme\Models\Programme::STATUSES as $status => $label)
                            <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="space-y-2 text-sm font-medium text-red-900">
                    <span>Level</span>
                    <select name="level" class="ams-select">
                        <option value="">All levels</option>
                        @foreach (['Diploma', 'Bachelor', 'Master', 'PhD'] as $level)
                            <option value="{{ $level }}" @selected(($filters['level'] ?? '') === $level)>{{ $level }}</option>
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
                    <a href="{{ route('programmes.index') }}" class="ams-button-secondary">Reset</a>
                </div>
            </form>
        </section>

        <section class="ams-card overflow-hidden">
            @if ($programmes->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="ams-table">
                        <thead>
                            <tr>
                                <th>Programme</th>
                                <th>Level</th>
                                <th>Status</th>
                                <th>Coverage</th>
                                <th>Leadership</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($programmes as $programme)
                                @php
                                    $statusClasses = match ($programme->status) {
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
                                                    class="inline-flex rounded-xl bg-red-100 px-3 py-1 text-xs font-semibold text-red-800">{{ $programme->code }}</span>
                                                <div>
                                                    <p class="font-semibold text-red-950">{{ $programme->name }}</p>
                                                    <p class="text-xs text-red-600">
                                                        {{ $programme->accreditation_body ?: 'Accreditation body not recorded' }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="space-y-1">
                                            <p class="font-medium text-red-900">{{ $programme->level }}</p>
                                            <p class="text-xs text-red-600">{{ $programme->duration_semesters }} semesters
                                            </p>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="space-y-2">
                                            <span
                                                class="ams-badge {{ $statusClasses }}">{{ str($programme->status ?? 'draft')->headline() }}</span>
                                            <div>
                                                <span
                                                    class="ams-badge {{ $programme->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700' }}">
                                                    {{ $programme->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="grid gap-2 text-xs text-red-700 sm:grid-cols-2">
                                            <div class="rounded-2xl border border-red-100 bg-red-50/70 px-3 py-2">
                                                <span class="block text-[11px] uppercase tracking-[0.16em]">Courses</span>
                                                <span
                                                    class="mt-1 block text-sm font-semibold text-red-900">{{ $programme->courses_count ?? 0 }}</span>
                                            </div>
                                            <div class="rounded-2xl border border-red-100 bg-red-50/70 px-3 py-2">
                                                <span class="block text-[11px] uppercase tracking-[0.16em]">PLOs</span>
                                                <span
                                                    class="mt-1 block text-sm font-semibold text-red-900">{{ $programme->programme_p_l_os_count ?? 0 }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="space-y-1">
                                            <p class="font-medium text-red-900">
                                                {{ $programme->programmeChair?->name ?? 'Not assigned' }}</p>
                                            <p class="text-xs text-red-600">
                                                {{ $programme->programmeChair?->email ?? 'Ownership pending' }}</p>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('programmes.show', $programme) }}"
                                                class="ams-button-secondary px-3 py-2 text-xs">View</a>
                                            @can('update', $programme)
                                                <a href="{{ route('programmes.edit', $programme) }}"
                                                    class="ams-button-secondary px-3 py-2 text-xs">Edit</a>
                                            @endcan
                                            @can('delete', $programme)
                                                <form action="{{ route('programmes.destroy', $programme) }}" method="POST"
                                                    onsubmit="return confirm('Are you sure you want to delete this programme?');">
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
                                d="M3 7h18M7 3v4m10-4v4M6 11h12M6 15h7m-7 4h12" />
                        </svg>
                    </div>
                    <h2 class="mt-5 text-xl font-semibold text-red-950">No programmes match the current filter set</h2>
                    <p class="mt-2 text-sm text-red-700">Adjust the search criteria or reset the filters to broaden the
                        portfolio view.</p>
                </div>
            @endif
        </section>
    </div>
@endsection
