{{-- Edit Programme Form --}}
@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4 py-6">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <!-- Header -->
                <div class="mb-4">
                    <h1 class="h2 mb-0">Edit Programme</h1>
                    <p class="text-muted mb-0">Update programme information</p>
                </div>

                <!-- Form Card -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <form action="{{ route('programmes.update', $programme) }}" method="POST">
                            @csrf @method('PUT')

                            <!-- Code -->
                            <div class="mb-3">
                                <label for="code" class="form-label">Programme Code <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('code') is-invalid @enderror"
                                    id="code" name="code" value="{{ old('code', $programme->code) }}"
                                    placeholder="e.g., CS101" required>
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Name -->
                            <div class="mb-3">
                                <label for="name" class="form-label">Programme Name <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                    id="name" name="name" value="{{ old('name', $programme->name) }}"
                                    placeholder="e.g., Bachelor of Computer Science" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Level -->
                            <div class="mb-3">
                                <label for="level" class="form-label">Level <span class="text-danger">*</span></label>
                                <select class="form-select @error('level') is-invalid @enderror" id="level"
                                    name="level" required>
                                    <option value="">Select Level</option>
                                    <option value="Diploma" @selected(old('level', $programme->level) === 'Diploma')>Diploma</option>
                                    <option value="Bachelor" @selected(old('level', $programme->level) === 'Bachelor')>Bachelor</option>
                                    <option value="Master" @selected(old('level', $programme->level) === 'Master')>Master</option>
                                    <option value="PhD" @selected(old('level', $programme->level) === 'PhD')>PhD</option>
                                </select>
                                @error('level')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Description -->
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                                    rows="3" placeholder="Brief description of the programme">{{ old('description', $programme->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Accreditation Body -->
                            <div class="mb-3">
                                <label for="accreditation_body" class="form-label">Accreditation Body</label>
                                <input type="text" class="form-control @error('accreditation_body') is-invalid @enderror"
                                    id="accreditation_body" name="accreditation_body"
                                    value="{{ old('accreditation_body', $programme->accreditation_body) }}"
                                    placeholder="e.g., Malaysian Qualifications Agency">
                                @error('accreditation_body')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Duration -->
                            <div class="mb-3">
                                <label for="duration_semesters" class="form-label">Duration (Semesters) <span
                                        class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('duration_semesters') is-invalid @enderror"
                                    id="duration_semesters" name="duration_semesters"
                                    value="{{ old('duration_semesters', $programme->duration_semesters) }}" min="1"
                                    max="20" required>
                                @error('duration_semesters')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Programme Chair -->
                            <div class="mb-3">
                                <label for="programme_chair_id" class="form-label">Programme Chair</label>
                                <select class="form-select @error('programme_chair_id') is-invalid @enderror"
                                    id="programme_chair_id" name="programme_chair_id">
                                    <option value="">Select Chair</option>
                                    @foreach ($chairs as $chair)
                                        <option value="{{ $chair->id }}" @selected(old('programme_chair_id', $programme->programme_chair_id) === $chair->id)>
                                            {{ $chair->name }} ({{ $chair->email }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('programme_chair_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Active Status -->
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                                        value="1" @checked(old('is_active', $programme->is_active))>
                                    <label class="form-check-label" for="is_active">
                                        Active
                                    </label>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="d-grid gap-2 d-sm-flex justify-content-sm-end">
                                <a href="{{ route('programmes.show', $programme) }}"
                                    class="btn btn-outline-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-lg me-2"></i>Update Programme
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
