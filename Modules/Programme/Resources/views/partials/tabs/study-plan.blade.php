{{-- Study Plan Tab --}}
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Study Plans</h5>
            @can('update', $programme)
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addStudyPlanModal">
                    <i class="bi bi-plus-lg me-1"></i>Create Study Plan
                </button>
            @endcan
        </div>

        @if ($programme->studyPlans->isNotEmpty())
            <div class="row g-3">
                @foreach ($programme->studyPlans->sortBy('name') as $plan)
                    <div class="col-md-6 col-lg-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <h6 class="card-title">{{ $plan->name }}</h6>
                                <p class="card-text text-muted small">{{ $plan->description }}</p>

                                <ul class="list-unstyled small">
                                    <li><strong>Years:</strong> {{ $plan->total_years }}</li>
                                    <li><strong>Semesters/Year:</strong> {{ $plan->semesters_per_year }}</li>
                                    <li><strong>Total Semesters:</strong>
                                        {{ $plan->total_years * $plan->semesters_per_year }}</li>
                                    <li><strong>Courses:</strong> {{ $plan->courses()->count() }}</li>
                                </ul>

                                <div class="mt-3">
                                    @if ($plan->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </div>
                            </div>
                            <div class="card-footer bg-white border-top">
                                <button class="btn btn-sm btn-outline-primary w-100"
                                    onclick="viewStudyPlanCourses({{ $plan->id }})">
                                    View Courses
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-inbox text-muted" style="font-size: 2rem;"></i>
                <p class="text-muted mt-2 mb-0">No study plans created for this programme</p>
            </div>
        @endif
    </div>
</div>

<!-- Add Study Plan Modal -->
<div class="modal fade" id="addStudyPlanModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Study Plan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="studyPlanForm" onsubmit="saveStudyPlan(event)">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="sp_name" class="form-label">Study Plan Name</label>
                        <input type="text" class="form-control" id="sp_name" name="name"
                            placeholder="e.g., Standard 4-Year Plan" required>
                    </div>
                    <div class="mb-3">
                        <label for="sp_desc" class="form-label">Description</label>
                        <textarea class="form-control" id="sp_desc" name="description" rows="2"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="sp_years" class="form-label">Total Years</label>
                                <input type="number" class="form-control" id="sp_years" name="total_years"
                                    min="1" value="4" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="sp_semesters" class="form-label">Semesters per Year</label>
                                <input type="number" class="form-control" id="sp_semesters" name="semesters_per_year"
                                    min="1" max="4" value="2" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Study Plan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function saveStudyPlan(e) {
        e.preventDefault();
        const data = {
            programme_id: {{ $programme->id }},
            name: document.getElementById('sp_name').value,
            description: document.getElementById('sp_desc').value,
            total_years: document.getElementById('sp_years').value,
            semesters_per_year: document.getElementById('sp_semesters').value,
            is_active: true,
        };

        fetch(`/programmes/{{ $programme->id }}/study-plans`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(r => r.json())
            .then(d => {
                if (d.success) {
                    alert(d.message);
                    location.reload();
                }
            });
    }

    function viewStudyPlanCourses(planId) {
        fetch(`/programmes/study-plans/${planId}/courses`)
            .then(r => r.json())
            .then(d => {
                console.log('Study Plan Courses:', d.data);
                alert('Study Plan has ' + Object.keys(d.data).length + ' semesters. Check console for details.');
            });
    }
</script>
