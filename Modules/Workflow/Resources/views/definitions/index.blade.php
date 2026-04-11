@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4 py-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <div>
                <h1 class="h3 mb-1">Workflow Definitions</h1>
                <p class="text-muted mb-0">Admin management for dynamic workflow setup.</p>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row g-4">
            {{-- Settings panel --}}
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">Default Template Versions</h6>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">
                            These versions are used when a submission does not specify a template version.
                            Override the defaults set in <code>config/workflow.php</code>.
                        </p>
                        <form method="POST" action="{{ route('workflows.manage.settings.save') }}"
                            class="row g-3 align-items-end">
                            @csrf
                            <div class="col-sm-4">
                                <label class="form-label fw-semibold mb-1">Course Default Version</label>
                                <input type="number" name="course_default_version" min="1" max="10"
                                    class="form-control @error('course_default_version') is-invalid @enderror"
                                    value="{{ old('course_default_version', $settings['course_default_version']) }}">
                                @error('course_default_version')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-sm-4">
                                <label class="form-label fw-semibold mb-1">Programme Default Version</label>
                                <input type="number" name="programme_default_version" min="1" max="10"
                                    class="form-control @error('programme_default_version') is-invalid @enderror"
                                    value="{{ old('programme_default_version', $settings['programme_default_version']) }}">
                                @error('programme_default_version')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-sm-4">
                                <button type="submit" class="btn btn-primary w-100">Save Settings</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Existing Definitions</h6>
                        <form method="GET" action="{{ route('workflows.manage.definitions') }}" class="d-flex gap-2">
                            <select name="entity_type" class="form-select form-select-sm">
                                <option value="">All</option>
                                <option value="course" @selected($entityType === 'course')>Course</option>
                                <option value="programme" @selected($entityType === 'programme')>Programme</option>
                            </select>
                            <button type="submit" class="btn btn-sm btn-outline-secondary">Filter</button>
                        </form>
                    </div>
                    <div class="card-body">
                        @forelse ($definitions as $definition)
                            <div class="border rounded-3 p-3 mb-3">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                                    <h6 class="mb-0">{{ $definition->name }}</h6>
                                    <span class="badge bg-primary">{{ ucfirst($definition->entity_type) }}</span>
                                </div>
                                @if ($definition->description)
                                    <p class="text-muted mb-2">{{ $definition->description }}</p>
                                @endif
                                <div class="small text-muted mb-2">Steps: {{ $definition->steps->count() }}</div>
                                <ul class="list-group list-group-flush">
                                    @foreach ($definition->steps as $step)
                                        <li class="list-group-item px-0 py-2">
                                            <div class="fw-semibold">{{ $step->step_number }}. {{ $step->title }}</div>
                                            <div class="small text-muted">
                                                Roles: {{ implode(', ', $step->roles_required ?? []) }}
                                                | Level: {{ $step->approval_level }}
                                                | Action: {{ $step->action_type }}
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @empty
                            <p class="text-muted mb-0">No workflow definitions found.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">Create Definition</h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('workflows.manage.definitions.store') }}"
                            id="workflowDefinitionForm">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Name</label>
                                <input type="text" name="name" class="form-control" value="{{ old('name') }}"
                                    required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Entity Type</label>
                                <select name="entity_type" class="form-select" required>
                                    <option value="course" @selected(old('entity_type') === 'course')>Course</option>
                                    <option value="programme" @selected(old('entity_type') === 'programme')>Programme</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
                            </div>

                            <hr>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0">Steps</h6>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addStep()">Add
                                    Step</button>
                            </div>
                            <div id="stepsContainer"></div>

                            <button type="submit" class="btn btn-primary w-100 mt-3">Create Workflow Definition</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <template id="stepTemplate">
        <div class="border rounded-3 p-3 mb-3 step-item">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0 step-title">Step</h6>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeStep(this)">Remove</button>
            </div>
            <div class="mb-2">
                <label class="form-label">Title</label>
                <input type="text" class="form-control" data-field="title" required>
            </div>
            <div class="mb-2">
                <label class="form-label">Description</label>
                <textarea class="form-control" data-field="description" rows="2"></textarea>
            </div>
            <div class="mb-2">
                <label class="form-label">Roles (comma-separated)</label>
                <input type="text" class="form-control" data-field="roles_required_text"
                    placeholder="Reviewer, Approver" required>
            </div>
            <div class="row g-2">
                <div class="col-md-6">
                    <label class="form-label">Approval Level</label>
                    <input type="number" min="1" class="form-control" data-field="approval_level"
                        value="1" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Action Type</label>
                    <select class="form-select" data-field="action_type">
                        <option value="approve">Approve</option>
                        <option value="review">Review</option>
                        <option value="clarification">Clarification</option>
                    </select>
                </div>
            </div>
            <div class="form-check mt-2">
                <input class="form-check-input" type="checkbox" data-field="allow_rejection" checked>
                <label class="form-check-label">Allow Rejection</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" data-field="requires_comment">
                <label class="form-check-label">Requires Comment</label>
            </div>
        </div>
    </template>

    <script>
        const stepsContainer = document.getElementById('stepsContainer');
        const template = document.getElementById('stepTemplate');

        function addStep(defaults = null) {
            const clone = template.content.cloneNode(true);
            stepsContainer.appendChild(clone);
            refreshStepIndexes();

            if (defaults) {
                const item = stepsContainer.lastElementChild;
                item.querySelector('[data-field="title"]').value = defaults.title ?? '';
                item.querySelector('[data-field="description"]').value = defaults.description ?? '';
                item.querySelector('[data-field="roles_required_text"]').value = defaults.roles_required ?? '';
                item.querySelector('[data-field="approval_level"]').value = defaults.approval_level ?? 1;
                item.querySelector('[data-field="action_type"]').value = defaults.action_type ?? 'approve';
                item.querySelector('[data-field="allow_rejection"]').checked = !!defaults.allow_rejection;
                item.querySelector('[data-field="requires_comment"]').checked = !!defaults.requires_comment;
            }
        }

        function removeStep(button) {
            button.closest('.step-item').remove();
            refreshStepIndexes();
        }

        function refreshStepIndexes() {
            [...stepsContainer.querySelectorAll('.step-item')].forEach((item, index) => {
                item.querySelector('.step-title').textContent = `Step ${index + 1}`;
                item.querySelectorAll('[data-field]').forEach((field) => {
                    const key = field.dataset.field;

                    if (key === 'roles_required_text') {
                        field.removeAttribute('name');
                        return;
                    }

                    if (field.type === 'checkbox') {
                        const hiddenName = `steps[${index}][${key}]`;
                        let hidden = item.querySelector(`input[type="hidden"][data-hidden="${key}"]`);

                        if (!hidden) {
                            hidden = document.createElement('input');
                            hidden.type = 'hidden';
                            hidden.dataset.hidden = key;
                            item.appendChild(hidden);
                        }

                        hidden.name = hiddenName;
                        hidden.value = field.checked ? '1' : '0';
                        field.name = '';
                    } else {
                        field.name = `steps[${index}][${key}]`;
                    }
                });

                const rolesInput = item.querySelector('[data-field="roles_required_text"]');
                rolesInput.addEventListener('input', () => syncRoles(index, item), {
                    once: true
                });
                syncRoles(index, item);
            });
        }

        function syncRoles(index, item) {
            item.querySelectorAll('input[data-role-hidden="1"]').forEach((el) => el.remove());
            const raw = item.querySelector('[data-field="roles_required_text"]').value || '';
            const roles = raw.split(',').map((role) => role.trim()).filter(Boolean);

            roles.forEach((role, roleIndex) => {
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.dataset.roleHidden = '1';
                hidden.name = `steps[${index}][roles_required][${roleIndex}]`;
                hidden.value = role;
                item.appendChild(hidden);
            });

            item.querySelectorAll('[data-field="allow_rejection"], [data-field="requires_comment"]').forEach((checkbox) => {
                const key = checkbox.dataset.field;
                const hidden = item.querySelector(`input[type="hidden"][data-hidden="${key}"]`);
                if (hidden) hidden.value = checkbox.checked ? '1' : '0';
            });
        }

        document.getElementById('workflowDefinitionForm').addEventListener('submit', () => {
            refreshStepIndexes();
            [...stepsContainer.querySelectorAll('.step-item')].forEach((item, index) => syncRoles(index, item));
        });

        addStep({
            title: 'Reviewer Assessment',
            roles_required: 'Reviewer',
            approval_level: 1,
            action_type: 'review',
            allow_rejection: true
        });
    </script>
@endsection
