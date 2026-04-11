@extends('layouts.app')

@section('title', 'Group Details')

@section('content')
    <div class="ams-shell">
        <section
            class="relative overflow-hidden rounded-4xl border border-white/70 bg-[linear-gradient(135deg,rgba(47,6,6,0.96),rgba(126,23,29,0.84))] px-6 py-7 text-white shadow-[0_28px_80px_-34px_rgba(47,6,6,0.68)] sm:px-8">
            <div class="absolute right-0 top-0 h-44 w-44 rounded-full bg-white/10 blur-3xl"></div>
            <div class="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-3xl space-y-3">
                    <span
                        class="inline-flex items-center rounded-full border border-white/20 bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.24em] text-red-50/90">Group
                        Profile</span>
                    <div>
                        <h1 class="text-3xl font-semibold tracking-tight sm:text-4xl">{{ $group->name }}</h1>
                        <p class="mt-2 text-sm text-red-50/80 sm:text-base">{{ $group->programme->code ?? 'N/A' }} • Intake
                            {{ $group->intake_year }} • Semester {{ $group->semester }}</p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    @can('update', $group)
                        <a href="{{ route('groups.edit', $group) }}"
                            class="inline-flex items-center justify-center rounded-2xl bg-white px-4 py-3 text-sm font-semibold text-red-900 transition hover:bg-red-50">Edit
                            Group</a>
                    @endcan
                    @can('delete', $group)
                        <form action="{{ route('groups.destroy', $group) }}" method="POST"
                            onsubmit="return confirm('Are you sure you want to delete this group?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="ams-button-danger">Delete</button>
                        </form>
                    @endcan
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <article class="ams-card p-5">
                <p class="ams-stat-label">Members</p>
                <p class="ams-stat-value mt-3">{{ $group->users()->count() }}</p>
            </article>
            <article class="ams-card p-5">
                <p class="ams-stat-label">Assigned Courses</p>
                <p class="ams-stat-value mt-3">{{ $group->courses()->count() }}</p>
            </article>
            <article class="ams-card p-5">
                <p class="ams-stat-label">Coordinator Roles</p>
                <p class="ams-stat-value mt-3">{{ $group->users()->where('role', 'coordinator')->count() }}</p>
            </article>
            <article class="ams-card p-5">
                <p class="ams-stat-label">Status</p>
                <span
                    class="ams-badge mt-3 {{ $group->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700' }}">{{ $group->is_active ? 'Active' : 'Inactive' }}</span>
            </article>
        </section>

        <section class="ams-card overflow-hidden">
            <div class="border-b border-red-100 px-4 py-4 sm:px-6">
                <div class="flex flex-wrap gap-2" data-tabs>
                    <button type="button" class="ams-tab ams-tab-active" data-tab-target="group-tab-info">Basic
                        Info</button>
                    <button type="button" class="ams-tab" data-tab-target="group-tab-courses">Courses</button>
                    <button type="button" class="ams-tab" data-tab-target="group-tab-users">Members</button>
                </div>
            </div>

            <div id="group-tab-info" data-tab-panel>
                @include('group::partials.tabs.info', ['group' => $group])
            </div>

            <div id="group-tab-courses" data-tab-panel class="hidden">
                @can('update', $group)
                    @include('group::partials.tabs.courses', [
                        'group' => $group,
                        'availableCourses' => $availableCourses,
                        'assignedCourses' => $assignedCourses,
                    ])
                @else
                    <div class="p-6">
                        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                            You don't have permission to manage courses for this group.
                        </div>
                    </div>
                @endcan
            </div>

            <div id="group-tab-users" data-tab-panel class="hidden">
                @can('update', $group)
                    @include('group::partials.tabs.users', [
                        'group' => $group,
                        'availableUsers' => $availableUsers,
                        'assignedUsers' => $assignedUsers,
                    ])
                @else
                    <div class="p-6">
                        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                            You don't have permission to manage members for this group.
                        </div>
                    </div>
                @endcan
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('[data-tabs]').forEach((tabSet) => {
                const buttons = tabSet.querySelectorAll('[data-tab-target]');
                const panels = document.querySelectorAll('[data-tab-panel]');

                buttons.forEach((button) => {
                    button.addEventListener('click', () => {
                        const target = button.dataset.tabTarget;

                        buttons.forEach((candidate) => candidate.classList.remove(
                            'ams-tab-active'));
                        panels.forEach((panel) => panel.classList.toggle('hidden', panel
                            .id !== target));

                        button.classList.add('ams-tab-active');
                    });
                });
            });
        });
    </script>
@endpush
