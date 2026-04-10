{{-- Users Dual List Selector Tab Partial --}}
<div class="p-4">
    <div class="mb-4">
        <h6 class="text-muted text-uppercase mb-1">Manage Group Members</h6>
        <p class="text-muted mb-0">Assign users to this group and manage their roles. Drag between lists or use buttons
            to transfer.</p>
    </div>

    {{-- Hidden form to submit changes --}}
    <form id="usersForm">
        @csrf
        <input type="hidden" name="user_ids" id="userIdsInput" value="">
        <input type="hidden" name="role" id="roleInput" value="member">
    </form>

    <!-- Role Filter -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="btn-group" role="group">
                <input type="radio" class="btn-check" name="roleFilter" id="roleAll" value="all" checked>
                <label class="btn btn-outline-secondary" for="roleAll">
                    All Members <span class="badge bg-secondary ms-2">{{ count($assignedUsers) }}</span>
                </label>

                <input type="radio" class="btn-check" name="roleFilter" id="roleCoordinator" value="coordinator">
                <label class="btn btn-outline-secondary" for="roleCoordinator">
                    Coordinators <span
                        class="badge bg-secondary ms-2">{{ count(array_filter($assignedUsers, fn($u) => $u->pivot->role === 'coordinator')) }}</span>
                </label>

                <input type="radio" class="btn-check" name="roleFilter" id="roleAssistant" value="assistant">
                <label class="btn btn-outline-secondary" for="roleAssistant">
                    Assistants <span
                        class="badge bg-secondary ms-2">{{ count(array_filter($assignedUsers, fn($u) => $u->pivot->role === 'assistant')) }}</span>
                </label>

                <input type="radio" class="btn-check" name="roleFilter" id="roleMember" value="member">
                <label class="btn btn-outline-secondary" for="roleMember">
                    Members <span
                        class="badge bg-secondary ms-2">{{ count(array_filter($assignedUsers, fn($u) => $u->pivot->role === 'member')) }}</span>
                </label>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Available Users (Left) --}}
        <div class="col-md-6 mb-3">
            <div class="card border-0 bg-light">
                <div class="card-header bg-white border-bottom-1 py-3">
                    <h6 class="mb-0 text-uppercase">
                        <i class="bi bi-person-plus me-2"></i>Available Users
                        <span class="badge bg-secondary float-end">{{ count($availableUsers) }}</span>
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="input-group input-group-sm p-2 border-bottom-1">
                        <input type="text" id="availableUsersSearch" class="form-control"
                            placeholder="Search users...">
                    </div>
                    <div id="availableUsersContainer" class="list-group list-group-flush dual-list-selector"
                        style="max-height: 500px; overflow-y: auto;">
                        @forelse($availableUsers as $user)
                            <div class="dual-item list-group-item d-flex align-items-center px-3 py-2 border-bottom-1"
                                draggable="true" data-user-id="{{ $user->id }}"
                                data-user-name="{{ $user->name }}">
                                <div class="flex-grow-1 min-width-0">
                                    <strong>{{ $user->name }}</strong>
                                    <div class="small text-muted text-truncate">{{ $user->email }}</div>
                                </div>
                                <button type="button"
                                    class="btn btn-sm btn-primary transfer-btn rounded-circle p-0 ms-2"
                                    style="width: 32px; height: 32px; flex-shrink: 0;" title="Add User">
                                    <i class="bi bi-chevron-right"></i>
                                </button>
                            </div>
                        @empty
                            <div class="p-3 text-center text-muted">
                                <i class="bi bi-inbox" style="font-size: 1.5rem;"></i>
                                <p class="mt-2 mb-0">All users assigned</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- Assigned Users (Right) --}}
        <div class="col-md-6 mb-3">
            <div class="card border-0 bg-light">
                <div class="card-header bg-white border-bottom-1 py-3">
                    <h6 class="mb-0 text-uppercase">
                        <i class="bi bi-check-circle me-2"></i>Assigned Members
                        <span class="badge bg-success float-end">{{ count($assignedUsers) }}</span>
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="input-group input-group-sm p-2 border-bottom-1">
                        <input type="text" id="assignedUsersSearch" class="form-control"
                            placeholder="Search members...">
                    </div>
                    <div id="assignedUsersContainer" class="list-group list-group-flush dual-list-selector"
                        style="max-height: 500px; overflow-y: auto;">
                        @forelse($assignedUsers as $user)
                            <div class="dual-item list-group-item d-flex align-items-center px-3 py-2 border-bottom-1"
                                draggable="true" data-user-id="{{ $user->id }}"
                                data-user-name="{{ $user->name }}"
                                data-user-role="{{ $user->pivot->role ?? 'member' }}">
                                <div class="flex-grow-1 min-width-0">
                                    <strong>{{ $user->name }}</strong>
                                    <div class="small text-muted text-truncate">{{ $user->email }}</div>
                                </div>
                                <div class="d-flex gap-2 align-items-center ms-2" style="flex-shrink: 0;">
                                    <select class="form-select form-select-sm role-select"
                                        data-user-id="{{ $user->id }}" style="width: 100px;">
                                        <option value="member" @selected(($user->pivot->role ?? 'member') === 'member')>Member</option>
                                        <option value="assistant" @selected(($user->pivot->role ?? 'member') === 'assistant')>Assistant</option>
                                        <option value="coordinator" @selected(($user->pivot->role ?? 'member') === 'coordinator')>Coordinator</option>
                                    </select>
                                    <button type="button"
                                        class="btn btn-sm btn-outline-danger transfer-btn rounded-circle p-0"
                                        style="width: 32px; height: 32px;" title="Remove User">
                                        <i class="bi bi-chevron-left"></i>
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="p-3 text-center text-muted">
                                <i class="bi bi-inbox" style="font-size: 1.5rem;"></i>
                                <p class="mt-2 mb-0">No members assigned</p>
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
        <button type="button" class="btn btn-primary" id="submitUsersBtn">
            <i class="bi bi-check-lg me-2"></i>Save Changes
        </button>
    </div>
