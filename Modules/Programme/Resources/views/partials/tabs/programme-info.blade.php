{{-- Programme Info Tab --}}
<div class="row">
    <div class="col-lg-8">
        <h5 class="mb-3">Detailed Information</h5>

        <div class="card border-0 bg-light p-3 mb-3">
            <strong>About This Programme</strong>
            <p class="mb-0 mt-2">{{ $programme->description ?? 'No description provided.' }}</p>
        </div>

        <h6 class="mt-4 mb-3">Requirements & Structure</h6>
        <ul class="list-group list-group-flush">
            <li class="list-group-item">
                <div class="d-flex justify-content-between">
                    <span>Total Semesters</span>
                    <strong>{{ $programme->duration_semesters }}</strong>
                </div>
            </li>
            <li class="list-group-item">
                <div class="d-flex justify-content-between">
                    <span>Academic Level</span>
                    <strong>{{ $programme->level }}</strong>
                </div>
            </li>
            <li class="list-group-item">
                <div class="d-flex justify-content-between">
                    <span>Accreditation Body</span>
                    <strong>{{ $programme->accreditation_body ?? 'Not specified' }}</strong>
                </div>
            </li>
            <li class="list-group-item">
                <div class="d-flex justify-content-between">
                    <span>Active Status</span>
                    <strong>
                        @if($programme->is_active)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-secondary">Inactive</span>
                        @endif
                    </strong>
                </div>
            </li>
        </ul>

        <h6 class="mt-4 mb-3">Programme Administration</h6>
        @if($programme->programmeChair)
            <div class="card border-0 bg-light p-3">
                <h6>Programme Chair</h6>
                <p class="mb-0">
                    <strong>{{ $programme->programmeChair->name }}</strong><br>
                    <small class="text-muted">{{ $programme->programmeChair->email }}</small>
                </p>
            </div>
        @else
            <div class="alert alert-warning mb-0">
                <i class="bi bi-exclamation-triangle me-2"></i>
                No programme chair assigned
            </div>
        @endif
    </div>

    <div class="col-lg-4">
        <h5 class="mb-3">Linked Objects</h5>
        <div class="list-group">
            <a href="#courses" class="list-group-item list-group-item-action" data-bs-toggle="tab">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0"><i class="bi bi-book me-2"></i>Courses</h6>
                    </div>
                    <span class="badge bg-primary">{{ $stats['total_courses'] }}</span>
                </div>
            </a>
            <a href="#plo" class="list-group-item list-group-item-action" data-bs-toggle="tab">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0"><i class="bi bi-bookmark me-2"></i>PLOs</h6>
                    </div>
                    <span class="badge bg-info">{{ $stats['total_plos'] }}</span>
                </div>
            </a>
            <a href="#peo" class="list-group-item list-group-item-action" data-bs-toggle="tab">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0"><i class="bi bi-bookmarks me-2"></i>PEOs</h6>
                    </div>
                    <span class="badge bg-warning">{{ $stats['total_peos'] }}</span>
                </div>
            </a>
            <a href="#study-plan" class="list-group-item list-group-item-action" data-bs-toggle="tab">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0"><i class="bi bi-calendar2-week me-2"></i>Study Plans</h6>
                    </div>
                    <span class="badge bg-success">{{ $stats['total_study_plans'] }}</span>
                </div>
            </a>
            <a href="#mapping" class="list-group-item list-group-item-action" data-bs-toggle="tab">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0"><i class="bi bi-diagram-3 me-2"></i>CLO-PLO Mappings</h6>
                    </div>
                    <span class="badge bg-secondary">{{ $stats['mapped_clos'] }}</span>
                </div>
            </a>
        </div>
    </div>
</div>
