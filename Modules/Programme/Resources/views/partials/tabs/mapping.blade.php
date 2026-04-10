{{-- CLO-PLO Mapping Tab --}}
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">CLO to PLO Mapping</h5>
            @can('update', $programme)
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addMappingModal">
                    <i class="bi bi-plus-lg me-1"></i>Add Mapping
                </button>
            @endcan
        </div>

        <div class="alert alert-info mb-3">
            <i class="bi bi-info-circle me-2"></i>
            Map Course Learning Outcomes (CLOs) to Programme Learning Outcomes (PLOs) using Bloom's Taxonomy levels.
        </div>

        @if ($programme->courses->isNotEmpty() && $programme->programmePLOs->isNotEmpty())
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th>Course</th>
                            <th>CLO Code</th>
                            <th>PLO</th>
                            <th style="width: 120px;">Bloom Level</th>
                            <th style="width: 100px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $mappings = \Modules\Programme\Models\CLOPLOMapping::whereIn(
                                'course_id',
                                $programme->courses()->pluck('id'),
                            )
                                ->with(['course:id,code,name', 'programmePLO:id,code,description'])
                                ->orderBy(function ($q) {
                                    $q->selectRaw('courses.code')
                                        ->from('courses')
                                        ->whereColumn('courses.id', 'clo_plo_mappings.course_id');
                                })
                                ->orderBy('clo_code')
                                ->get();
                        @endphp

                        @if ($mappings->isNotEmpty())
                            @foreach ($mappings as $mapping)
                                <tr>
                                    <td>
                                        <small>
                                            <span class="badge bg-light text-dark">{{ $mapping->course->code }}</span>
                                            {{ $mapping->course->name }}
                                        </small>
                                    </td>
                                    <td><code>{{ $mapping->clo_code }}</code></td>
                                    <td>
                                        <small>
                                            <span
                                                class="badge bg-light text-dark">{{ $mapping->programmePLO->code }}</span>
                                        </small>
                                    </td>
                                    <td>
                                        <small>
                                            <span class="badge bg-secondary">{{ $mapping->getBloomLevelLabel() }}</span>
                                        </small>
                                    </td>
                                    <td>
                                        @can('update', $programme)
                                            <button class="btn btn-outline-danger btn-sm"
                                                onclick="deleteMapping({{ $mapping->id }})">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    No mappings created yet
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        @else
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle me-2"></i>
                You need to have at least one course and one PLO to create mappings.
            </div>
        @endif
    </div>
</div>

<!-- Add Mapping Modal -->
<div class="modal fade" id="addMappingModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add CLO-PLO Mapping</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="mappingForm" onsubmit="saveMapping(event)">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="map_course" class="form-label">Course</label>
                        <select class="form-select" id="map_course" name="course_id" required>
                            <option value="">Select Course</option>
                            @foreach ($programme->courses->sortBy('code') as $course)
                                <option value="{{ $course->id }}">{{ $course->code }} - {{ $course->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="map_plo" class="form-label">PLO</label>
                        <select class="form-select" id="map_plo" name="programme_plo_id" required>
                            <option value="">Select PLO</option>
                            @foreach ($programme->programmePLOs->sortBy('sequence_order') as $plo)
                                <option value="{{ $plo->id }}">{{ $plo->code }} -
                                    {{ substr($plo->description, 0, 50) }}...</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="map_clo" class="form-label">CLO Code</label>
                        <input type="text" class="form-control" id="map_clo" name="clo_code"
                            placeholder="e.g., CLO1" required>
                    </div>
                    <div class="mb-3">
                        <label for="map_bloom" class="form-label">Bloom Level</label>
                        <select class="form-select" id="map_bloom" name="bloom_level" required>
                            <option value="">Select Level</option>
                            <option value="1">1 - Remember</option>
                            <option value="2">2 - Understand</option>
                            <option value="3">3 - Apply</option>
                            <option value="4">4 - Analyze</option>
                            <option value="5">5 - Evaluate</option>
                            <option value="6">6 - Create</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="map_notes" class="form-label">Alignment Notes</label>
                        <textarea class="form-control" id="map_notes" name="alignment_notes" rows="2"
                            placeholder="Optional notes about this mapping"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Mapping</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function saveMapping(e) {
        e.preventDefault();
        const data = {
            course_id: document.getElementById('map_course').value,
            programme_plo_id: document.getElementById('map_plo').value,
            clo_code: document.getElementById('map_clo').value,
            bloom_level: document.getElementById('map_bloom').value,
            alignment_notes: document.getElementById('map_notes').value,
        };

        fetch('/programmes/mappings', {
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
            })
            .catch(e => alert('Error: ' + e.message));
    }

    function deleteMapping(id) {
        if (confirm('Delete this mapping?')) {
            fetch(`/programmes/mappings/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(r => r.json())
                .then(d => {
                    if (d.success) {
                        alert(d.message);
                        location.reload();
                    }
                });
        }
    }

    function filterCourseMapping(courseId) {
        // Could be enhanced to filter the mapping table by course
        console.log('Filter mappings for course:', courseId);
    }
</script>
