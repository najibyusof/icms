{{-- Courses Tab --}}
<div class="row">
    <div class="col-12">
        <h5 class="mb-3">Courses in This Programme</h5>

        @if($programme->courses->isNotEmpty())
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Credit Hours</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($programme->courses->sortBy('code') as $course)
                            <tr>
                                <td><span class="badge bg-light text-dark">{{ $course->code }}</span></td>
                                <td><strong>{{ $course->name }}</strong></td>
                                <td><span class="badge bg-secondary">{{ $course->credit_hours }}</span></td>
                                <td>
                                    @if($course->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="#mapping" class="btn btn-sm btn-outline-primary" data-bs-toggle="tab"
                                       onclick="filterCourseMapping({{ $course->id }})">
                                        View Mappings
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-inbox text-muted" style="font-size: 2rem;"></i>
                <p class="text-muted mt-2 mb-0">No courses linked to this programme</p>
            </div>
        @endif
    </div>
</div>
