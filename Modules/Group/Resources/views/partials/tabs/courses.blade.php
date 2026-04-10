{{-- Courses Dual List Selector Tab Partial --}}
<div class="p-4">
    <div class="mb-4">
        <h6 class="text-muted text-uppercase mb-1">Manage Assigned Courses</h6>
        <p class="text-muted mb-0">Select courses to assign to this group. Drag between lists or use buttons to transfer.
        </p>
    </div>

    {{-- Hidden form to submit changes --}}
    <form id="coursesForm">
        @csrf @method('PUT')
        <input type="hidden" name="course_ids" id="courseIdsInput" value="">
    </form>

    <div class="row">
        {{-- Available Courses (Left) --}}
        <div class="col-md-6 mb-3">
            <div class="card border-0 bg-light">
                <div class="card-header bg-white border-bottom-1 py-3">
                    <h6 class="mb-0 text-uppercase">
                        <i class="bi bi-list me-2"></i>Available Courses
                        <span class="badge bg-secondary float-end">{{ count($availableCourses) }}</span>
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div id="availableCoursesContainer" class="list-group list-group-flush dual-list-selector"
                        style="max-height: 500px; overflow-y: auto;">
                        @forelse($availableCourses as $course)
                            <div class="dual-item list-group-item d-flex align-items-center px-3 py-2 border-bottom-1"
                                draggable="true" data-course-id="{{ $course->id }}">
                                <div class="flex-grow-1">
                                    <strong>{{ $course->code }}</strong>
                                    <div class="small text-muted">{{ $course->name }}</div>
                                </div>
                                <button type="button" class="btn btn-sm btn-primary transfer-btn rounded-circle p-0"
                                    style="width: 32px; height: 32px;" title="Add Course">
                                    <i class="bi bi-chevron-right"></i>
                                </button>
                            </div>
                        @empty
                            <div class="p-3 text-center text-muted">
                                <i class="bi bi-inbox" style="font-size: 1.5rem;"></i>
                                <p class="mt-2 mb-0">All courses assigned</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- Assigned Courses (Right) --}}
        <div class="col-md-6 mb-3">
            <div class="card border-0 bg-light">
                <div class="card-header bg-white border-bottom-1 py-3">
                    <h6 class="mb-0 text-uppercase">
                        <i class="bi bi-check-circle me-2"></i>Assigned Courses
                        <span class="badge bg-success float-end">{{ count($assignedCourses) }}</span>
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div id="assignedCoursesContainer" class="list-group list-group-flush dual-list-selector"
                        style="max-height: 500px; overflow-y: auto;">
                        @forelse($assignedCourses as $course)
                            <div class="dual-item list-group-item d-flex align-items-center px-3 py-2 border-bottom-1"
                                draggable="true" data-course-id="{{ $course->id }}">
                                <div class="flex-grow-1">
                                    <strong>{{ $course->code }}</strong>
                                    <div class="small text-muted">{{ $course->name }}</div>
                                </div>
                                <button type="button"
                                    class="btn btn-sm btn-outline-danger transfer-btn rounded-circle p-0"
                                    style="width: 32px; height: 32px;" title="Remove Course">
                                    <i class="bi bi-chevron-left"></i>
                                </button>
                            </div>
                        @empty
                            <div class="p-3 text-center text-muted">
                                <i class="bi bi-inbox" style="font-size: 1.5rem;"></i>
                                <p class="mt-2 mb-0">No courses assigned</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="d-flex gap-2 justify-content-end mt-4">
        <a href="{{ route('groups.show', $group) }}" class="btn btn-outline-secondary">Cancel</a>
        <button type="button" class="btn btn-primary" id="submitCoursesBtn">
            <i class="bi bi-check-lg me-2"></i>Save Changes
        </button>
    </div>
</div>

