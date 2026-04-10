{{-- Create Group --}}
@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4 py-6">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <!-- Header -->
                <div class="mb-4">
                    <h1 class="h2 mb-0">Create New Group</h1>
                    <p class="text-muted mb-0">Define a new academic group or cohort</p>
                </div>

                <!-- Form Card -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <form action="{{ route('groups.store') }}" method="POST">
                            @csrf

                            <!-- Programme -->
                            <div class="mb-3">
                                <label for="programme_id" class="form-label">Programme <span
                                        class="text-danger">*</span></label>
                                <select class="form-select @error('programme_id') is-invalid @enderror" id="programme_id"
                                    name="programme_id" required>
                                    <option value="">Select Programme</option>
                                    @foreach ($programmes as $prog)
                                        <option value="{{ $prog->id }}" @selected(old('programme_id') === $prog->id)>
                                            {{ $prog->code }} - {{ $prog->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('programme_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Group Name -->
                            <div class="mb-3">
                                <label for="name" class="form-label">Group Name <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                    id="name" name="name" value="{{ old('name') }}"
                                    placeholder="e.g., CS 2024 Batch A" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Intake Year -->
                            <div class="mb-3">
                                <label for="intake_year" class="form-label">Intake Year <span
                                        class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('intake_year') is-invalid @enderror"
                                    id="intake_year" name="intake_year" value="{{ old('intake_year', now()->year) }}"
                                    min="2000" max="2100" required>
                                @error('intake_year')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Semester -->
                            <div class="mb-3">
                                <label for="semester" class="form-label">Semester <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('semester') is-invalid @enderror"
                                    id="semester" name="semester" value="{{ old('semester', 1) }}" min="1"
                                    max="14" required>
                                @error('semester')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Coordinator -->
                            <div class="mb-3">
                                <label for="coordinator_id" class="form-label">Coordinator (Optional)</label>
                                <select class="form-select @error('coordinator_id') is-invalid @enderror"
                                    id="coordinator_id" name="coordinator_id">
                                    <option value="">Select Coordinator</option>
                                    @foreach (\App\Models\User::orderBy('name')->get() as $user)
                                        <option value="{{ $user->id }}" @selected(old('coordinator_id') === $user->id)>
                                            {{ $user->name }} ({{ $user->email }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('coordinator_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Active Status -->
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                                        value="1" @checked(old('is_active', true))>
                                    <label class="form-check-label" for="is_active">
                                        Active
                                    </label>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="d-grid gap-2 d-sm-flex justify-content-sm-end">
                                <a href="{{ route('groups.index') }}" class="btn btn-outline-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-lg me-2"></i>Create Group
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
