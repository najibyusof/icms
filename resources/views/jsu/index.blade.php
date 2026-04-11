@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-red-950">JSU Management</h1>
                <p class="text-sm text-red-900/70">Build, review, approve, and activate examination blueprints.</p>
            </div>
            @can('create', \Modules\Jsu\Models\Jsu::class)
                <a href="{{ route('jsu.manage.create') }}"
                    class="rounded-lg bg-red-900 px-4 py-2 text-sm font-semibold text-white hover:bg-red-800">Create JSU</a>
            @endcan
        </div>

        @if (session('success'))
            <div class="rounded-xl border border-emerald-300 bg-emerald-50 px-4 py-3 text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        <form method="GET" action="{{ route('jsu.manage.index') }}"
            class="grid gap-3 rounded-2xl border border-red-200 bg-white/80 p-4 md:grid-cols-4">
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-red-900/70">Course</label>
                <select name="course_id" class="w-full rounded-lg border border-red-200 px-3 py-2 text-sm">
                    <option value="">All Courses</option>
                    @foreach ($courses as $course)
                        <option value="{{ $course->id }}" @selected((string) ($filters['course_id'] ?? '') === (string) $course->id)>
                            {{ $course->code }} - {{ $course->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-red-900/70">Status</label>
                <select name="status" class="w-full rounded-lg border border-red-200 px-3 py-2 text-sm">
                    <option value="">All Statuses</option>
                    @foreach (['draft', 'submitted', 'approved', 'rejected', 'active'] as $status)
                        <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-red-900/70">Exam Type</label>
                <select name="exam_type" class="w-full rounded-lg border border-red-200 px-3 py-2 text-sm">
                    <option value="">All Types</option>
                    @foreach ($examTypes as $examType)
                        <option value="{{ $examType }}" @selected(($filters['exam_type'] ?? '') === $examType)>{{ ucfirst($examType) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit"
                    class="rounded-lg border border-red-300 px-4 py-2 text-sm font-semibold text-red-900 hover:bg-red-50">Filter</button>
                <a href="{{ route('jsu.manage.index') }}"
                    class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Reset</a>
            </div>
        </form>

        <div class="overflow-hidden rounded-2xl border border-red-200 bg-white/80">
            <table class="min-w-full divide-y divide-red-100 text-sm">
                <thead class="bg-red-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-red-900">Title</th>
                        <th class="px-4 py-3 text-left font-semibold text-red-900">Course</th>
                        <th class="px-4 py-3 text-left font-semibold text-red-900">Session</th>
                        <th class="px-4 py-3 text-left font-semibold text-red-900">Type</th>
                        <th class="px-4 py-3 text-left font-semibold text-red-900">Status</th>
                        <th class="px-4 py-3 text-left font-semibold text-red-900">Questions</th>
                        <th class="px-4 py-3 text-left font-semibold text-red-900">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-red-100">
                    @forelse ($jsuList as $jsu)
                        <tr>
                            <td class="px-4 py-3">
                                <div class="font-semibold text-red-950">{{ $jsu->title }}</div>
                                <div class="text-xs text-red-900/70">Created by {{ $jsu->creator?->name ?? 'N/A' }}</div>
                            </td>
                            <td class="px-4 py-3">{{ $jsu->course?->code }} - {{ $jsu->course?->name }}</td>
                            <td class="px-4 py-3">{{ $jsu->academic_session }}</td>
                            <td class="px-4 py-3">{{ ucfirst($jsu->exam_type) }}</td>
                            <td class="px-4 py-3">
                                <span
                                    class="rounded-full px-2.5 py-1 text-xs font-semibold
                                    @if ($jsu->status === 'active') bg-emerald-100 text-emerald-700
                                    @elseif($jsu->status === 'approved') bg-sky-100 text-sky-700
                                    @elseif($jsu->status === 'submitted') bg-amber-100 text-amber-800
                                    @elseif($jsu->status === 'rejected') bg-rose-100 text-rose-700
                                    @else bg-slate-100 text-slate-700 @endif">
                                    {{ ucfirst($jsu->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">{{ $jsu->total_questions }}</td>
                            <td class="px-4 py-3">
                                <a href="{{ route('jsu.manage.show', $jsu) }}"
                                    class="rounded-lg border border-red-300 px-3 py-1.5 text-xs font-semibold text-red-900 hover:bg-red-50">Open</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-red-900/70">No JSU records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>
            {{ $jsuList->links() }}
        </div>
    </div>
@endsection
