{{-- Programme Index --}}
@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4 py-6">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h2 mb-0">Programmes</h1>
                <p class="text-muted mb-0">Manage academic programmes</p>
            </div>
            <div>
                @can('programme.create')
                    <a href="{{ route('programmes.create') }}" class="btn btn-primary btn-lg">
                        <i class="bi bi-plus-lg me-2"></i>Create Programme
                    </a>
                @endcan
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase mb-2">Total Programmes</h6>
                        <h3 class="mb-0">{{ $programmes->count() }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase mb-2">Active</h6>
                        <h3 class="mb-0">{{ $programmes->where('is_active', true)->count() }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase mb-2">Approved</h6>
                        <h3 class="mb-0">{{ $programmes->where('status', 'approved')->count() }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase mb-2">In Review</h6>
                        <h3 class="mb-0">{{ $programmes->where('status', 'in_review')->count() }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Programmes Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                @if ($programmes->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Level</th>
                                    <th>Duration</th>
                                    <th>Status</th>
                                    <th>Courses</th>
                                    <th>PLOs</th>
                                    <th>Chair</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($programmes as $programme)
                                    <tr>
                                        <td>
                                            <span class="badge bg-light text-dark">{{ $programme->code }}</span>
                                        </td>
                                        <td>
                                            <strong>{{ $programme->name }}</strong>
                                        </td>
                                        <td>{{ $programme->level }}</td>
                                        <td>{{ $programme->duration_semesters }} semesters</td>
                                        <td>
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
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $programme->courses_count ?? 0 }}</span>
                                        </td>
                                        <td>
                                            <span
                                                class="badge bg-secondary">{{ $programme->programme_p_l_os_count ?? 0 }}</span>
                                        </td>
                                        <td>
                                            @if ($programme->programmeChair)
                                                <small class="text-muted">{{ $programme->programmeChair->name }}</small>
                                            @else
                                                <small class="text-muted text-danger">Not assigned</small>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('programmes.show', $programme) }}"
                                                    class="btn btn-outline-primary" title="View">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                @can('update', $programme)
                                                    <a href="{{ route('programmes.edit', $programme) }}"
                                                        class="btn btn-outline-warning" title="Edit">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                @endcan
                                                @can('delete', $programme)
                                                    <form action="{{ route('programmes.destroy', $programme) }}" method="POST"
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
                        <p class="text-muted mt-3 mb-0">No programmes found</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
