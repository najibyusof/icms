@extends('layouts.app')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-red-950">Course Management</h1>
            <p class="mt-1 text-sm text-red-700">Manage course profiles, CLO, SLT, assessments, and submission status.</p>
        </div>
        @can('create', Modules\Course\Models\Course::class)
            <a href="{{ route('courses.create') }}"
                class="rounded-xl bg-red-900 px-4 py-2 text-sm font-semibold text-white hover:bg-red-800 transition">
                New Course
            </a>
        @endcan
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-xl border border-green-300 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-hidden rounded-2xl border border-red-200/70 bg-white/80 shadow-sm backdrop-blur">
        <table class="min-w-full divide-y divide-red-100">
            <thead class="bg-red-50/70">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-red-700">Code</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-red-700">Course</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-red-700">Programme
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-red-700">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-red-700">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-red-50">
                @forelse ($courses as $course)
                    <tr class="hover:bg-red-50/40 transition">
                        <td class="px-4 py-3 text-sm font-semibold text-red-900">{{ $course->code }}</td>
                        <td class="px-4 py-3 text-sm text-red-900">{{ $course->name }}</td>
                        <td class="px-4 py-3 text-sm text-red-700">{{ $course->programme?->name ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <span
                                class="inline-flex rounded-lg bg-red-100 px-2.5 py-0.5 text-xs font-semibold text-red-800">
                                {{ str($course->status ?? 'draft')->headline() }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('courses.edit', $course) }}"
                                class="rounded-lg border border-red-300 px-3 py-1.5 text-xs font-semibold text-red-800 hover:bg-red-50 transition">
                                Open
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-sm text-red-400">No courses found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="border-t border-red-100 px-4 py-3">
            {{ $courses->links() }}
        </div>
    </div>
@endsection
