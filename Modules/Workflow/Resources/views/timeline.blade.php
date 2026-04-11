@extends('layouts.app')

@php
    $entityLabel = match ($workflow->entity_type) {
        'Modules\\Course\\Models\\Course' => 'Course',
        'Modules\\Programme\\Models\\Programme' => 'Programme',
        default => 'Entity',
    };

    $entityTitle = trim(
        collect([$workflow->entity?->code, $workflow->entity?->name])
            ->filter()
            ->join(' - '),
    );

    $statusClasses = match ($workflow->status) {
        'approved' => 'bg-emerald-100 text-emerald-700',
        'rejected' => 'bg-rose-100 text-rose-700',
        'in_progress' => 'bg-sky-100 text-sky-700',
        'withdrawn' => 'bg-slate-100 text-slate-700',
        default => 'bg-red-100 text-red-700',
    };

    $viewerRoles = auth()->user()?->roles()->pluck('name')->toArray() ?? [];
    $canAct = $workflow->isStatus('in_progress') && $workflow->currentStep?->userHasRequiredRole($viewerRoles);
    $stepCount = $workflow->workflow->steps()->count();
@endphp

@section('content')
    <div class="ams-shell">
        <section
            class="relative overflow-hidden rounded-4xl border border-white/70 bg-[linear-gradient(135deg,rgba(47,6,6,0.97),rgba(126,23,29,0.84))] px-6 py-7 text-white shadow-[0_28px_80px_-34px_rgba(47,6,6,0.68)] sm:px-8">
            <div class="absolute right-0 top-0 h-44 w-44 rounded-full bg-white/10 blur-3xl"></div>
            <div class="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-3xl space-y-3">
                    <span
                        class="inline-flex items-center rounded-full border border-white/20 bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.24em] text-red-50/90">
                        Workflow Timeline
                    </span>
                    <div>
                        <h1 class="text-3xl font-semibold tracking-tight sm:text-4xl">{{ $entityLabel }} approval journey
                        </h1>
                        <p class="mt-2 text-sm text-red-50/80 sm:text-base">
                            {{ $entityTitle ?: 'Track every transition, reviewer action, and decision artefact across the workflow lifecycle.' }}
                        </p>
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-3">
                    <div class="rounded-3xl border border-white/15 bg-white/10 px-4 py-4 backdrop-blur">
                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-red-100/80">Progress</p>
                        <p class="mt-2 text-2xl font-semibold">{{ $workflow->getProgressPercentage() }}%</p>
                    </div>
                    <div class="rounded-3xl border border-white/15 bg-white/10 px-4 py-4 backdrop-blur">
                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-red-100/80">Status</p>
                        <p class="mt-2 text-lg font-semibold">{{ str($workflow->status)->headline() }}</p>
                    </div>
                    <div class="rounded-3xl border border-white/15 bg-white/10 px-4 py-4 backdrop-blur">
                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-red-100/80">Current Step</p>
                        <p class="mt-2 text-sm font-semibold">{{ $workflow->currentStep?->title ?? 'No active step' }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <article class="ams-card p-5">
                <p class="ams-stat-label">Workflow</p>
                <p class="mt-3 text-lg font-semibold text-red-950">{{ $workflow->workflow->name }}</p>
                <p class="mt-2 text-sm text-red-700">Structured approval template in use.</p>
            </article>
            <article class="ams-card p-5">
                <p class="ams-stat-label">Active Stage</p>
                <p class="mt-3 text-lg font-semibold text-red-950">
                    {{ $workflow->currentStep?->approval_level ? $workflow->currentStep->approval_level . ' / ' . $stepCount : 'Completed' }}
                </p>
                <p class="mt-2 text-sm text-red-700">Current routing position.</p>
            </article>
            <article class="ams-card p-5">
                <p class="ams-stat-label">Submitted</p>
                <p class="mt-3 text-lg font-semibold text-red-950">{{ $workflow->submittedBy?->name ?? 'Not submitted' }}
                </p>
                <p class="mt-2 text-sm text-red-700">
                    {{ $workflow->submitted_at?->format('M d, Y g:i A') ?? 'Submission has not happened yet.' }}</p>
            </article>
            <article class="ams-card p-5">
                <p class="ams-stat-label">State</p>
                <span class="ams-badge mt-3 {{ $statusClasses }}">{{ str($workflow->status)->headline() }}</span>
                <p class="mt-2 text-sm text-red-700">Current workflow outcome state.</p>
            </article>
        </section>

        <section class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">
            <div class="space-y-6">
                <div class="ams-card p-6 sm:p-8">
                    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="ams-stat-label">Progress Meter</p>
                            <h2 class="mt-2 text-2xl font-semibold text-red-950">Workflow completion</h2>
                        </div>
                        <span class="text-sm font-medium text-red-700">{{ $workflow->getProgressPercentage() }}%
                            complete</span>
                    </div>

                    <div class="h-3 overflow-hidden rounded-full bg-red-100">
                        <div class="h-full rounded-full bg-[linear-gradient(90deg,#7e171d,#bf3039)]"
                            style="width: {{ $workflow->getProgressPercentage() }}%"></div>
                    </div>
                </div>

                <div class="ams-card p-6 sm:p-8">
                    <div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="ams-stat-label">Event Stream</p>
                            <h2 class="mt-2 text-2xl font-semibold text-red-950">Timeline</h2>
                        </div>
                        <p class="text-sm text-red-700">Every action, comment, and decision is preserved in sequence.</p>
                    </div>

                    @if ($timeline->isNotEmpty())
                        <div
                            class="relative space-y-6 before:absolute before:left-[1.1rem] before:top-2 before:h-[calc(100%-1rem)] before:w-px before:bg-red-100">
                            @foreach ($timeline as $log)
                                @php
                                    $iconClasses = match ($log->action) {
                                        'approved' => 'bg-emerald-100 text-emerald-700',
                                        'rejected' => 'bg-rose-100 text-rose-700',
                                        'commented' => 'bg-amber-100 text-amber-700',
                                        'clarification_requested' => 'bg-sky-100 text-sky-700',
                                        default => 'bg-red-100 text-red-700',
                                    };

                                    $icon = match ($log->action) {
                                        'approved' => 'check',
                                        'rejected' => 'x',
                                        'commented' => 'comment',
                                        'clarification_requested' => 'question',
                                        default => 'arrow',
                                    };
                                @endphp
                                <article class="relative pl-12">
                                    <div
                                        class="absolute left-0 top-1 flex h-9 w-9 items-center justify-center rounded-full {{ $iconClasses }} ring-8 ring-white">
                                        @if ($icon === 'check')
                                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd"
                                                    d="M16.704 5.29a1 1 0 010 1.42l-7.2 7.2a1 1 0 01-1.415 0l-3-3a1 1 0 111.414-1.42l2.293 2.294 6.493-6.494a1 1 0 011.415 0z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        @elseif ($icon === 'x')
                                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd"
                                                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        @elseif ($icon === 'comment')
                                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd"
                                                    d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.146-3.055A6.775 6.775 0 012 10c0-3.866 3.582-7 8-7s8 3.134 8 7zm-11-1a1 1 0 100 2h6a1 1 0 100-2H7z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        @elseif ($icon === 'question')
                                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd"
                                                    d="M18 10A8 8 0 112 10a8 8 0 0116 0zm-7-3a2 2 0 00-2 2 1 1 0 11-2 0 4 4 0 118 0c0 1.098-.5 1.763-1.172 2.252-.596.434-1.25.698-1.25 1.248a1 1 0 11-2 0c0-1.62 1.165-2.402 2.074-3.064.361-.263.348-.422.348-.436a2 2 0 00-2-2zm-1 8a1.5 1.5 0 113 0 1.5 1.5 0 01-3 0z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        @else
                                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd"
                                                    d="M10.293 15.707a1 1 0 010-1.414L13.586 11H5a1 1 0 110-2h8.586l-3.293-3.293a1 1 0 111.414-1.414l5 5a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        @endif
                                    </div>

                                    <div
                                        class="rounded-3xl border border-red-100 bg-white p-5 shadow-[0_20px_45px_-35px_rgba(95,15,19,0.45)]">
                                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                            <div>
                                                <h3 class="text-lg font-semibold text-red-950">{{ $log->getActionLabel() }}
                                                </h3>
                                                <p class="mt-1 text-sm text-red-700">
                                                    <span
                                                        class="font-semibold text-red-900">{{ $log->user?->name ?? 'System' }}</span>
                                                    @if ($log->workflowStep)
                                                        <span> on {{ $log->workflowStep->title }}</span>
                                                    @endif
                                                </p>
                                            </div>
                                            <span
                                                class="text-xs font-medium uppercase tracking-[0.18em] text-red-500">{{ $log->created_at->format('M d, Y g:i A') }}</span>
                                        </div>

                                        @if ($log->comment)
                                            <div
                                                class="mt-4 rounded-2xl border border-red-100 bg-red-50/70 px-4 py-3 text-sm text-red-800">
                                                {{ $log->comment }}
                                            </div>
                                        @endif
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @else
                        <div
                            class="rounded-3xl border border-dashed border-red-200 bg-red-50/60 px-6 py-12 text-center text-red-700">
                            No workflow activity has been recorded yet.
                        </div>
                    @endif
                </div>
            </div>

            <aside class="space-y-6">
                <div class="ams-card p-6">
                    <p class="ams-stat-label">Workflow Information</p>
                    <div class="mt-5 space-y-5 text-sm text-red-800">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-red-600">Current step</p>
                            <p class="mt-2 font-semibold text-red-950">
                                {{ $workflow->currentStep?->title ?? 'No active step' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-red-600">Submitted by</p>
                            <p class="mt-2 font-semibold text-red-950">
                                {{ $workflow->submittedBy?->name ?? 'Not submitted yet' }}</p>
                            @if ($workflow->submitted_at)
                                <p class="mt-1 text-xs text-red-600">{{ $workflow->submitted_at->format('M d, Y g:i A') }}
                                </p>
                            @endif
                        </div>

                        @if ($workflow->isStatus('approved'))
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-red-600">Approved by</p>
                                <p class="mt-2 font-semibold text-red-950">{{ $workflow->approvedBy?->name }}</p>
                                <p class="mt-1 text-xs text-red-600">{{ $workflow->approved_at?->format('M d, Y g:i A') }}
                                </p>
                            </div>
                        @endif

                        @if ($workflow->isStatus('rejected'))
                            <div class="rounded-[22px] border border-rose-200 bg-rose-50 px-4 py-4 text-rose-800">
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-rose-600">Rejected by</p>
                                <p class="mt-2 font-semibold">{{ $workflow->rejectedBy?->name }}</p>
                                <p class="mt-2 text-sm">{{ $workflow->rejection_reason }}</p>
                                <p class="mt-2 text-xs text-rose-600">
                                    {{ $workflow->rejected_at?->format('M d, Y g:i A') }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                @if ($canAct)
                    <div class="ams-card p-6">
                        <p class="ams-stat-label">Decision Actions</p>
                        <div class="mt-5 flex flex-col gap-3">
                            <button type="button" class="ams-button-primary justify-center"
                                data-modal-target="approveModal">Approve</button>
                            @if ($workflow->currentStep->allow_rejection)
                                <button type="button" class="ams-button-danger justify-center"
                                    data-modal-target="rejectModal">Reject</button>
                            @endif
                            <button type="button" class="ams-button-secondary justify-center"
                                data-modal-target="clarificationModal">Request clarification</button>
                            <button type="button" class="ams-button-secondary justify-center"
                                data-modal-target="commentModal">Add comment</button>
                        </div>
                    </div>
                @endif

                @if ($workflow->canEdit())
                    <div class="ams-card p-6">
                        <p class="ams-stat-label">Owner Actions</p>
                        <div class="mt-5 flex flex-col gap-3">
                            <button type="button" class="ams-button-primary justify-center"
                                data-modal-target="submitModal">Submit for approval</button>
                            <button type="button" class="ams-button-secondary justify-center"
                                id="withdrawWorkflow">Withdraw</button>
                        </div>
                    </div>
                @endif
            </aside>
        </section>
    </div>

    <div id="workflowToastContainer" class="fixed right-4 top-4 z-60 space-y-3"></div>

    <div id="submitModal" class="ams-modal">
        <div class="ams-modal-panel">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold text-red-950">Submit for approval</h2>
                    <p class="mt-1 text-sm text-red-700">Add an optional note for the first reviewer.</p>
                </div>
                <button type="button" class="text-red-400 transition hover:text-red-700"
                    data-close-modal>&times;</button>
            </div>
            <form id="submitForm" class="mt-6 space-y-4">
                @csrf
                <label class="space-y-2 text-sm font-medium text-red-900">
                    <span>Comment</span>
                    <textarea id="submitComment" class="ams-textarea" rows="4" placeholder="Add any context for reviewers."></textarea>
                </label>
                <div class="flex justify-end gap-3">
                    <button type="button" class="ams-button-secondary" data-close-modal>Cancel</button>
                    <button type="submit" class="ams-button-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>

    <div id="approveModal" class="ams-modal">
        <div class="ams-modal-panel">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold text-red-950">Approve stage</h2>
                    <p class="mt-1 text-sm text-red-700">Record an optional note before advancing the workflow.</p>
                </div>
                <button type="button" class="text-red-400 transition hover:text-red-700"
                    data-close-modal>&times;</button>
            </div>
            <form id="approveForm" class="mt-6 space-y-4">
                @csrf
                <label class="space-y-2 text-sm font-medium text-red-900">
                    <span>Comment</span>
                    <textarea id="approveComment" class="ams-textarea" rows="4"></textarea>
                </label>
                <div class="flex justify-end gap-3">
                    <button type="button" class="ams-button-secondary" data-close-modal>Cancel</button>
                    <button type="submit" class="ams-button-primary">Approve</button>
                </div>
            </form>
        </div>
    </div>

    <div id="rejectModal" class="ams-modal">
        <div class="ams-modal-panel">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold text-red-950">Reject workflow</h2>
                    <p class="mt-1 text-sm text-red-700">A reason is required to send this item back.</p>
                </div>
                <button type="button" class="text-red-400 transition hover:text-red-700"
                    data-close-modal>&times;</button>
            </div>
            <form id="rejectForm" class="mt-6 space-y-4">
                @csrf
                <label class="space-y-2 text-sm font-medium text-red-900">
                    <span>Reason for rejection</span>
                    <textarea id="rejectReason" class="ams-textarea" rows="4" required></textarea>
                </label>
                <div class="flex justify-end gap-3">
                    <button type="button" class="ams-button-secondary" data-close-modal>Cancel</button>
                    <button type="submit" class="ams-button-danger">Reject</button>
                </div>
            </form>
        </div>
    </div>

    <div id="clarificationModal" class="ams-modal">
        <div class="ams-modal-panel">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold text-red-950">Request clarification</h2>
                    <p class="mt-1 text-sm text-red-700">Explain what needs to be revised or clarified.</p>
                </div>
                <button type="button" class="text-red-400 transition hover:text-red-700"
                    data-close-modal>&times;</button>
            </div>
            <form id="clarificationForm" class="mt-6 space-y-4">
                @csrf
                <label class="space-y-2 text-sm font-medium text-red-900">
                    <span>Request details</span>
                    <textarea id="clarificationComment" class="ams-textarea" rows="4" required></textarea>
                </label>
                <div class="flex justify-end gap-3">
                    <button type="button" class="ams-button-secondary" data-close-modal>Cancel</button>
                    <button type="submit" class="ams-button-primary">Request</button>
                </div>
            </form>
        </div>
    </div>

    <div id="commentModal" class="ams-modal">
        <div class="ams-modal-panel">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold text-red-950">Add comment</h2>
                    <p class="mt-1 text-sm text-red-700">Attach a review note without changing workflow status.</p>
                </div>
                <button type="button" class="text-red-400 transition hover:text-red-700"
                    data-close-modal>&times;</button>
            </div>
            <form id="commentForm" class="mt-6 space-y-4">
                @csrf
                <label class="space-y-2 text-sm font-medium text-red-900">
                    <span>Comment</span>
                    <textarea id="commentText" class="ams-textarea" rows="4" required></textarea>
                </label>
                <div class="flex justify-end gap-3">
                    <button type="button" class="ams-button-secondary" data-close-modal>Cancel</button>
                    <button type="submit" class="ams-button-primary">Add comment</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modalTriggers = document.querySelectorAll('[data-modal-target]');
            const closeButtons = document.querySelectorAll('[data-close-modal]');
            const modals = document.querySelectorAll('.ams-modal');

            modalTriggers.forEach((trigger) => {
                trigger.addEventListener('click', () => openModal(trigger.dataset.modalTarget));
            });

            closeButtons.forEach((button) => {
                button.addEventListener('click', () => closeModal(button.closest('.ams-modal')));
            });

            modals.forEach((modal) => {
                modal.addEventListener('click', (event) => {
                    if (event.target === modal) {
                        closeModal(modal);
                    }
                });
            });

            document.getElementById('submitForm')?.addEventListener('submit', (event) => {
                event.preventDefault();
                makeRequest('{{ route('workflows.submit', $workflow) }}', {
                    comment: document.getElementById('submitComment').value
                }, 'Submitted for approval');
            });

            document.getElementById('approveForm')?.addEventListener('submit', (event) => {
                event.preventDefault();
                makeRequest('{{ route('workflows.approve', $workflow) }}', {
                    comment: document.getElementById('approveComment').value
                }, 'Approved successfully');
            });

            document.getElementById('rejectForm')?.addEventListener('submit', (event) => {
                event.preventDefault();
                makeRequest('{{ route('workflows.reject', $workflow) }}', {
                    reason: document.getElementById('rejectReason').value
                }, 'Rejected successfully');
            });

            document.getElementById('clarificationForm')?.addEventListener('submit', (event) => {
                event.preventDefault();
                makeRequest('{{ route('workflows.clarification', $workflow) }}', {
                    comment: document.getElementById('clarificationComment').value
                }, 'Clarification requested');
            });

            document.getElementById('commentForm')?.addEventListener('submit', (event) => {
                event.preventDefault();
                makeRequest('{{ route('workflows.comment', $workflow) }}', {
                    comment: document.getElementById('commentText').value
                }, 'Comment added');
            });

            document.getElementById('withdrawWorkflow')?.addEventListener('click', () => {
                if (window.confirm('Are you sure you want to withdraw this workflow?')) {
                    makeRequest('{{ route('workflows.withdraw', $workflow) }}', {}, 'Workflow withdrawn');
                }
            });

            function openModal(modalId) {
                const modal = document.getElementById(modalId);
                if (!modal) {
                    return;
                }

                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }

            function closeModal(modal) {
                if (!modal) {
                    return;
                }

                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }

            function closeAllModals() {
                modals.forEach((modal) => closeModal(modal));
            }

            async function makeRequest(url, data, successMessage) {
                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(data),
                    });

                    if (!response.ok) {
                        const errorData = await response.json().catch(() => ({}));
                        throw new Error(errorData.message ||
                            'An error occurred while processing the workflow action.');
                    }

                    showToast(successMessage, 'success');
                    closeAllModals();
                    window.setTimeout(() => window.location.reload(), 1200);
                } catch (error) {
                    showToast(error.message, 'error');
                }
            }

            function showToast(message, type) {
                const container = document.getElementById('workflowToastContainer');
                const toast = document.createElement('div');
                const classes = type === 'success' ?
                    'border-emerald-200 bg-emerald-50 text-emerald-800' :
                    'border-rose-200 bg-rose-50 text-rose-800';

                toast.className = `rounded-2xl border px-4 py-3 text-sm shadow-lg ${classes}`;
                toast.textContent = message;

                container.appendChild(toast);
                window.setTimeout(() => {
                    toast.remove();
                }, 4000);
            }
        });
    </script>
@endpush