{{-- Dual List Selector Script --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const availableContainer = document.getElementById('availableCoursesContainer');
        const assignedContainer = document.getElementById('assignedCoursesContainer');
        const courseIdsInput = document.getElementById('courseIdsInput');
        const submitBtn = document.getElementById('submitCoursesBtn');

        // Drag and Drop Setup
        setupDragAndDrop();

        // Transfer Button Click Handlers
        document.querySelectorAll('.transfer-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const item = this.closest('.dual-item');
                const container = item.closest('.dual-list-selector');

                if (container === availableContainer) {
                    transferItem(item, availableContainer, assignedContainer);
                } else {
                    transferItem(item, assignedContainer, availableContainer);
                }
            });
        });

        // Submit Handler with AJAX
        submitBtn.addEventListener('click', function() {
            updateCourseIds();

            const formData = new FormData(document.getElementById('coursesForm'));
            const courseIds = document.getElementById('courseIdsInput').value;

            // Disable button while processing
            submitBtn.disabled = true;
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML =
            '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';

            fetch('{{ route('groups.updateCourses', $group) }}', {
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        course_ids: JSON.parse(courseIds)
                    })
                })
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    // Show success message
                    const alert = document.createElement('div');
                    alert.className = 'alert alert-success alert-dismissible fade show';
                    alert.innerHTML = `
                <i class="bi bi-check-circle me-2"></i>
                ${data.message || 'Courses updated successfully!'}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
                    document.querySelector('.p-4').insertBefore(alert, document.querySelector(
                        '.mb-4'));

                    // Auto-dismiss after 3s
                    setTimeout(() => alert.remove(), 3000);
                })
                .catch(error => {
                    console.error('Error:', error);
                    const alert = document.createElement('div');
                    alert.className = 'alert alert-danger alert-dismissible fade show';
                    alert.innerHTML = `
                <i class="bi bi-exclamation-circle me-2"></i>
                Failed to update courses. Please try again.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
                    document.querySelector('.p-4').insertBefore(alert, document.querySelector(
                        '.mb-4'));
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                });
        });

        function setupDragAndDrop() {
            document.querySelectorAll('.dual-item').forEach(item => {
                item.addEventListener('dragstart', function(e) {
                    e.dataTransfer.effectAllowed = 'move';
                    e.dataTransfer.setData('text/html', this.innerHTML);
                    this.classList.add('opacity-50');
                });

                item.addEventListener('dragend', function() {
                    this.classList.remove('opacity-50');
                });
            });

            [availableContainer, assignedContainer].forEach(container => {
                container.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    e.dataTransfer.dropEffect = 'move';
                    this.style.backgroundColor = 'rgba(0,123,255,0.1)';
                });

                container.addEventListener('dragleave', function() {
                    this.style.backgroundColor = '';
                });

                container.addEventListener('drop', function(e) {
                    e.preventDefault();
                    this.style.backgroundColor = '';

                    const draggedItem = document.querySelector('.dual-item.opacity-50');
                    if (draggedItem && draggedItem.closest('.dual-list-selector') !== this) {
                        this.appendChild(draggedItem);
                        setupTransferButtons();
                    }
                });
            });
        }

        function transferItem(item, fromContainer, toContainer) {
            item.style.opacity = '0';
            setTimeout(() => {
                toContainer.appendChild(item);
                item.style.opacity = '1';
                setupTransferButtons();
            }, 150);
        }

        function setupTransferButtons() {
            // Update button styles based on container
            document.querySelectorAll('.dual-item').forEach(item => {
                const btn = item.querySelector('.transfer-btn');
                const container = item.closest('.dual-list-selector');

                if (container === availableContainer) {
                    btn.className = 'btn btn-sm btn-primary transfer-btn rounded-circle p-0';
                    btn.style.width = '32px';
                    btn.style.height = '32px';
                    btn.innerHTML = '<i class="bi bi-chevron-right"></i>';
                    btn.title = 'Add Course';
                } else {
                    btn.className = 'btn btn-sm btn-outline-danger transfer-btn rounded-circle p-0';
                    btn.style.width = '32px';
                    btn.style.height = '32px';
                    btn.innerHTML = '<i class="bi bi-chevron-left"></i>';
                    btn.title = 'Remove Course';
                }

                // Rebind click handler
                btn.removeEventListener('click', handleTransferClick);
                btn.addEventListener('click', handleTransferClick);
            });
        }

        function handleTransferClick(e) {
            e.preventDefault();
            const item = this.closest('.dual-item');
            const container = item.closest('.dual-list-selector');

            if (container === availableContainer) {
                transferItem(item, availableContainer, assignedContainer);
            } else {
                transferItem(item, assignedContainer, availableContainer);
            }
        }

        function updateCourseIds() {
            const courseIds = [];
            document.querySelectorAll('#assignedCoursesContainer .dual-item').forEach(item => {
                courseIds.push(item.dataset.courseId);
            });
            courseIdsInput.value = JSON.stringify(courseIds);
        }
    });
</script>

<style>
    .dual-list-selector {
        transition: background-color 0.2s;
        border: 2px solid transparent;
        border-radius: 0 0 4px 4px;
    }

    .dual-item {
        cursor: move;
        transition: opacity 0.15s ease, background-color 0.1s;
    }

    .dual-item:hover {
        background-color: rgba(0, 123, 255, 0.05);
    }

    .dual-item.opacity-50 {
        opacity: 0.5;
    }
</style>
