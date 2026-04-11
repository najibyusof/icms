{{-- Workflow Timeline View --}}
@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4 py-6">
        <!-- Header -->
        <div class="mb-4">
            <h1 class="h2 mb-0">Workflow Timeline</h1>
            <p class="text-muted mb-0">
                @if ($workflow->entity_type === 'Modules\Course\Models\Course')
                    Course: {{ $workflow->entity?->code }} - {{ $workflow->entity?->name }}
                @elseif($workflow->entity_type === 'Modules\Programme\Models\Programme')
                    Programme: {{ $workflow->entity?->code }} - {{ $workflow->entity?->name }}
                @endif
            </p>
        </div>

        <div class="row">
            <!-- Timeline Column -->
            <div class="col-lg-8">
                <!-- Progress Bar -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h6 class="text-uppercase text-muted mb-3">Progress</h6>
                        <div class="progress mb-2" style="height: 24px;">
                            <div class="progress-bar" role="progressbar"
                                style="width: {{ $workflow->getProgressPercentage() }}%;"
                                aria-valuenow="{{ $workflow->getProgressPercentage() }}" aria-valuemin="0"
                                aria-valuemax="100">
                                {{ $workflow->getProgressPercentage() }}%
                            </div>
                        </div>
                        <small class="text-muted">
                            Status:
                            <span
                                class="badge
                            @if ($workflow->isStatus('approved')) bg-success
                            @elseif($workflow->isStatus('rejected')) bg-danger
                            @elseif($workflow->isStatus('in_progress')) bg-info
                            @else bg-secondary @endif">
                                {{ ucfirst($workflow->status) }}
                            </span>
                        </small>
                    </div>
                </div>

                <!-- Timeline -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h6 class="text-uppercase text-muted mb-4">Activity Timeline</h6>

                        @if ($timeline->isNotEmpty())
                            <div class="timeline">
                                @foreach ($timeline as $log)
                                    <div class="timeline-item mb-4">
                                        <div class="timeline-marker bg-primary text-white rounded-circle"
                                            style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                            @if ($log->action === 'approved')
                                                <i class="bi bi-check-lg"></i>
                                            @elseif($log->action === 'rejected')
                                                <i class="bi bi-x-lg"></i>
                                            @elseif($log->action === 'commented')
                                                <i class="bi bi-chat-dots"></i>
                                            @elseif($log->action === 'clarification_requested')
                                                <i class="bi bi-question-circle"></i>
                                            @else
                                                <i class="bi bi-arrow-right"></i>
                                            @endif
                                        </div>

                                        <div class="ms-3">
                                            <h6 class="mb-1">{{ $log->getActionLabel() }}</h6>
                                            <p class="mb-1 text-muted">
                                                <strong>{{ $log->user->name }}</strong>
                                                @if ($log->workflowStep)
                                                    - {{ $log->workflowStep->title }}
                                                @endif
                                            </p>
                                            @if ($log->comment)
                                                <div class="bg-light p-2 rounded mt-2 mb-2">
                                                    <small>{{ $log->comment }}</small>
                                                </div>
                                            @endif
                                            <small
                                                class="text-muted">{{ $log->created_at->format('M d, Y g:i A') }}</small>
                                        </div>
                                    </div>

                                    @if (!$loop->last)
                                        <div class="ms-5 ps-2 pb-3 border-start border-2"></div>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-5 text-muted">
                                <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                                <p class="mt-2">No activity yet</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Sidebar Column -->
            <div class="col-lg-4">
                <!-- Workflow Info Card -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">Workflow Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <small class="text-uppercase text-muted">Workflow Name</small>
                            <p class="mb-0"><strong>{{ $workflow->workflow->name }}</strong></p>
                        </div>

                        <div class="mb-3">
                            <small class="text-uppercase text-muted">Current Step</small>
                            <p class="mb-0">
                                @if ($workflow->currentStep)
                                    <strong>{{ $workflow->currentStep->title }}</strong>
                                    <span class="badge bg-info ms-2">{{ $workflow->currentStep->approval_level }} of
                                        {{ $workflow->workflow->steps()->count() }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </p>
                        </div>

                        <div class="mb-3">
                            <small class="text-uppercase text-muted">Submitted By</small>
                            <p class="mb-0">
                                @if ($workflow->submittedBy)
                                    <strong>{{ $workflow->submittedBy->name }}</strong><br>
                                    <small
                                        class="text-muted">{{ $workflow->submitted_at?->format('M d, Y g:i A') }}</small>
                                @else
                                    <span class="text-muted">Not submitted yet</span>
                                @endif
                            </p>
                        </div>

                        @if ($workflow->isStatus('approved'))
                            <div class="mb-3">
                                <small class="text-uppercase text-muted">Approved By</small>
                                <p class="mb-0">
                                    <strong>{{ $workflow->approvedBy->name }}</strong><br>
                                    <small class="text-muted">{{ $workflow->approved_at?->format('M d, Y g:i A') }}</small>
                                </p>
                            </div>
                        @endif

                        @if ($workflow->isStatus('rejected'))
                            <div class="mb-3 alert alert-danger">
                                <small class="text-uppercase"><strong>Rejected By</strong></small>
                                <p class="mb-1">{{ $workflow->rejectedBy->name }}</p>
                                <p class="mb-0 text-muted">{{ $workflow->rejection_reason }}</p>
                                <small
                                    class="text-muted d-block mt-1">{{ $workflow->rejected_at?->format('M d, Y g:i A') }}</small>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Action Buttons Card -->
                @if (
                    $workflow->isStatus('in_progress') &&
                        $workflow->currentStep?->userHasRequiredRole(auth()->user()->roles()->pluck('name')->toArray()))
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Actions</h6>
                        </div>
                        <div class="card-body d-grid gap-2">
                            <!-- Approve Button -->
                            <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                data-bs-target="#approveModal">
                                <i class="bi bi-check-lg me-2"></i>Approve
                            </button>

                            @if ($workflow->currentStep->allow_rejection)
                                <!-- Reject Button -->
                                <button type="button" class="btn btn-danger" data-bs-toggle="modal"
                                    data-bs-target="#rejectModal">
                                    <i class="bi bi-x-lg me-2"></i>Reject
                                </button>
                            @endif

                            <!-- Clarification Button -->
                            <button type="button" class="btn btn-warning" data-bs-toggle="modal"
                                data-bs-target="#clarificationModal">
                                <i class="bi bi-question-circle me-2"></i>Request Clarification
                            </button>

                            <!-- Comment Button -->
                            <button type="button" class="btn btn-info" data-bs-toggle="modal"
                                data-bs-target="#commentModal">
                                <i class="bi bi-chat-dots me-2"></i>Add Comment
                            </button>
                        </div>
                    </div>
                @endif

                @if ($workflow->canEdit())
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body d-grid gap-2">
                            <!-- Submit Button -->
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                data-bs-target="#submitModal">
                                <i class="bi bi-send me-2"></i>Submit for Approval
                            </button>

                            <!-- Withdraw Button -->
                            <button type="button" class="btn btn-outline-secondary" onclick="confirmWithdraw()">
                                <i class="bi bi-arrow-counterclockwise me-2"></i>Withdraw
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modals -->

    <!-- Submit Modal -->
    <div class="modal fade" id="submitModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Submit for Approval</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="submitForm">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="submitComment" class="form-label">Comment (Optional)</label>
                            <textarea class="form-control" id="submitComment" name="comment" rows="3"
                                placeholder="Add any additional information..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Approve Modal -->
    <div class="modal fade" id="approveModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Approve</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="approveForm">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="approveComment" class="form-label">Comment (Optional)</label>
                            <textarea class="form-control" id="approveComment" name="comment" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Approve</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reject</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="rejectForm">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="rejectReason" class="form-label">Reason for Rejection <span
                                    class="text-danger">*</span></label>
                            <textarea class="form-control" id="rejectReason" name="reason" rows="4" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Reject</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Clarification Modal -->
    <div class="modal fade" id="clarificationModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Request Clarification</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="clarificationForm">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="clarificationComment" class="form-label">Request Details <span
                                    class="text-danger">*</span></label>
                            <textarea class="form-control" id="clarificationComment" name="comment" rows="4" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Comment Modal -->
    <div class="modal fade" id="commentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Comment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="commentForm">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="commentText" class="form-label">Comment <span
                                    class="text-danger">*</span></label>
                            <textarea class="form-control" id="commentText" name="comment" rows="4" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Comment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            setupFormHandlers();
        });

        function setupFormHandlers() {
            // Submit Form
            document.getElementById('submitForm')?.addEventListener('submit', function(e) {
                e.preventDefault();
                submitWorkflow();
            });

            // Approve Form
            document.getElementById('approveForm')?.addEventListener('submit', function(e) {
                e.preventDefault();
                approveWorkflow();
            });

            // Reject Form
            document.getElementById('rejectForm')?.addEventListener('submit', function(e) {
                e.preventDefault();
                rejectWorkflow();
            });

            // Clarification Form
            document.getElementById('clarificationForm')?.addEventListener('submit', function(e) {
                e.preventDefault();
                requestClarification();
            });

            // Comment Form
            document.getElementById('commentForm')?.addEventListener('submit', function(e) {
                e.preventDefault();
                addComment();
            });
        }

        function submitWorkflow() {
            const comment = document.getElementById('submitComment').value;
            makeRequest('{{ route('workflows.submit', $workflow) }}', 'POST', {
                comment
            }, 'Submitted for approval');
        }

        function approveWorkflow() {
            const comment = document.getElementById('approveComment').value;
            makeRequest('{{ route('workflows.approve', $workflow) }}', 'POST', {
                comment
            }, 'Approved successfully');
        }

        function rejectWorkflow() {
            const reason = document.getElementById('rejectReason').value;
            makeRequest('{{ route('workflows.reject', $workflow) }}', 'POST', {
                reason
            }, 'Rejected successfully');
        }

        function requestClarification() {
            const comment = document.getElementById('clarificationComment').value;
            makeRequest('{{ route('workflows.clarification', $workflow) }}', 'POST', {
                comment
            }, 'Clarification requested');
        }

        function addComment() {
            const comment = document.getElementById('commentText').value;
            makeRequest('{{ route('workflows.comment', $workflow) }}', 'POST', {
                comment
            }, 'Comment added', true);
        }

        function confirmWithdraw() {
            if (confirm('Are you sure you want to withdraw this workflow?')) {
                makeRequest('{{ route('workflows.withdraw', $workflow) }}', 'POST', {}, 'Workflow withdrawn');
            }
        }

        function makeRequest(url, method, data, successMessage, reloadTimeline = false) {
            const token = document.querySelector('meta[name="csrf-token"]').content;

            fetch(url, {
                    method: method,
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(data)
                })
                .then(response => {
                    if (!response.ok) return Promise.reject(response);
                    return response.json();
                })
                .then(result => {
                    showSuccess(successMessage);
                    closeModals();

                    if (reloadTimeline || true) {
                        setTimeout(() => location.reload(), 1500);
                    }
                })
                .catch(error => {
                    error.json().then(data => {
                        showError(data.message || 'An error occurred');
                    });
                });
        }

        function showSuccess(message) {
            const alert = document.createElement('div');
            alert.className = 'alert alert-success alert-dismissible fade show position-fixed';
            alert.style.top = '20px';
            alert.style.right = '20px';
            alert.style.zIndex = '9999';
            alert.innerHTML = `
        <i class="bi bi-check-circle me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
            document.body.appendChild(alert);

            setTimeout(() => alert.remove(), 5000);
        }

        function showError(message) {
            const alert = document.createElement('div');
            alert.className = 'alert alert-danger alert-dismissible fade show position-fixed';
            alert.style.top = '20px';
            alert.style.right = '20px';
            alert.style.zIndex = '9999';
            alert.innerHTML = `
        <i class="bi bi-exclamation-circle me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
            document.body.appendChild(alert);
        }

        function closeModals() {
            document.getElementById('submitModal')?.querySelector('.btn-close').click();
            document.getElementById('approveModal')?.querySelector('.btn-close').click();
            document.getElementById('rejectModal')?.querySelector('.btn-close').click();
            document.getElementById('clarificationModal')?.querySelector('.btn-close').click();
            document.getElementById('commentModal')?.querySelector('.btn-close').click();
        }
    </script>

    <style>
        .timeline {
            position: relative;
            padding-left: 20px;
        }

        .timeline-item {
            position: relative;
            padding-left: 50px;
            min-height: 50px;
        }

        .timeline-marker {
            position: absolute;
            left: -30px;
            top: 0;
        }

        .border-start {
            border-left: 2px solid #dee2e6 !important;
        }

        .timeline-item:first-child .timeline-marker {
            background-color: #0d6efd !important;
        }

        .timeline-item .timeline-marker {
            background-color: #6c757d !important;
        }

        .timeline-item:last-child .ms-5.ps-2.pb-3 {
            display: none;
        }
    </style>
@endsection
