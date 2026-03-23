@extends('layouts.app')

@section('title', 'Positions')

@section('styles')
<style>
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }
    .btn-add {
        background: var(--primary);
        border: none;
        color: #fff;
        border-radius: var(--radius-sm);
        padding: 0.55rem 1.2rem;
        font-weight: 600;
        font-size: 0.85rem;
        transition: all 0.15s;
        cursor: pointer;
    }
    .btn-add:hover { background: var(--primary-dark); color: #fff; box-shadow: var(--shadow-sm); }

    .pos-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; }
    @media (max-width: 1199px) { .pos-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 767px) { .pos-grid { grid-template-columns: 1fr; } }

    .pos-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        padding: 1.35rem;
        transition: all 0.2s ease;
        display: flex;
        flex-direction: column;
    }
    .pos-card:hover {
        box-shadow: var(--shadow-md);
        border-color: var(--primary);
        transform: translateY(-2px);
    }

    .pos-card-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        margin-bottom: 0.85rem;
    }
    .pos-icon {
        width: 42px;
        height: 42px;
        border-radius: var(--radius-sm);
        background: var(--primary-muted);
        color: var(--primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
        flex-shrink: 0;
    }
    .pos-name {
        font-weight: 700;
        font-size: 0.95rem;
        color: var(--text-dark);
        margin-top: 0.75rem;
    }
    .pos-name-th {
        font-size: 0.78rem;
        color: var(--text-light);
        margin-top: 1px;
    }

    .pos-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-top: 0.75rem;
        flex: 1;
    }
    .pos-tag {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.25rem 0.6rem;
        border-radius: 999px;
        background: var(--cream);
        color: var(--text-mid);
        border: 1px solid var(--border);
    }
    .pos-tag i { font-size: 0.7rem; }

    .status-toggle {
        background: none;
        border: none;
        cursor: pointer;
        padding: 0;
        transition: transform 0.15s;
    }
    .status-toggle:hover { transform: scale(1.05); }
    .status-active {
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.2rem 0.6rem;
        border-radius: 999px;
        background: rgba(18,183,106,0.12);
        color: #12b76a;
    }
    .status-inactive {
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.2rem 0.6rem;
        border-radius: 999px;
        background: rgba(107,140,147,0.15);
        color: #6b8c93;
    }

    .pos-card-footer {
        display: flex;
        gap: 0.5rem;
        margin-top: 1rem;
        padding-top: 0.85rem;
        border-top: 1px solid var(--border);
    }
    .pos-card-footer .btn { flex: 1; font-size: 0.8rem; font-weight: 600; border-radius: var(--radius-sm); }

    /* Modal */
    .modal-content { border: none; border-radius: var(--radius-lg); box-shadow: var(--shadow-lg); }
    .modal-header { border-bottom: 1px solid var(--border); padding: 1.25rem 1.5rem; }
    .modal-title { font-weight: 700; font-size: 1rem; color: var(--text-dark); }
    .modal-body { padding: 1.5rem; }
    .modal-footer { border-top: 1px solid var(--border); padding: 1rem 1.5rem; }
    .modal-footer .btn-primary { background: var(--primary); border: none; border-radius: var(--radius-sm); font-weight: 600; }
    .modal-footer .btn-primary:hover { background: var(--primary-dark); }

    .empty-state {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        text-align: center;
        padding: 3rem;
    }
</style>
@endsection

@section('content')
    <div class="page-header">
        <h3 class="fw-bold mb-0">Positions</h3>
        <button class="btn-add" data-bs-toggle="modal" data-bs-target="#positionModal" onclick="document.getElementById('positionForm').reset(); document.getElementById('positionForm').action='{{ route('employee.positions.store') }}'; document.getElementById('positionMethod').innerHTML=''; document.getElementById('positionModalLabel').textContent='Add Position';">
            <i class="bi bi-plus-lg me-1"></i> Add Position
        </button>
    </div>

    @if($positions->count())
        <div class="pos-grid">
            @foreach($positions as $position)
                <div class="pos-card">
                    <div class="pos-card-top">
                        <span class="pos-icon"><i class="bi bi-diagram-3"></i></span>
                        <form action="{{ route('employee.positions.toggle', $position) }}" method="POST">
                            @csrf
                            <button type="submit" class="status-toggle">
                                <span class="{{ $position->is_active ? 'status-active' : 'status-inactive' }}">
                                    {{ $position->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </button>
                        </form>
                    </div>
                    <div class="pos-name">{{ $position->name }}</div>
                    @if($position->name_th)
                        <div class="pos-name-th">{{ $position->name_th }}</div>
                    @endif

                    <div class="pos-meta">
                        @if($position->department)
                            <span class="pos-tag"><i class="bi bi-building"></i> {{ $position->department }}</span>
                        @endif
                        <span class="pos-tag"><i class="bi bi-layers"></i> Level {{ $position->level }}</span>
                        <span class="pos-tag"><i class="bi bi-people"></i> {{ $position->employees_count }} employees</span>
                    </div>

                    <div class="pos-card-footer">
                        <button class="btn btn-sm btn-outline-primary" onclick="editPosition({{ $position->toJson() }})">
                            <i class="bi bi-pencil me-1"></i>Edit
                        </button>
                        @if($position->employees_count === 0)
                            <form action="{{ route('employee.positions.destroy', $position) }}" method="POST" class="flex-fill" onsubmit="return confirm('Delete this position?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger w-100">
                                    <i class="bi bi-trash me-1"></i>Delete
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="empty-state">
            <i class="bi bi-diagram-3" style="font-size:2.5rem; color:var(--text-light);"></i>
            <p class="mt-2 mb-0 text-muted">No positions found.</p>
        </div>
    @endif

    {{-- Position Modal --}}
    <div class="modal fade" id="positionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="positionForm" action="{{ route('employee.positions.store') }}" method="POST">
                    @csrf
                    <div id="positionMethod"></div>
                    <div class="modal-header">
                        <h5 class="modal-title" id="positionModalLabel"><i class="bi bi-diagram-3 me-2 text-primary"></i>Add Position</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Name (EN) <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="pos_name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Name (TH)</label>
                                <input type="text" name="name_th" id="pos_name_th" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Department</label>
                                <input type="text" name="department" id="pos_department" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Level <span class="text-danger">*</span></label>
                                <input type="number" name="level" id="pos_level" class="form-control" min="0" value="0" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Description</label>
                                <textarea name="description" id="pos_description" rows="2" class="form-control"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check-lg me-1"></i>Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
function editPosition(p) {
    document.getElementById('positionForm').action = '{{ url("employee/positions") }}/' + p.id;
    document.getElementById('positionMethod').innerHTML = '@method("PUT")';
    document.getElementById('positionModalLabel').innerHTML = '<i class="bi bi-diagram-3 me-2 text-primary"></i>Edit Position';
    document.getElementById('pos_name').value = p.name;
    document.getElementById('pos_name_th').value = p.name_th || '';
    document.getElementById('pos_department').value = p.department || '';
    document.getElementById('pos_level').value = p.level;
    document.getElementById('pos_description').value = p.description || '';
    new bootstrap.Modal(document.getElementById('positionModal')).show();
}
</script>
@endsection
