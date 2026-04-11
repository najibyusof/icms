@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-4xl space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-red-950">Create JSU</h1>
                <p class="text-sm text-red-900/70">Define examination matrix and difficulty targets.</p>
            </div>
            <a href="{{ route('jsu.manage.index') }}"
                class="rounded-lg border border-red-300 px-4 py-2 text-sm font-semibold text-red-900 hover:bg-red-50">Back</a>
        </div>

        @if ($errors->any())
            <div class="rounded-xl border border-rose-300 bg-rose-50 px-4 py-3 text-rose-700">
                <ul class="list-disc space-y-1 pl-5 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('jsu.manage.store') }}"
            class="space-y-5 rounded-2xl border border-red-200 bg-white/80 p-6">
            @csrf

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-semibold text-red-900">Course</label>
                    <select name="course_id" class="w-full rounded-lg border border-red-200 px-3 py-2 text-sm" required>
                        <option value="">Select course</option>
                        @foreach ($courses as $course)
                            <option value="{{ $course->id }}" @selected(old('course_id') == $course->id)>
                                {{ $course->code }} - {{ $course->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-semibold text-red-900">Academic Session</label>
                    <input type="text" name="academic_session" value="{{ old('academic_session') }}"
                        placeholder="2025/2026-1" class="w-full rounded-lg border border-red-200 px-3 py-2 text-sm"
                        required>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-semibold text-red-900">Exam Type</label>
                    <select name="exam_type" class="w-full rounded-lg border border-red-200 px-3 py-2 text-sm" required>
                        @foreach ($examTypes as $examType)
                            <option value="{{ $examType }}" @selected(old('exam_type') === $examType)>{{ ucfirst($examType) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-semibold text-red-900">Total Marks</label>
                    <input type="number" name="total_marks" value="{{ old('total_marks', 100) }}" min="1"
                        class="w-full rounded-lg border border-red-200 px-3 py-2 text-sm" required>
                </div>

                <div class="md:col-span-2">
                    <label class="mb-1 block text-sm font-semibold text-red-900">Title</label>
                    <input type="text" name="title" value="{{ old('title') }}"
                        class="w-full rounded-lg border border-red-200 px-3 py-2 text-sm" required>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-semibold text-red-900">Duration (minutes)</label>
                    <input type="number" name="duration_minutes" value="{{ old('duration_minutes') }}" min="1"
                        class="w-full rounded-lg border border-red-200 px-3 py-2 text-sm">
                </div>

                <div class="md:col-span-2">
                    <label class="mb-1 block text-sm font-semibold text-red-900">Notes</label>
                    <textarea name="notes" rows="3" class="w-full rounded-lg border border-red-200 px-3 py-2 text-sm">{{ old('notes') }}</textarea>
                </div>
            </div>

            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
                <h2 class="mb-2 text-sm font-bold text-amber-900">Default Difficulty Distribution</h2>
                <div class="grid gap-3 text-xs text-amber-900 md:grid-cols-3">
                    @foreach ($difficultyConfig as $key => $cfg)
                        <div class="rounded-lg border border-amber-200 bg-white px-3 py-2">
                            <div class="font-semibold uppercase">{{ $key }}</div>
                            <div>Levels: {{ implode(', ', $cfg['bloom_levels'] ?? []) }}</div>
                            <div>Target: {{ $cfg['target_pct'] ?? 0 }}%</div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex justify-end gap-2">
                <a href="{{ route('jsu.manage.index') }}"
                    class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</a>
                <button type="submit"
                    class="rounded-lg bg-red-900 px-4 py-2 text-sm font-semibold text-white hover:bg-red-800">Create
                    JSU</button>
            </div>
        </form>
    </div>
@endsection
