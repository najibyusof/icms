{{-- Main Info Tab --}}
<div class="row">
    <div class="col-lg-8">
        <h5 class="mb-4">Basic Information</h5>
        <dl class="row">
            <dt class="col-sm-3">Code</dt>
            <dd class="col-sm-9">
                <span class="badge bg-light text-dark fs-6">{{ $programme->code }}</span>
            </dd>

            <dt class="col-sm-3">Name</dt>
            <dd class="col-sm-9">{{ $programme->name }}</dd>

            <dt class="col-sm-3">Level</dt>
            <dd class="col-sm-9">{{ $programme->level }}</dd>

            <dt class="col-sm-3">Duration</dt>
            <dd class="col-sm-9">{{ $programme->duration_semesters }} semesters</dd>

            <dt class="col-sm-3">Description</dt>
            <dd class="col-sm-9">{{ $programme->description ?? 'Not provided' }}</dd>

            <dt class="col-sm-3">Accreditation</dt>
            <dd class="col-sm-9">{{ $programme->accreditation_body ?? 'Not specified' }}</dd>

            <dt class="col-sm-3">Status</dt>
            <dd class="col-sm-9">
                @switch($programme->status)
                    @case('approved')
                        <span class="badge bg-success">Approved</span>
                        @break
                    @case('submitted')
                        <span class="badge bg-info">Submitted</span>
                        @break
                    @case('in_review')
                        <span class="badge bg-warning">In Review</span>
                        @break
                    @case('rejected')
                        <span class="badge bg-danger">Rejected</span>
                        @break
                    @default
                        <span class="badge bg-secondary">Draft</span>
                @endswitch
            </dd>

            <dt class="col-sm-3">Chair</dt>
            <dd class="col-sm-9">
                @if($programme->programmeChair)
                    <div>
                        <strong>{{ $programme->programmeChair->name }}</strong>
                        <br>
                        <small class="text-muted">{{ $programme->programmeChair->email }}</small>
                    </div>
                @else
                    <span class="text-muted">Not assigned</span>
                @endif
            </dd>

            <dt class="col-sm-3">Created</dt>
            <dd class="col-sm-9">
                <small class="text-muted">{{ $programme->created_at->format('d M Y H:i') }}</small>
            </dd>

            <dt class="col-sm-3">Last Updated</dt>
            <dd class="col-sm-9">
                <small class="text-muted">{{ $programme->updated_at->format('d M Y H:i') }}</small>
            </dd>
        </dl>
    </div>

    <div class="col-lg-4">
        <h5 class="mb-4">Quick Stats</h5>
        <div class="list-group">
            <div class="list-group-item">
                <div class="d-flex justify-content-between align-items-center">
                    <strong>Total Courses</strong>
                    <span class="badge bg-primary">{{ $stats['total_courses'] }}</span>
                </div>
            </div>
            <div class="list-group-item">
                <div class="d-flex justify-content-between align-items-center">
                    <strong>PLOs</strong>
                    <span class="badge bg-info">{{ $stats['total_plos'] }}</span>
                </div>
            </div>
            <div class="list-group-item">
                <div class="d-flex justify-content-between align-items-center">
                    <strong>PEOs</strong>
                    <span class="badge bg-warning">{{ $stats['total_peos'] }}</span>
                </div>
            </div>
            <div class="list-group-item">
                <div class="d-flex justify-content-between align-items-center">
                    <strong>Study Plans</strong>
                    <span class="badge bg-success">{{ $stats['total_study_plans'] }}</span>
                </div>
            </div>
            <div class="list-group-item">
                <div class="d-flex justify-content-between align-items-center">
                    <strong>Mapped CLOs</strong>
                    <span class="badge bg-secondary">{{ $stats['mapped_clos'] }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
