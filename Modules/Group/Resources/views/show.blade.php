{{-- Group Show/Detail --}}
@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4 py-6">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h2 mb-0">{{ $group->name }}</h1>
                <p class="text-muted mb-0">{{ $group->programme->code ?? 'N/A' }} • Intake {{ $group->intake_year }} •
                    Semester {{ $group->semester }}</p>
            </div>
            <div>
                @can('update', $group)
                    <a href="{{ route('groups.edit', $group) }}" class="btn btn-warning me-2">
                        <i class="bi bi-pencil me-2"></i>Edit
                    </a>
                @endcan
                @can('delete', $group)
                    <form action="{{ route('groups.destroy', $group) }}" method="POST" style="display: inline;">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure?')">
                            <i class="bi bi-trash me-2"></i>Delete
                        </button>
                    </form>
                @endcan
            </div>
        </div>

        <!-- Statistics Row -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase mb-2">Total Members</h6>
                        <h3 class="mb-0">{{ $group->users()->count() }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase mb-2">Assigned Courses</h6>
                        <h3 class="mb-0">{{ $group->courses()->count() }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase mb-2">Coordinators</h6>
                        <h3 class="mb-0">{{ $group->users()->where('role', 'coordinator')->count() }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase mb-2">Status</h6>
                        <h3 class="mb-0">
                            @if ($group->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs Card -->
        <div class="card border-0 shadow-sm">
            <!-- Tab Navigation -->
            <ul class="nav nav-tabs border-0" role="tablist" style="background-color: #f8f9fa; border-radius: 8px 8px 0 0;">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="info-tab" data-bs-toggle="tab" data-bs-target="#info"
                        type="button" role="tab" aria-controls="info" aria-selected="true">
                        <i class="bi bi-info-circle me-2"></i>Basic Info
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="courses-tab" data-bs-toggle="tab" data-bs-target="#courses" type="button"
                        role="tab" aria-controls="courses" aria-selected="false">
                        <i class="bi bi-book me-2"></i>Courses
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="users-tab" data-bs-toggle="tab" data-bs-target="#users" type="button"
                        role="tab" aria-controls="users" aria-selected="false">
                        <i class="bi bi-people me-2"></i>Members
                    </button>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content">
                <!-- Info Tab -->
                <div class="tab-pane fade show active" id="info" role="tabpanel" aria-labelledby="info-tab">
                    @include('group::partials.tabs.info', ['group' => $group])
                </div>

                <!-- Courses Tab -->
                <div class="tab-pane fade" id="courses" role="tabpanel" aria-labelledby="courses-tab">
                    @can('update', $group)
                        @include('group::partials.tabs.courses', [
                            'group' => $group,
                            'availableCourses' => $availableCourses,
                            'assignedCourses' => $assignedCourses,
                        ])
                    @else
                        <div class="p-4">
                            <div class="alert alert-warning mb-0">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                You don't have permission to manage courses for this group.
                            </div>
                        </div>
                    @endcan
                </div>

                <!-- Users Tab -->
                <div class="tab-pane fade" id="users" role="tabpanel" aria-labelledby="users-tab">
                    @can('update', $group)
                        @include('group::partials.tabs.users', [
                            'group' => $group,
                            'availableUsers' => $availableUsers,
                            'assignedUsers' => $assignedUsers,
                        ])
                    @else
                        <div class="p-4">
                            <div class="alert alert-warning mb-0">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                You don't have permission to manage members for this group.
                            </div>
                        </div>
                    @endcan
                </div>
            </div>
        </div>
    </div>
@endsection
