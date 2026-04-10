{{-- Group Index --}}
@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4 py-6">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h2 mb-0">Groups</h1>
                <p class="text-muted mb-0">Manage academic groups and cohorts</p>
            </div>
            <div>
                @can('create', Modules\Group\Models\AcademicGroup::class)
                    <a href="{{ route('groups.create') }}" class="btn btn-primary btn-lg">
                        <i class="bi bi-plus-lg me-2"></i>Create Group
                    </a>
                @endcan
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase mb-2">Total Groups</h6>
                        <h3 class="mb-0">{{ $groups->count() }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase mb-2">Active</h6>
                        <h3 class="mb-0">{{ $groups->where('is_active', true)->count() }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase mb-2">Total Members</h6>
                        <h3 class="mb-0">{{ $groups->sum(fn($g) => $g->users()->count()) }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Groups Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                @if ($groups->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Programme</th>
                                    <th>Intake Year</th>
                                    <th>Semester</th>
                                    <th>Coordinator</th>
                                    <th>Members</th>
                                    <th>Courses</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($groups as $group)
                                    <tr>
                                        <td><strong>{{ $group->name }}</strong></td>
                                        <td>
                                            <span
                                                class="badge bg-light text-dark">{{ $group->programme->code ?? 'N/A' }}</span>
                                        </td>
                                        <td>{{ $group->intake_year }}</td>
                                        <td>{{ $group->semester }}</td>
                                        <td>
                                            @if ($group->coordinator)
                                                <small>{{ $group->coordinator->name }}</small>
                                            @else
                                                <small class="text-muted">-</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $group->users()->count() }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $group->courses()->count() }}</span>
                                        </td>
                                        <td>
                                            @if ($group->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('groups.show', $group) }}"
                                                    class="btn btn-outline-primary" title="View">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                @can('update', $group)
                                                    <a href="{{ route('groups.edit', $group) }}"
                                                        class="btn btn-outline-warning" title="Edit">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                @endcan
                                                @can('delete', $group)
                                                    <form action="{{ route('groups.destroy', $group) }}" method="POST"
                                                        style="display:inline;">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger btn-sm"
                                                            title="Delete" onclick="return confirm('Are you sure?')">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-3 mb-0">No groups found</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