</div>

{{-- Dual List Selector Script for Users --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const availableContainer = document.getElementById('availableUsersContainer');
        const assignedContainer = document.getElementById('assignedUsersContainer');
        const userIdsInput = document.getElementById('userIdsInput');
        const roleInput = document.getElementById('roleInput');
        const submitBtn = document.getElementById('submitUsersBtn');

        // Search functionality
        setupSearchFilters();

        // Drag and Drop Setup
        setupDragAndDrop();

        // Transfer Button Click Handlers
        document.querySelectorAll('.transfer-btn').forEach(btn => {
            btn.addEventListener('click', handleTransferClick);
        });

        // Submit Handler with AJAX
        submitBtn.addEventListener('click', function() {
            updateUserIds();

            const userIds = JSON.parse(document.getElementById('userIdsInput').value);
            const roles = {};

            // Get roles from all assigned users
            document.querySelectorAll('#assignedUsersContainer .role-select').forEach(select => {
                roles[select.dataset.userId] = select.value;
            });

            // Disable button while processing
            submitBtn.disabled = true;
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML =
            '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';

            // First, assign new users
            fetch('{{ route('groups.users.assign', $group) }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        user_ids: userIds,
                        role: 'member'
                    })
                })
                .then(response => {
                    if (!response.ok) throw new Error('Failed to assign users');
                    return response.json();
                })
                .then(data => {
                    // Update roles for each user
                    const roleUpdatePromises = [];
                    Object.keys(roles).forEach(userId => {
                        if (roles[userId] !== 'member') {
                            roleUpdatePromises.push(
                                fetch(
                                    `{{ route('groups.users.role', [$group, '$userId', '$role']) }}`
                                    .replace('$userId', userId).replace('$role', roles[
                                        userId]), {
                                        method: 'PUT',
                                        headers: {
                                            'X-CSRF-TOKEN': document.querySelector(
                                                'input[name="_token"]').value,
                                            'Accept': 'application/json',
                                        }
                                    })
                            );
                        }
                    });
                    return Promise.all(roleUpdatePromises).then(() => data);
                })
                .then(data => {
                    // Show success message
                    const alert = document.createElement('div');
                    alert.className = 'alert alert-success alert-dismissible fade show';
                    alert.innerHTML = `
                <i class="bi bi-check-circle me-2"></i>
                Members updated successfully!
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
                Failed to update members. Please try again.
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

        // Role select change handlers
        document.querySelectorAll('.role-select').forEach(select => {
            select.addEventListener('change', function() {
                const userId = this.dataset.userId;
                // Update role on server (you'll implement this with AJAX)
                console.log('Role changed for user', userId, 'to', this.value);
            });
        });

        function setupSearchFilters() {
            const availableSearch = document.getElementById('availableUsersSearch');
            const assignedSearch = document.getElementById('assignedUsersSearch');

            if (availableSearch) {
                availableSearch.addEventListener('keyup', function() {
                    filterUsers(availableContainer, this.value);
                });
            }

            if (assignedSearch) {
                assignedSearch.addEventListener('keyup', function() {
                    filterUsers(assignedContainer, this.value);
                });
            }
        }

        function filterUsers(container, searchTerm) {
            const items = container.querySelectorAll('.dual-item');
            searchTerm = searchTerm.toLowerCase();

            items.forEach(item => {
                const name = item.dataset.userName.toLowerCase();
                const email = item.querySelector('.small')?.textContent.toLowerCase() || '';

                if (name.includes(searchTerm) || email.includes(searchTerm)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        function setupDragAndDrop() {
            setupItemDragHandlers();

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
                        setupItemDragHandlers();
                    }
                });
            });
        }

        function setupItemDragHandlers() {
            document.querySelectorAll('.dual-item').forEach(item => {
                item.removeEventListener('dragstart', handleDragStart);
                item.removeEventListener('dragend', handleDragEnd);
                item.addEventListener('dragstart', handleDragStart);
                item.addEventListener('dragend', handleDragEnd);
            });
        }

        function handleDragStart(e) {
            e.dataTransfer.effectAllowed = 'move';
            this.classList.add('opacity-50');
        }

        function handleDragEnd(e) {
            this.classList.remove('opacity-50');
        }

        function handleTransferClick(e) {
            e.preventDefault();
            const item = this.closest('.dual-item');
            const container = item.closest('.dual-list-selector');

            item.style.opacity = '0';
            setTimeout(() => {
                if (container === availableContainer) {
                    assignedContainer.appendChild(item);
                } else {
                    availableContainer.appendChild(item);
                }
                item.style.opacity = '1';
                setupTransferButtons();
                setupItemDragHandlers();
            }, 150);
        }

        function setupTransferButtons() {
            document.querySelectorAll('.dual-item').forEach(item => {
                const btn = item.querySelector('.transfer-btn');
                const container = item.closest('.dual-list-selector');

                if (container === availableContainer) {
                    btn.className = 'btn btn-sm btn-primary transfer-btn rounded-circle p-0';
                    btn.style.width = '32px';
                    btn.style.height = '32px';
                    btn.innerHTML = '<i class="bi bi-chevron-right"></i>';
                    btn.title = 'Add User';

                    // Hide role select if exists
                    const roleSelect = item.querySelector('.role-select');
                    if (roleSelect) roleSelect.style.display = 'none';
                } else {
                    btn.className = 'btn btn-sm btn-outline-danger transfer-btn rounded-circle p-0';
                    btn.style.width = '32px';
                    btn.style.height = '32px';
                    btn.innerHTML = '<i class="bi bi-chevron-left"></i>';
                    btn.title = 'Remove User';

                    // Show role select if doesn't exist, create it
                    if (!item.querySelector('.role-select')) {
                        const roleSelect = document.createElement('select');
                        roleSelect.className = 'form-select form-select-sm role-select';
                        roleSelect.style.width = '100px';
                        roleSelect.style.marginRight = '8px';
                        roleSelect.innerHTML = `
                        <option value="member">Member</option>
                        <option value="assistant">Assistant</option>
                        <option value="coordinator">Coordinator</option>
                    `;
                        item.querySelector('.d-flex.gap-2')?.insertBefore(roleSelect, item
                            .querySelector('.transfer-btn'));
                    }
                }

                btn.removeEventListener('click', handleTransferClick);
                btn.addEventListener('click', handleTransferClick);
            });
        }

        function updateUserIds() {
            const userIds = [];
            document.querySelectorAll('#assignedUsersContainer .dual-item').forEach(item => {
                userIds.push(item.dataset.userId);
            });
            userIdsInput.value = JSON.stringify(userIds);
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

    .min-width-0 {
        min-width: 0;
    }
</style>
