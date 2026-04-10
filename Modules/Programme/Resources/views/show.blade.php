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
            @if($programme->status === 'draft')
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

    <!-- Tab Navigation -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom">
            <ul class="nav nav-tabs card-header-tabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="main-tab" data-bs-toggle="tab" 
                            data-bs-target="#main" type="button" role="tab">
                        <i class="bi bi-info-circle me-2"></i>Main Info
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="info-tab" data-bs-toggle="tab" 
                            data-bs-target="#info" type="button" role="tab">
                        <i class="bi bi-file-text me-2"></i>Programme Info
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="peo-tab" data-bs-toggle="tab" 
                            data-bs-target="#peo" type="button" role="tab">
                        <i class="bi bi-bookmarks me-2"></i>PEO
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="plo-tab" data-bs-toggle="tab" 
                            data-bs-target="#plo" type="button" role="tab">
                        <i class="bi bi-bookmark me-2"></i>PLO
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="courses-tab" data-bs-toggle="tab" 
                            data-bs-target="#courses" type="button" role="tab">
                        <i class="bi bi-book me-2"></i>Courses
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="study-plan-tab" data-bs-toggle="tab" 
                            data-bs-target="#study-plan" type="button" role="tab">
                        <i class="bi bi-calendar2-week me-2"></i>Study Plan
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="mapping-tab" data-bs-toggle="tab" 
                            data-bs-target="#mapping" type="button" role="tab">
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
