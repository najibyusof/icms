@extends('layouts.app')

@section('content')
    @php
        $isEdit = $mode === 'edit';
        $workflowTimeline = $workflowTimeline ?? ['summary' => null, 'events' => []];
        $workflowSummary = $workflowTimeline['summary'] ?? null;
        $workflowEvents = $workflowTimeline['events'] ?? [];
        $workflowAction = $workflowTimeline['action'] ?? null;

        $clos = old(
            'clos',
            $isEdit
                ? $course->clos->map(fn($x) => ['statement' => $x->statement, 'bloom_level' => $x->bloom_level])->all()
                : [['statement' => '', 'bloom_level' => 'C1']],
        );
        $requisites = old(
            'requisites',
            $isEdit
                ? $course->requisites
                    ->map(
                        fn($x) => [
                            'type' => $x->type,
                            'course_code' => $x->course_code,
                            'course_name' => $x->course_name,
                        ],
                    )
                    ->all()
                : [['type' => 'prerequisite', 'course_code' => '', 'course_name' => '']],
        );
        $assessments = old(
            'assessments',
            $isEdit
                ? $course->assessments
                    ->map(
                        fn($x) => [
                            'component' => $x->component,
                            'weightage' => $x->weightage,
                            'remarks' => $x->remarks,
                        ],
                    )
                    ->all()
                : [['component' => '', 'weightage' => '', 'remarks' => '']],
        );
        $topics = old(
            'topics',
            $isEdit
                ? $course->topics
                    ->map(
                        fn($x) => [
                            'week_no' => $x->week_no,
                            'title' => $x->title,
                            'learning_activity' => $x->learning_activity,
                        ],
                    )
                    ->all()
                : [['week_no' => 1, 'title' => '', 'learning_activity' => '']],
        );
        $slt = old(
            'slt',
            $isEdit
                ? $course->sltItems
                    ->map(
                        fn($x) => [
                            'activity' => $x->activity,
                            'f2f_hours' => $x->f2f_hours,
                            'non_f2f_hours' => $x->non_f2f_hours,
                            'independent_hours' => $x->independent_hours,
                        ],
                    )
                    ->all()
                : [['activity' => '', 'f2f_hours' => 0, 'non_f2f_hours' => 0, 'independent_hours' => 0]],
        );
    @endphp

    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-red-950">{{ $isEdit ? 'Edit Course' : 'Create Course' }}</h1>
            <p class="mt-1 text-sm text-red-700">Complete all tabs before submission for approval.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('courses.index') }}"
                class="rounded-lg border border-red-300 px-4 py-2 text-sm font-semibold text-red-800 hover:bg-red-50">Back</a>
            @if ($isEdit)
                <a href="{{ route('courses.slt.export', $course) }}"
                    class="rounded-lg border border-red-300 px-4 py-2 text-sm font-semibold text-red-800 hover:bg-red-50">Export
                    SLT (Excel)</a>
                @if (($workflowSummary['workflow_id'] ?? null) !== null)
                    <a href="{{ route('workflows.timeline', $workflowSummary['workflow_id']) }}"
                        class="rounded-lg border border-red-300 px-4 py-2 text-sm font-semibold text-red-800 hover:bg-red-50">View Workflow</a>
                @endif
                @can('submit', $course)
                    <form method="POST" action="{{ route('courses.submit', $course) }}">
                        @csrf
                        <button type="submit"
                            class="rounded-lg bg-red-900 px-4 py-2 text-sm font-semibold text-white hover:bg-red-800">Submit for
                            Approval</button>
                    </form>
                @endcan
            @endif
        </div>
    </div>

    @if ($isEdit)
        <section class="mb-5 rounded-2xl border border-red-200/70 bg-white/80 p-5 shadow-sm">
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold uppercase tracking-wide text-red-700">Course Approval Timeline</h2>
                    <p class="mt-1 text-xs text-red-600">Live workflow stages tied to reviewer and approver records.</p>
                </div>
                <span class="rounded-lg bg-red-100 px-2.5 py-1 text-xs font-semibold text-red-800">
                    {{ str($course->status ?? 'draft')->headline() }}
                </span>
            </div>

            @if ($workflowSummary)
                <div class="mb-4 grid gap-3 sm:grid-cols-3">
                    <article class="rounded-xl border border-red-100 bg-red-50/50 px-4 py-3">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-red-600">Workflow Status</p>
                        <p class="mt-1 text-lg font-bold text-red-950">{{ str($workflowSummary['status'])->headline() }}</p>
                    </article>
                    <article class="rounded-xl border border-red-100 bg-red-50/50 px-4 py-3">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-red-600">Current Stage</p>
                        <p class="mt-1 text-lg font-bold text-red-950">{{ $workflowSummary['current_stage'] ?? '-' }}</p>
                    </article>
                    <article class="rounded-xl border border-red-100 bg-red-50/50 px-4 py-3">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-red-600">Completed Stages</p>
                        <p class="mt-1 text-lg font-bold text-red-950">
                            {{ $workflowSummary['completed_count'] }}/{{ $workflowSummary['approval_count'] }}</p>
                    </article>
                </div>
                @if (($workflowSummary['workflow_id'] ?? null) !== null)
                    <div class="mb-4">
                        <a href="{{ route('workflows.timeline', $workflowSummary['workflow_id']) }}"
                            class="inline-flex items-center rounded-lg border border-red-300 px-4 py-2 text-sm font-semibold text-red-800 hover:bg-red-50">
                            Open Full Workflow Timeline
                        </a>
                    </div>
                @endif
            @endif

            @if ($workflowAction && ($workflowAction['is_actionable'] ?? false))
                <div class="mb-4 rounded-xl border border-amber-300 bg-amber-50 px-4 py-4 shadow-sm">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-amber-900">Action Required</p>
                            <p class="mt-1 text-xs text-amber-700">
                                You are assigned to stage {{ $workflowAction['pending_stage'] ?? '-' }}
                                as {{ str($workflowAction['pending_role'] ?? 'reviewer')->headline() }}.
                            </p>
                        </div>
                        <span class="rounded-full bg-white px-2.5 py-1 text-xs font-semibold text-amber-700">
                            Pending Your Decision
                        </span>
                    </div>

                    <form method="POST" action="{{ route('courses.workflow.decide', $course) }}" class="mt-4 space-y-3">
                        @csrf
                        <input type="hidden" name="workflow_id" value="{{ $workflowAction['workflow_id'] }}">

                        <div class="flex flex-wrap items-center gap-2">
                            <button type="submit" name="decision" value="approved"
                                class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">
                                Approve Stage
                            </button>
                            <button type="button" data-open-reject-modal
                                class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-500">
                                Reject Stage
                            </button>
                        </div>
                    </form>
                </div>
            @elseif ($workflowAction && !empty($workflowAction['pending_stage']))
                <div class="mb-4 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                    Pending stage {{ $workflowAction['pending_stage'] }} is currently assigned to
                    {{ $workflowAction['pending_reviewer'] ?? 'another reviewer' }}.
                </div>
            @endif

            <div class="space-y-3">
                @forelse ($workflowEvents as $event)
                    @php
                        $status = $event['status'] ?? 'pending';
                        $accent = match ($status) {
                            'approved' => 'emerald',
                            'rejected' => 'rose',
                            'pending' => 'amber',
                            'queued' => 'slate',
                            default => 'red',
                        };
                    @endphp
                    <div class="rounded-xl border border-red-100 bg-red-50/40 px-4 py-3">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold text-red-900">{{ $event['label'] }}</p>
                                @if (!empty($event['role']))
                                    <p class="mt-0.5 text-xs font-medium uppercase tracking-wide text-red-500">
                                        {{ $event['role'] }}</p>
                                @endif
                            </div>
                            <span
                                class="rounded-full bg-{{ $accent }}-100 px-2 py-0.5 text-xs font-semibold text-{{ $accent }}-700">
                                {{ str($status)->headline() }}
                            </span>
                        </div>
                        <p class="mt-1 text-xs text-red-700">By: {{ $event['by'] ?? 'System' }}</p>
                        @if (!empty($event['notes']))
                            <p class="mt-1 text-xs text-red-600">{{ $event['notes'] }}</p>
                        @endif
                        <p class="mt-1 text-xs text-red-500">{{ optional($event['at'] ?? null)->diffForHumans() }}</p>
                    </div>
                @empty
                    <p class="text-sm text-red-500">No approval timeline yet. Submit this course to create workflow approval
                        stages.
                    </p>
                @endforelse
            </div>
        </section>

        @if ($workflowAction && ($workflowAction['is_actionable'] ?? false))
            <div id="rejectDecisionModal"
                class="hidden fixed inset-0 z-50 items-center justify-center bg-red-950/40 px-4 backdrop-blur-sm">
                <div class="w-full max-w-md rounded-2xl border border-rose-200 bg-white shadow-2xl">
                    <div class="flex items-center justify-between border-b border-rose-100 px-5 py-4">
                        <div>
                            <h3 class="text-base font-bold text-red-950">Confirm Rejection</h3>
                            <p class="mt-1 text-xs text-red-600">Comments are required before rejecting this stage.</p>
                        </div>
                        <button type="button" data-close-reject-modal
                            class="rounded-lg p-2 text-red-400 hover:bg-red-50 hover:text-red-700">X</button>
                    </div>

                    <form method="POST" action="{{ route('courses.workflow.decide', $course) }}"
                        class="space-y-4 px-5 py-5">
                        @csrf
                        <input type="hidden" name="workflow_id" value="{{ $workflowAction['workflow_id'] }}">
                        <input type="hidden" name="decision" value="rejected">

                        <div>
                            <label for="reject_workflow_comments"
                                class="mb-1 block text-xs font-semibold uppercase tracking-wide text-rose-800">
                                Rejection Comments
                            </label>
                            <textarea id="reject_workflow_comments" name="comments" rows="4" required
                                class="w-full rounded-lg border border-rose-200 bg-white px-3 py-2 text-sm text-red-900 focus:border-rose-400 focus:outline-none focus:ring-1 focus:ring-rose-300"
                                placeholder="Explain why this stage is being rejected...">{{ old('comments') }}</textarea>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-2">
                            <button type="button" data-close-reject-modal
                                class="rounded-lg border border-red-300 px-4 py-2 text-sm font-semibold text-red-800 hover:bg-red-50">
                                Cancel
                            </button>
                            <button type="submit"
                                class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-500">
                                Confirm Rejection
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    @endif

    @if (session('success'))
        <div class="mb-4 rounded-xl border border-green-300 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="mb-4 rounded-xl border border-rose-300 bg-rose-50 px-4 py-3 text-sm text-rose-800">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ $isEdit ? route('courses.update', $course) : route('courses.store.web') }}"
        class="rounded-2xl border border-red-200/70 bg-white/80 p-5 shadow-sm backdrop-blur">
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        <div class="mb-4 flex flex-wrap gap-2" id="courseTabs">
            @foreach (['main' => 'Main Info', 'clo' => 'CLO', 'requisites' => 'Requisites', 'assessment' => 'Assessment', 'topics' => 'Topics', 'slt' => 'SLT'] as $key => $label)
                <button type="button" data-tab="{{ $key }}"
                    class="tab-btn rounded-lg border border-red-300 px-3 py-1.5 text-xs font-semibold text-red-800 hover:bg-red-50 {{ $loop->first ? 'bg-red-900 text-white border-red-900 hover:bg-red-900' : '' }}">
                    {{ $label }}
                    @if ($key !== 'main')
                        <span data-tab-count="{{ $key }}"
                            class="ml-2 inline-flex min-w-5 items-center justify-center rounded-full bg-white/85 px-1.5 py-0.5 text-[10px] font-bold text-red-800">
                            {{ match ($key) {
                                'clo' => count($clos),
                                'requisites' => count($requisites),
                                'assessment' => count($assessments),
                                'topics' => count($topics),
                                'slt' => count($slt),
                            } }}
                        </span>
                    @endif
                </button>
            @endforeach
        </div>

        <section class="tab-pane" data-tab-pane="main">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-semibold text-red-800">Programme</label>
                    <select name="programme_id" required
                        class="w-full rounded-lg border border-red-200 px-3 py-2 text-sm">
                        <option value="">Select programme</option>
                        @foreach ($programmes as $programme)
                            <option value="{{ $programme->id }}" @selected(old('programme_id', $course->programme_id) == $programme->id)>{{ $programme->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-red-800">Course Code</label>
                    <input name="code" value="{{ old('code', $course->code) }}" required
                        class="w-full rounded-lg border border-red-200 px-3 py-2 text-sm" />
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-red-800">Course Name</label>
                    <input name="name" value="{{ old('name', $course->name) }}" required
                        class="w-full rounded-lg border border-red-200 px-3 py-2 text-sm" />
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-red-800">Credit Hours</label>
                    <input type="number" min="1" max="20" name="credit_hours"
                        value="{{ old('credit_hours', $course->credit_hours) }}" required
                        class="w-full rounded-lg border border-red-200 px-3 py-2 text-sm" />
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-red-800">Lecturer</label>
                    <select name="lecturer_id" class="w-full rounded-lg border border-red-200 px-3 py-2 text-sm">
                        <option value="">Not assigned</option>
                        @foreach ($lecturers as $user)
                            <option value="{{ $user->id }}" @selected(old('lecturer_id', $course->lecturer_id) == $user->id)>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-red-800">Resource Person</label>
                    <select name="resource_person_id" class="w-full rounded-lg border border-red-200 px-3 py-2 text-sm">
                        <option value="">Not assigned</option>
                        @foreach ($resourcePeople as $user)
                            <option value="{{ $user->id }}" @selected(old('resource_person_id', $course->resource_person_id) == $user->id)>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-red-800">Vetter</label>
                    <select name="vetter_id" class="w-full rounded-lg border border-red-200 px-3 py-2 text-sm">
                        <option value="">Not assigned</option>
                        @foreach ($vetters as $user)
                            <option value="{{ $user->id }}" @selected(old('vetter_id', $course->vetter_id) == $user->id)>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center gap-2 pt-6">
                    <input type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', $course->is_active ?? true))>
                    <label for="is_active" class="text-sm text-red-800">Active</label>
                </div>
            </div>
        </section>

        <section class="tab-pane hidden" data-tab-pane="clo">
            <div class="mb-3 flex items-center justify-between gap-3">
                <p class="text-xs text-red-600">Drag rows by the grip handle to reorder CLO sequence.</p>
                <button type="button" data-add-target="closRows" data-template="closTemplate"
                    class="rounded-lg border border-red-300 px-3 py-1.5 text-xs font-semibold text-red-800 hover:bg-red-50">+
                    Add CLO</button>
            </div>
            <div id="closRows" data-next-index="{{ count($clos) }}" data-repeater-key="clo" data-sortable-container
                class="space-y-3">
                <p data-empty-state
                    class="{{ count($clos) > 0 ? 'hidden ' : '' }}rounded-xl border border-dashed border-red-200 bg-red-50/40 px-4 py-3 text-sm text-red-500">
                    No CLO rows yet. Add the first CLO for this course.
                </p>
                @foreach ($clos as $i => $row)
                    <div draggable="true" data-sortable-row
                        class="grid grid-cols-1 gap-3 sm:grid-cols-12 repeater-row sortable-row rounded-xl border border-transparent p-2 transition">
                        <button type="button" title="Drag to reorder"
                            class="drag-handle rounded-lg border border-red-200 bg-white px-2 py-2 text-red-500 hover:bg-red-50 sm:col-span-1">
                            <span aria-hidden="true">::</span>
                        </button>
                        <span data-clo-order
                            class="inline-flex items-center justify-center rounded-lg border border-red-200 bg-red-50 px-2 py-2 text-xs font-bold text-red-700 sm:col-span-1">
                            {{ $i + 1 }}
                        </span>
                        <input name="clos[{{ $i }}][statement]" value="{{ $row['statement'] ?? '' }}"
                            placeholder="CLO statement"
                            class="sm:col-span-7 rounded-lg border border-red-200 px-3 py-2 text-sm" />
                        <select name="clos[{{ $i }}][bloom_level]"
                            class="sm:col-span-2 rounded-lg border border-red-200 px-3 py-2 text-sm">
                            @foreach (['C1', 'C2', 'C3', 'C4', 'C5', 'C6'] as $level)
                                <option value="{{ $level }}" @selected(($row['bloom_level'] ?? 'C1') === $level)>{{ $level }}
                                </option>
                            @endforeach
                        </select>
                        <button type="button"
                            class="remove-row rounded-lg border border-rose-300 px-2 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-50 sm:col-span-1">Remove</button>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="tab-pane hidden" data-tab-pane="requisites">
            <div class="mb-3 flex justify-end">
                <button type="button" data-add-target="requisiteRows" data-template="requisiteTemplate"
                    class="rounded-lg border border-red-300 px-3 py-1.5 text-xs font-semibold text-red-800 hover:bg-red-50">+
                    Add Requisite</button>
            </div>
            <div id="requisiteRows" data-next-index="{{ count($requisites) }}" data-repeater-key="requisites"
                class="space-y-3">
                <p data-empty-state
                    class="{{ count($requisites) > 0 ? 'hidden ' : '' }}rounded-xl border border-dashed border-red-200 bg-red-50/40 px-4 py-3 text-sm text-red-500">
                    No requisite rows yet. Add prerequisite or corequisite courses.
                </p>
                @foreach ($requisites as $i => $row)
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-12 repeater-row">
                        <select name="requisites[{{ $i }}][type]"
                            class="sm:col-span-2 rounded-lg border border-red-200 px-3 py-2 text-sm">
                            <option value="prerequisite" @selected(($row['type'] ?? 'prerequisite') === 'prerequisite')>Prerequisite</option>
                            <option value="corequisite" @selected(($row['type'] ?? '') === 'corequisite')>Corequisite</option>
                        </select>
                        <input name="requisites[{{ $i }}][course_code]"
                            value="{{ $row['course_code'] ?? '' }}" placeholder="Code"
                            class="sm:col-span-3 rounded-lg border border-red-200 px-3 py-2 text-sm" />
                        <input name="requisites[{{ $i }}][course_name]"
                            value="{{ $row['course_name'] ?? '' }}" placeholder="Course name"
                            class="sm:col-span-6 rounded-lg border border-red-200 px-3 py-2 text-sm" />
                        <button type="button"
                            class="remove-row rounded-lg border border-rose-300 px-2 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-50">Remove</button>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="tab-pane hidden" data-tab-pane="assessment">
            <div class="mb-3 flex justify-end">
                <button type="button" data-add-target="assessmentRows" data-template="assessmentTemplate"
                    class="rounded-lg border border-red-300 px-3 py-1.5 text-xs font-semibold text-red-800 hover:bg-red-50">+
                    Add Assessment</button>
            </div>
            <div id="assessmentRows" data-next-index="{{ count($assessments) }}" data-repeater-key="assessment"
                class="space-y-3">
                <p data-empty-state
                    class="{{ count($assessments) > 0 ? 'hidden ' : '' }}rounded-xl border border-dashed border-red-200 bg-red-50/40 px-4 py-3 text-sm text-red-500">
                    No assessment rows yet. Add assessment components until the total reaches 100%.
                </p>
                @foreach ($assessments as $i => $row)
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-12 repeater-row assessment-row">
                        <input name="assessments[{{ $i }}][component]" value="{{ $row['component'] ?? '' }}"
                            placeholder="Component"
                            class="sm:col-span-4 rounded-lg border border-red-200 px-3 py-2 text-sm" />
                        <input type="number" step="0.01" name="assessments[{{ $i }}][weightage]"
                            value="{{ $row['weightage'] ?? '' }}" placeholder="%"
                            class="assessment-input sm:col-span-2 rounded-lg border border-red-200 px-3 py-2 text-sm" />
                        <input name="assessments[{{ $i }}][remarks]" value="{{ $row['remarks'] ?? '' }}"
                            placeholder="Remarks"
                            class="sm:col-span-5 rounded-lg border border-red-200 px-3 py-2 text-sm" />
                        <button type="button"
                            class="remove-row rounded-lg border border-rose-300 px-2 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-50">Remove</button>
                    </div>
                @endforeach
            </div>
            <div class="mt-3 flex items-center justify-between">
                <p class="text-xs text-red-700">Assessment total must equal 100%.</p>
                <p class="text-sm font-semibold text-red-900">Current Total: <span id="assessmentTotal">0.00</span>%</p>
            </div>
        </section>

        <section class="tab-pane hidden" data-tab-pane="topics">
            <div class="mb-3 flex items-center justify-between gap-3">
                <p class="text-xs text-red-600">Drag topic rows to change the teaching sequence visually.</p>
                <button type="button" data-add-target="topicRows" data-template="topicTemplate"
                    class="rounded-lg border border-red-300 px-3 py-1.5 text-xs font-semibold text-red-800 hover:bg-red-50">+
                    Add Topic</button>
            </div>
            <div id="topicRows" data-next-index="{{ count($topics) }}" data-repeater-key="topics"
                data-sortable-container class="space-y-3">
                <p data-empty-state
                    class="{{ count($topics) > 0 ? 'hidden ' : '' }}rounded-xl border border-dashed border-red-200 bg-red-50/40 px-4 py-3 text-sm text-red-500">
                    No topic rows yet. Add weekly learning topics.
                </p>
                @foreach ($topics as $i => $row)
                    <div draggable="true" data-sortable-row
                        class="grid grid-cols-1 gap-3 sm:grid-cols-12 repeater-row sortable-row rounded-xl border border-transparent p-2 transition">
                        <button type="button" title="Drag to reorder"
                            class="drag-handle rounded-lg border border-red-200 bg-white px-2 py-2 text-red-500 hover:bg-red-50 sm:col-span-1">
                            <span aria-hidden="true">::</span>
                        </button>
                        <input type="number" min="1" readonly name="topics[{{ $i }}][week_no]"
                            value="{{ $row['week_no'] ?? 1 }}"
                            class="topic-week-input sm:col-span-2 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm font-semibold text-red-800" />
                        <input name="topics[{{ $i }}][title]" value="{{ $row['title'] ?? '' }}"
                            placeholder="Topic"
                            class="sm:col-span-4 rounded-lg border border-red-200 px-3 py-2 text-sm" />
                        <input name="topics[{{ $i }}][learning_activity]"
                            value="{{ $row['learning_activity'] ?? '' }}" placeholder="Learning activity"
                            class="sm:col-span-4 rounded-lg border border-red-200 px-3 py-2 text-sm" />
                        <button type="button"
                            class="remove-row rounded-lg border border-rose-300 px-2 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-50 sm:col-span-1">Remove</button>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="tab-pane hidden" data-tab-pane="slt">
            <div class="mb-3 flex items-center justify-between gap-3">
                <p class="text-xs text-red-600">Drag SLT activities to reorganize the workload sequence.</p>
                <button type="button" data-add-target="sltRows" data-template="sltTemplate"
                    class="rounded-lg border border-red-300 px-3 py-1.5 text-xs font-semibold text-red-800 hover:bg-red-50">+
                    Add SLT Activity</button>
            </div>
            <div id="sltRows" data-next-index="{{ count($slt) }}" data-repeater-key="slt" data-sortable-container
                class="space-y-3">
                <p data-empty-state
                    class="{{ count($slt) > 0 ? 'hidden ' : '' }}rounded-xl border border-dashed border-red-200 bg-red-50/40 px-4 py-3 text-sm text-red-500">
                    No SLT activities yet. Add teaching and learning workload rows.
                </p>
                @foreach ($slt as $i => $row)
                    <div draggable="true" data-sortable-row
                        class="grid grid-cols-1 gap-3 sm:grid-cols-12 slt-row repeater-row sortable-row rounded-xl border border-transparent p-2 transition">
                        <button type="button" title="Drag to reorder"
                            class="drag-handle rounded-lg border border-red-200 bg-white px-2 py-2 text-red-500 hover:bg-red-50 sm:col-span-1">
                            <span aria-hidden="true">::</span>
                        </button>
                        <input name="slt[{{ $i }}][activity]" value="{{ $row['activity'] ?? '' }}"
                            placeholder="Activity"
                            class="sm:col-span-3 rounded-lg border border-red-200 px-3 py-2 text-sm" />
                        <input type="number" step="0.01" min="0" name="slt[{{ $i }}][f2f_hours]"
                            value="{{ $row['f2f_hours'] ?? 0 }}"
                            class="slt-input sm:col-span-2 rounded-lg border border-red-200 px-3 py-2 text-sm" />
                        <input type="number" step="0.01" min="0"
                            name="slt[{{ $i }}][non_f2f_hours]" value="{{ $row['non_f2f_hours'] ?? 0 }}"
                            class="slt-input sm:col-span-2 rounded-lg border border-red-200 px-3 py-2 text-sm" />
                        <input type="number" step="0.01" min="0"
                            name="slt[{{ $i }}][independent_hours]"
                            value="{{ $row['independent_hours'] ?? 0 }}"
                            class="slt-input sm:col-span-2 rounded-lg border border-red-200 px-3 py-2 text-sm" />
                        <input type="text" readonly value="0"
                            class="slt-total sm:col-span-1 rounded-lg border border-red-100 bg-red-50 px-3 py-2 text-sm font-semibold text-red-800" />
                        <button type="button"
                            class="remove-row rounded-lg border border-rose-300 px-2 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-50 sm:col-span-1">Remove</button>
                    </div>
                @endforeach
            </div>
            <div class="mt-3 text-sm font-semibold text-red-900">SLT Grand Total: <span id="sltGrandTotal">0.00</span>
                hours</div>
        </section>

        <div class="mt-6 flex items-center justify-end gap-3 border-t border-red-100 pt-4">
            @if ($isEdit)
                <button type="submit" formaction="{{ route('courses.destroy', $course) }}" formmethod="POST"
                    onclick="event.preventDefault(); if(confirm('Delete this course?')) { const f = this.form; const m=document.createElement('input'); m.type='hidden'; m.name='_method'; m.value='DELETE'; f.appendChild(m); f.submit(); }"
                    class="rounded-lg border border-rose-400 px-4 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-50">Delete</button>
            @endif
            <button type="submit"
                class="rounded-lg bg-red-900 px-5 py-2 text-sm font-semibold text-white hover:bg-red-800">Save
                Course</button>
        </div>
    </form>

    <template id="closTemplate">
        <div draggable="true" data-sortable-row
            class="grid grid-cols-1 gap-3 sm:grid-cols-12 repeater-row sortable-row rounded-xl border border-transparent p-2 transition">
            <button type="button" title="Drag to reorder"
                class="drag-handle rounded-lg border border-red-200 bg-white px-2 py-2 text-red-500 hover:bg-red-50 sm:col-span-1">
                <span aria-hidden="true">::</span>
            </button>
            <span data-clo-order
                class="inline-flex items-center justify-center rounded-lg border border-red-200 bg-red-50 px-2 py-2 text-xs font-bold text-red-700 sm:col-span-1">0</span>
            <input name="clos[__INDEX__][statement]" placeholder="CLO statement"
                class="sm:col-span-7 rounded-lg border border-red-200 px-3 py-2 text-sm" />
            <select name="clos[__INDEX__][bloom_level]"
                class="sm:col-span-2 rounded-lg border border-red-200 px-3 py-2 text-sm">
                <option value="C1">C1</option>
                <option value="C2">C2</option>
                <option value="C3">C3</option>
                <option value="C4">C4</option>
                <option value="C5">C5</option>
                <option value="C6">C6</option>
            </select>
            <button type="button"
                class="remove-row rounded-lg border border-rose-300 px-2 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-50 sm:col-span-1">Remove</button>
        </div>
    </template>

    <template id="requisiteTemplate">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-12 repeater-row">
            <select name="requisites[__INDEX__][type]"
                class="sm:col-span-2 rounded-lg border border-red-200 px-3 py-2 text-sm">
                <option value="prerequisite">Prerequisite</option>
                <option value="corequisite">Corequisite</option>
            </select>
            <input name="requisites[__INDEX__][course_code]" placeholder="Code"
                class="sm:col-span-3 rounded-lg border border-red-200 px-3 py-2 text-sm" />
            <input name="requisites[__INDEX__][course_name]" placeholder="Course name"
                class="sm:col-span-6 rounded-lg border border-red-200 px-3 py-2 text-sm" />
            <button type="button"
                class="remove-row rounded-lg border border-rose-300 px-2 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-50">Remove</button>
        </div>
    </template>

    <template id="assessmentTemplate">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-12 repeater-row assessment-row">
            <input name="assessments[__INDEX__][component]" placeholder="Component"
                class="sm:col-span-4 rounded-lg border border-red-200 px-3 py-2 text-sm" />
            <input type="number" step="0.01" name="assessments[__INDEX__][weightage]" placeholder="%"
                class="assessment-input sm:col-span-2 rounded-lg border border-red-200 px-3 py-2 text-sm" />
            <input name="assessments[__INDEX__][remarks]" placeholder="Remarks"
                class="sm:col-span-5 rounded-lg border border-red-200 px-3 py-2 text-sm" />
            <button type="button"
                class="remove-row rounded-lg border border-rose-300 px-2 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-50">Remove</button>
        </div>
    </template>

    <template id="topicTemplate">
        <div draggable="true" data-sortable-row
            class="grid grid-cols-1 gap-3 sm:grid-cols-12 repeater-row sortable-row rounded-xl border border-transparent p-2 transition">
            <button type="button" title="Drag to reorder"
                class="drag-handle rounded-lg border border-red-200 bg-white px-2 py-2 text-red-500 hover:bg-red-50 sm:col-span-1">
                <span aria-hidden="true">::</span>
            </button>
            <input type="number" min="1" readonly name="topics[__INDEX__][week_no]" value="1"
                class="topic-week-input sm:col-span-2 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm font-semibold text-red-800" />
            <input name="topics[__INDEX__][title]" placeholder="Topic"
                class="sm:col-span-4 rounded-lg border border-red-200 px-3 py-2 text-sm" />
            <input name="topics[__INDEX__][learning_activity]" placeholder="Learning activity"
                class="sm:col-span-4 rounded-lg border border-red-200 px-3 py-2 text-sm" />
            <button type="button"
                class="remove-row rounded-lg border border-rose-300 px-2 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-50 sm:col-span-1">Remove</button>
        </div>
    </template>

    <template id="sltTemplate">
        <div draggable="true" data-sortable-row
            class="grid grid-cols-1 gap-3 sm:grid-cols-12 slt-row repeater-row sortable-row rounded-xl border border-transparent p-2 transition">
            <button type="button" title="Drag to reorder"
                class="drag-handle rounded-lg border border-red-200 bg-white px-2 py-2 text-red-500 hover:bg-red-50 sm:col-span-1">
                <span aria-hidden="true">::</span>
            </button>
            <input name="slt[__INDEX__][activity]" placeholder="Activity"
                class="sm:col-span-3 rounded-lg border border-red-200 px-3 py-2 text-sm" />
            <input type="number" step="0.01" min="0" name="slt[__INDEX__][f2f_hours]" value="0"
                class="slt-input sm:col-span-2 rounded-lg border border-red-200 px-3 py-2 text-sm" />
            <input type="number" step="0.01" min="0" name="slt[__INDEX__][non_f2f_hours]" value="0"
                class="slt-input sm:col-span-2 rounded-lg border border-red-200 px-3 py-2 text-sm" />
            <input type="number" step="0.01" min="0" name="slt[__INDEX__][independent_hours]" value="0"
                class="slt-input sm:col-span-2 rounded-lg border border-red-200 px-3 py-2 text-sm" />
            <input type="text" readonly value="0"
                class="slt-total sm:col-span-1 rounded-lg border border-red-100 bg-red-50 px-3 py-2 text-sm font-semibold text-red-800" />
            <button type="button"
                class="remove-row rounded-lg border border-rose-300 px-2 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-50 sm:col-span-1">Remove</button>
        </div>
    </template>

    <script>
        const tabButtons = document.querySelectorAll('.tab-btn');
        const panes = document.querySelectorAll('.tab-pane');
        const rejectDecisionModal = document.getElementById('rejectDecisionModal');
        const rejectCommentsField = document.getElementById('reject_workflow_comments');
        let draggingRow = null;

        function openRejectModal() {
            if (!rejectDecisionModal) return;
            rejectDecisionModal.classList.remove('hidden');
            rejectDecisionModal.classList.add('flex');
            document.body.style.overflow = 'hidden';
            if (rejectCommentsField) {
                rejectCommentsField.focus();
            }
        }

        function closeRejectModal() {
            if (!rejectDecisionModal) return;
            rejectDecisionModal.classList.add('hidden');
            rejectDecisionModal.classList.remove('flex');
            document.body.style.overflow = '';
        }

        tabButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                const target = btn.dataset.tab;

                tabButtons.forEach(b => b.classList.remove('bg-red-900', 'text-white', 'border-red-900'));
                btn.classList.add('bg-red-900', 'text-white', 'border-red-900');

                panes.forEach(pane => pane.classList.toggle('hidden', pane.dataset.tabPane !== target));
            });
        });

        function refreshSltTotals() {
            let grand = 0;
            document.querySelectorAll('.slt-row').forEach(row => {
                const nums = Array.from(row.querySelectorAll('.slt-input')).map(i => parseFloat(i.value || '0'));
                const sum = nums.reduce((a, b) => a + b, 0);
                grand += sum;
                const totalInput = row.querySelector('.slt-total');
                if (totalInput) totalInput.value = sum.toFixed(2);
            });
            const grandEl = document.getElementById('sltGrandTotal');
            if (grandEl) grandEl.textContent = grand.toFixed(2);
        }

        function refreshAssessmentTotal() {
            let total = 0;
            document.querySelectorAll('.assessment-input').forEach(i => total += parseFloat(i.value || '0'));
            const totalEl = document.getElementById('assessmentTotal');
            if (totalEl) totalEl.textContent = total.toFixed(2);
        }

        function refreshRepeaterMeta() {
            document.querySelectorAll('[data-repeater-key]').forEach(container => {
                const key = container.dataset.repeaterKey;
                const rows = container.querySelectorAll('.repeater-row').length;
                const badge = document.querySelector(`[data-tab-count="${key}"]`);
                const emptyState = container.querySelector('[data-empty-state]');

                if (badge) {
                    badge.textContent = String(rows);
                }

                if (emptyState) {
                    emptyState.classList.toggle('hidden', rows > 0);
                }
            });
        }

        function refreshSortableState() {
            document.querySelectorAll('[data-sortable-container]').forEach(container => {
                Array.from(container.querySelectorAll('[data-sortable-row]')).forEach((row, index) => {
                    row.dataset.sortIndex = String(index);
                    row.classList.remove('ring-2', 'ring-red-300', 'bg-red-50/50', 'opacity-60');

                    if (container.id === 'closRows') {
                        const badge = row.querySelector('[data-clo-order]');
                        if (badge) {
                            badge.textContent = String(index + 1);
                        }
                    }

                    if (container.id === 'topicRows') {
                        const weekInput = row.querySelector('.topic-week-input');
                        if (weekInput) {
                            weekInput.value = String(index + 1);
                        }
                    }
                });
            });
        }

        document.addEventListener('click', (event) => {
            if (event.target.closest('[data-open-reject-modal]')) {
                openRejectModal();
                return;
            }

            if (event.target.closest('[data-close-reject-modal]')) {
                closeRejectModal();
                return;
            }

            if (rejectDecisionModal && event.target === rejectDecisionModal) {
                closeRejectModal();
                return;
            }

            const addBtn = event.target.closest('[data-add-target]');
            if (addBtn) {
                const targetId = addBtn.getAttribute('data-add-target');
                const templateId = addBtn.getAttribute('data-template');
                const container = document.getElementById(targetId);
                const template = document.getElementById(templateId);

                if (!container || !template) return;

                const index = parseInt(container.dataset.nextIndex || '0', 10);
                container.dataset.nextIndex = String(index + 1);

                const html = template.innerHTML.replaceAll('__INDEX__', String(index));
                const wrapper = document.createElement('div');
                wrapper.innerHTML = html.trim();
                container.appendChild(wrapper.firstElementChild);

                refreshSltTotals();
                refreshAssessmentTotal();
                refreshRepeaterMeta();
                refreshSortableState();
                return;
            }

            const removeBtn = event.target.closest('.remove-row');
            if (removeBtn) {
                const row = removeBtn.closest('.repeater-row');
                if (row) row.remove();
                refreshSltTotals();
                refreshAssessmentTotal();
                refreshRepeaterMeta();
                refreshSortableState();
            }
        });

        document.addEventListener('dragstart', (event) => {
            const row = event.target.closest('[data-sortable-row]');
            if (!row) return;

            draggingRow = row;
            row.classList.add('opacity-60');

            if (event.dataTransfer) {
                event.dataTransfer.effectAllowed = 'move';
                event.dataTransfer.setData('text/plain', row.dataset.sortIndex || '0');
            }
        });

        document.addEventListener('dragover', (event) => {
            const container = event.target.closest('[data-sortable-container]');
            if (!container || !draggingRow) return;

            event.preventDefault();

            const targetRow = event.target.closest('[data-sortable-row]');
            if (!targetRow || targetRow === draggingRow || targetRow.parentElement !== container) return;

            const rect = targetRow.getBoundingClientRect();
            const insertAfter = event.clientY > rect.top + (rect.height / 2);

            targetRow.classList.add('ring-2', 'ring-red-300', 'bg-red-50/50');

            if (insertAfter) {
                targetRow.after(draggingRow);
            } else {
                targetRow.before(draggingRow);
            }
        });

        document.addEventListener('dragleave', (event) => {
            const row = event.target.closest('[data-sortable-row]');
            if (row) {
                row.classList.remove('ring-2', 'ring-red-300', 'bg-red-50/50');
            }
        });

        document.addEventListener('dragend', () => {
            document.querySelectorAll('[data-sortable-row]').forEach(row => {
                row.classList.remove('opacity-60', 'ring-2', 'ring-red-300', 'bg-red-50/50');
            });

            draggingRow = null;
            refreshSortableState();
            refreshSltTotals();
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeRejectModal();
            }
        });

        document.addEventListener('input', (event) => {
            if (event.target.classList.contains('slt-input')) {
                refreshSltTotals();
            }
            if (event.target.classList.contains('assessment-input')) {
                refreshAssessmentTotal();
            }
        });

        refreshSltTotals();
        refreshAssessmentTotal();
        refreshRepeaterMeta();
        refreshSortableState();
    </script>
@endsection
