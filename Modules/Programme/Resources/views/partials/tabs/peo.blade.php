{{-- PEO Tab --}}
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Programme Educational Objectives (PEOs)</h5>
            @can('update', $programme)
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addPeoModal">
                    <i class="bi bi-plus-lg me-1"></i>Add PEO
                </button>
            @endcan
        </div>

        @if($programme->programmePEOs->isNotEmpty())
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 80px;">Code</th>
                            <th>Description</th>
                            <th style="width: 100px;">Order</th>
                            <th style="width: 120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($programme->programmePEOs->sortBy('sequence_order') as $peo)
                            <tr>
                                <td><span class="badge bg-light text-dark">{{ $peo->code }}</span></td>
                                <td>{{ $peo->description }}</td>
                                <td>{{ $peo->sequence_order }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        @can('update', $programme)
                                            <button class="btn btn-outline-warning btn-sm" onclick="editPEO({{ $peo->id }})">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                        @endcan
                                        @can('update', $programme)
                                            <button class="btn btn-outline-danger btn-sm" onclick="deletePEO({{ $peo->id }})">
                                                <i class="bi bi-trash"></i>
                                            </button>
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
                <i class="bi bi-inbox text-muted" style="font-size: 2rem;"></i>
                <p class="text-muted mt-2 mb-0">No PEOs defined for this programme</p>
            </div>
        @endif
    </div>
</div>

<!-- Add/Edit PEO Modal -->
<div class="modal fade" id="addPeoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add PEO</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="peoForm" onsubmit="savePEO(event)">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="peo_code" class="form-label">Code</label>
                        <input type="text" class="form-control" id="peo_code" name="code" required>
                    </div>
                    <div class="mb-3">
                        <label for="peo_desc" class="form-label">Description</label>
                        <textarea class="form-control" id="peo_desc" name="description" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="peo_order" class="form-label">Sequence Order</label>
                        <input type="number" class="form-control" id="peo_order" name="sequence_order" min="1" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save PEO</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function savePEO(e) {
    e.preventDefault();
    const data = {
        programme_id: {{ $programme->id }},
        code: document.getElementById('peo_code').value,
        description: document.getElementById('peo_desc').value,
        sequence_order: document.getElementById('peo_order').value,
    };
    
    fetch(`/programmes/{{ $programme->id }}/peos`, {
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

function deletePEO(id) {
    if (confirm('Are you sure?')) {
        fetch(`/programmes/peos/${id}`, {
            method: 'DELETE',
            headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content}
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
</script>
