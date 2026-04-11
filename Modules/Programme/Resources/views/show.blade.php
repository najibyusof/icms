{{-- Programme Detail View with Multi-Tab Interface --}}
@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4 py-6">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h2 mb-0">{{ $programme->name }}</h1>
                <p class="text-muted mb-0">{{ $programme->code }} • {{ $programme->level }}</p>
            </div>
            <div>
                @can('update', $programme)
                    <a href="{{ route('programmes.edit', $programme) }}" class="btn btn-warning me-2">
                        <i class="bi bi-pencil me-2"></i>Edit
                    </a>
                @endcan
                @if ($workflowInstance?->id !== null)
                    <a href="{{ route('workflows.timeline', $workflowInstance) }}" class="btn btn-outline-primary me-2">
                        <i class="bi bi-diagram-3 me-2"></i>View Workflow
                    </a>
                @endif
                @if ($programme->status === 'draft')
                    <button class="btn btn-success" onclick="submitForApproval()">
                        <i class="bi bi-send me-2"></i>Submit for Approval
                    </button>
                @endif
            </div>
        </div>

        <!-- Status Badge & Basic Info -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-2">
                                <small class="text-muted d-block">Status</small>
                                @switch($programme->status)
                                    @case('approved')
                                        <span class="badge bg-success fs-6">Approved</span>
                                    @break

                                    @case('submitted')
                                        <span class="badge bg-info fs-6">Submitted</span>
                                    @break

                                    @case('in_review')
                                        <span class="badge bg-warning fs-6">In Review</span>
                                    @break

                                    @case('rejected')
                                        <span class="badge bg-danger fs-6">Rejected</span>
                                    @break

                                    @default
                                        <span class="badge bg-secondary fs-6">Draft</span>
                                @endswitch
                            </div>
                            <div class="col-md-2">
                                <small class="text-muted d-block">Chair</small>
                                <strong>{{ $programme->programmeChair?->name ?? 'Not assigned' }}</strong>
                            </div>
                            <div class="col-md-2">
                                <small class="text-muted d-block">Courses</small>
                                <strong>{{ $stats['total_courses'] }}</strong>
                            </div>
                            <div class="col-md-2">
                                <small class="text-muted d-block">PLOs</small>
                                <strong>{{ $stats['total_plos'] }}</strong>
                            </div>
                            <div class="col-md-2">
                                <small class="text-muted d-block">PEOs</small>
                                <strong>{{ $stats['total_peos'] }}</strong>
                            </div>
                            <div class="col-md-2">
                                <small class="text-muted d-block">Study Plans</small>
                                <strong>{{ $stats['total_study_plans'] }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if ($workflowInstance?->id !== null)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Workflow Progress Timeline</h6>
                        <small class="text-muted">
                            {{ $workflowInstance->workflow?->name }}
                            @if ($workflowInstance->currentStep)
                                • Current: {{ $workflowInstance->currentStep->title }}
                            @endif
                        </small>
                    </div>
                    <span
                        class="badge
                        @if ($workflowInstance->isStatus('approved')) bg-success
                        @elseif($workflowInstance->isStatus('rejected')) bg-danger
                        @elseif($workflowInstance->isStatus('in_progress')) bg-info
                        @else bg-secondary @endif">
                        {{ ucfirst(str_replace('_', ' ', $workflowInstance->status)) }}
                    </span>
                </div>
                <div class="card-body">
                    @if ($canWorkflowAct)
                        <div class="alert alert-warning d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <strong>Action required:</strong>
                                You are assigned to approve <strong>{{ $workflowInstance->currentStep?->title }}</strong>.
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-success btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#programmeApproveModal">Approve</button>
                                @if ($workflowInstance->currentStep?->allow_rejection)
                                    <button class="btn btn-danger btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#programmeRejectModal">Reject</button>
                                @endif
                                <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#programmeCommentModal">Comment</button>
                            </div>
                        </div>
                    @endif

                    <div class="timeline-list">
                        @forelse ($workflowTimeline as $log)
                            <div class="border rounded-3 p-3 mb-2">
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <div>
                                        <div class="fw-semibold">{{ $log->getActionLabel() }}</div>
                                        <div class="text-muted small">
                                            by {{ $log->user?->name ?? 'System' }}
                                            @if ($log->workflowStep)
                                                • {{ $log->workflowStep->title }}
                                            @endif
                                        </div>
                                    </div>
                                    <span class="text-muted small">{{ $log->created_at?->format('d M Y, h:i A') }}</span>
                                </div>
                                @if ($log->comment)
                                    <div class="mt-2 text-body-secondary small">{{ $log->comment }}</div>
                                @endif
                            </div>
                        @empty
                            <p class="text-muted mb-0">No workflow logs yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="modal fade" id="programmeApproveModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="POST" action="{{ route('workflows.approve', $workflowInstance) }}"
                            class="workflow-action-form" data-success="Step approved successfully.">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title">Approve Workflow Step</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <label for="approveComment" class="form-label">Comment</label>
                                <textarea id="approveComment" name="comment" class="form-control" rows="3"
                                    placeholder="Optional approval comment"></textarea>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-success">Approve</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="programmeRejectModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="POST" action="{{ route('workflows.reject', $workflowInstance) }}"
                            class="workflow-action-form" data-success="Step rejected successfully.">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title">Reject Workflow Step</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <label for="rejectReason" class="form-label">Reason <span
                                        class="text-danger">*</span></label>
                                <textarea id="rejectReason" name="reason" class="form-control" rows="4" required
                                    placeholder="Provide rejection reason"></textarea>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-danger">Reject</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="programmeCommentModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="POST" action="{{ route('workflows.comment', $workflowInstance) }}"
                            class="workflow-action-form" data-success="Comment added successfully.">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title">Add Workflow Comment</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <label for="workflowComment" class="form-label">Comment <span
                                        class="text-danger">*</span></label>
                                <textarea id="workflowComment" name="comment" class="form-control" rows="4" required
                                    placeholder="Add your notes"></textarea>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Save Comment</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        <!-- Tab Navigation -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <ul class="nav nav-tabs card-header-tabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="main-tab" data-bs-toggle="tab" data-bs-target="#main"
                            type="button" role="tab">
                            <i class="bi bi-info-circle me-2"></i>Main Info
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="info-tab" data-bs-toggle="tab" data-bs-target="#info"
                            type="button" role="tab">
                            <i class="bi bi-file-text me-2"></i>Programme Info
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="peo-tab" data-bs-toggle="tab" data-bs-target="#peo"
                            type="button" role="tab">
                            <i class="bi bi-bookmarks me-2"></i>PEO
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="plo-tab" data-bs-toggle="tab" data-bs-target="#plo"
                            type="button" role="tab">
                            <i class="bi bi-bookmark me-2"></i>PLO
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="courses-tab" data-bs-toggle="tab" data-bs-target="#courses"
                            type="button" role="tab">
                            <i class="bi bi-book me-2"></i>Courses
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="study-plan-tab" data-bs-toggle="tab" data-bs-target="#study-plan"
                            type="button" role="tab">
                            <i class="bi bi-calendar2-week me-2"></i>Study Plan
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="mapping-tab" data-bs-toggle="tab" data-bs-target="#mapping"
                            type="button" role="tab">
                            <i class="bi bi-diagram-3 me-2"></i>CLO-PLO Mapping
                        </button>
                    </li>
                </ul>
            </div>

            <div class="card-body">
                <div class="tab-content">
                    <!-- Main Info Tab -->
                    <div class="tab-pane fade show active" id="main" role="tabpanel">
                        @include('programme::partials.tabs.main-info')
                    </div>

                    <!-- Programme Info Tab -->
                    <div class="tab-pane fade" id="info" role="tabpanel">
                        @include('programme::partials.tabs.programme-info')
                    </div>

                    <!-- PEO Tab -->
                    <div class="tab-pane fade" id="peo" role="tabpanel">
                        @include('programme::partials.tabs.peo')
                    </div>

                    <!-- PLO Tab -->
                    <div class="tab-pane fade" id="plo" role="tabpanel">
                        @include('programme::partials.tabs.plo')
                    </div>

                    <!-- Courses Tab -->
                    <div class="tab-pane fade" id="courses" role="tabpanel">
                        @include('programme::partials.tabs.courses')
                    </div>

                    <!-- Study Plan Tab -->
                    <div class="tab-pane fade" id="study-plan" role="tabpanel">
                        @include('programme::partials.tabs.study-plan')
                    </div>

                    <!-- CLO-PLO Mapping Tab -->
                    <div class="tab-pane fade" id="mapping" role="tabpanel">
                        @include('programme::partials.tabs.mapping')
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.workflow-action-form').forEach((form) => {
                form.addEventListener('submit', async (event) => {
                    event.preventDefault();

                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector(
                                'meta[name="csrf-token"]').content,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(Object.fromEntries(new FormData(form)
                            .entries())),
                    });

                    const payload = await response.json();

                    if (!response.ok || !payload.success) {
                        alert(payload.message ?? 'Workflow action failed.');
                        return;
                    }

                    alert(form.dataset.success ?? payload.message ??
                        'Workflow action completed.');
                    location.reload();
                });
            });
        });

        function submitForApproval() {
            if (confirm('Submit this programme for approval? You won\'t be able to edit it until reviewed.')) {
                fetch(`/programmes/{{ $programme->id }}/submit-for-approval`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(r => r.json())
                    .then(d => {
                        if (d.success) {
                            alert(d.message);
                            location.reload();
                        }
                    })
                    .catch(e => alert('Error: ' + e.message));
            }
        }
    </script>
@endsection
