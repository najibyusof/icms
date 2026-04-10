{{-- Group Info Tab Partial --}}
<div class="p-4">
    <div class="row">
        <div class="col-md-6">
            <div class="mb-4">
                <h6 class="text-uppercase text-muted mb-2">Programme</h6>
                <p class="mb-0">
                    @if ($group->programme)
                        <strong>{{ $group->programme->code }}</strong><br>
                        <small class="text-muted">{{ $group->programme->name }}</small>
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </p>
            </div>

            <div class="mb-4">
                <h6 class="text-uppercase text-muted mb-2">Intake Year</h6>
                <p class="mb-0"><strong>{{ $group->intake_year }}</strong></p>
            </div>

            <div class="mb-4">
                <h6 class="text-uppercase text-muted mb-2">Semester</h6>
                <p class="mb-0"><strong>{{ $group->semester }}</strong></p>
            </div>
        </div>

        <div class="col-md-6">
            <div class="mb-4">
                <h6 class="text-uppercase text-muted mb-2">Coordinator</h6>
                <p class="mb-0">
                    @if ($group->coordinator)
                        <strong>{{ $group->coordinator->name }}</strong><br>
                        <small class="text-muted">{{ $group->coordinator->email }}</small>
                    @else
                        <span class="text-muted">Not assigned</span>
                    @endif
                </p>
            </div>

            <div class="mb-4">
                <h6 class="text-uppercase text-muted mb-2">Status</h6>
                <p class="mb-0">
                    @if ($group->is_active)
                        <span class="badge bg-success bg-opacity-10 text-success">Active</span>
                    @else
                        <span class="badge bg-secondary bg-opacity-10 text-secondary">Inactive</span>
                    @endif
                </p>
            </div>

            <div class="mb-4">
                <h6 class="text-uppercase text-muted mb-2">Created</h6>
                <p class="mb-0">
                    <small class="text-muted">{{ $group->created_at?->format('M d, Y g:i A') }}</small>
                </p>
            </div>
        </div>
    </div>

    <!-- Members by Role -->
    <hr class="my-4">
    <h6 class="text-uppercase text-muted mb-3">Members by Role</h6>
    <div class="row">
        @php
            $coordinators = $group->users()->where('role', 'coordinator')->count();
            $assistants = $group->users()->where('role', 'assistant')->count();
            $members = $group->users()->where('role', 'member')->count();
        @endphp

        <div class="col-md-4">
            <div class="card border-0 bg-light">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase mb-2">Coordinators</h6>
                    <h4 class="mb-0">{{ $coordinators }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 bg-light">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase mb-2">Assistants</h6>
                    <h4 class="mb-0">{{ $assistants }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 bg-light">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase mb-2">Members</h6>
                    <h4 class="mb-0">{{ $members }}</h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Courses at a Glance -->
    @if ($group->courses()->count() > 0)
        <hr class="my-4">
        <h6 class="text-uppercase text-muted mb-3">Assigned Courses</h6>
        <div class="list-group list-group-sm">
            @foreach ($group->courses()->limit(5)->get() as $course)
                <div class="list-group-item px-0 py-2 border-bottom-1">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>{{ $course->code }}</strong><br>
                            <small class="text-muted">{{ $course->name }}</small>
                        </div>
                        <span class="badge bg-secondary">{{ $course->credit_hours ?? 3 }} credits</span>
                    </div>
                </div>
            @endforeach
        </div>
        @if ($group->courses()->count() > 5)
            <p class="text-muted mt-2 mb-0">
                <small>+ {{ $group->courses()->count() - 5 }} more courses. See Courses tab for full list.</small>
            </p>
        @endif
    @endif
</div>
