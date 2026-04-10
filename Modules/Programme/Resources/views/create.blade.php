{{-- Create Programme Form --}}
@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-6">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <!-- Header -->
            <div class="mb-4">
                <h1 class="h2 mb-0">Create New Programme</h1>
                <p class="text-muted mb-0">Fill in the basic information below</p>
            </div>

            <!-- Form Card -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form action="{{ route('programmes.store') }}" method="POST">
                        @csrf

                        <!-- Code -->
                        <div class="mb-3">
                            <label for="code" class="form-label">Programme Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('code') is-invalid @enderror" 
                                   id="code" name="code" value="{{ old('code') }}" 
                                   placeholder="e.g., CS101" required>
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label">Programme Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name') }}" 
                                   placeholder="e.g., Bachelor of Computer Science" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Level -->
                        <div class="mb-3">
                            <label for="level" class="form-label">Level <span class="text-danger">*</span></label>
                            <select class="form-select @error('level') is-invalid @enderror" 
                                    id="level" name="level" required>
                                <option value="">Select Level</option>
                                <option value="Diploma" @selected(old('level') === 'Diploma')>Diploma</option>
                                <option value="Bachelor" @selected(old('level') === 'Bachelor')>Bachelor</option>
                                <option value="Master" @selected(old('level') === 'Master')>Master</option>
                                <option value="PhD" @selected(old('level') === 'PhD')>PhD</option>
                            </select>
                            @error('level')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3" 
                                      placeholder="Brief description of the programme">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Accreditation Body -->
                        <div class="mb-3">
                            <label for="accreditation_body" class="form-label">Accreditation Body</label>
                            <input type="text" class="form-control @error('accreditation_body') is-invalid @enderror" 
                                   id="accreditation_body" name="accreditation_body" value="{{ old('accreditation_body') }}" 
                                   placeholder="e.g., Malaysian Qualifications Agency">
                            @error('accreditation_body')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Duration -->
                        <div class="mb-3">
                            <label for="duration_semesters" class="form-label">Duration (Semesters) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('duration_semesters') is-invalid @enderror" 
                                   id="duration_semesters" name="duration_semesters" value="{{ old('duration_semesters') }}" 
                                   min="1" max="20" placeholder="e.g., 8" required>
                            @error('duration_semesters')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid gap-2 d-sm-flex justify-content-sm-end">
                            <a href="{{ route('programmes.index') }}" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-2"></i>Create Programme
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
