@extends('layouts.app')

@section('title', 'Roles & Permissions')

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

    .role-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; }
    @media (max-width: 1199px) { .role-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 767px) { .role-grid { grid-template-columns: 1fr; } }

    .role-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        padding: 1.35rem;
        transition: all 0.2s ease;
        display: flex;
        flex-direction: column;
    }
    .role-card:hover {
        box-shadow: var(--shadow-md);
        border-color: var(--primary);
        transform: translateY(-2px);
    }
    .role-card-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        margin-bottom: 0.85rem;
    }
    .role-icon {
        width: 42px; height: 42px;
        border-radius: var(--radius-sm);
        background: var(--primary-muted);
        color: var(--primary);
        display: flex; align-items: center; justify-content: center;
        font-size: 1.15rem; flex-shrink: 0;
    }
    .role-name { font-weight: 700; font-size: 0.95rem; color: var(--text-dark); margin-top: 0.75rem; }
    .role-desc { font-size: 0.78rem; color: var(--text-light); margin-top: 1px; }
    .role-meta { display: flex; flex-wrap: wrap; gap: 0.5rem; margin-top: 0.75rem; flex: 1; }
    .role-tag {
        display: inline-flex; align-items: center; gap: 4px;
        font-size: 0.75rem; font-weight: 600; padding: 0.25rem 0.6rem;
        border-radius: 999px; background: var(--cream); color: var(--text-mid); border: 1px solid var(--border);
    }
    .role-tag i { font-size: 0.7rem; }
    .badge-default { font-size: 0.72rem; font-weight: 600; padding: 0.2rem 0.6rem; border-radius: 999px; background: rgba(42,139,146,0.12); color: var(--primary); }
    .badge-custom { font-size: 0.72rem; font-weight: 600; padding: 0.2rem 0.6rem; border-radius: 999px; background: rgba(107,140,147,0.15); color: #6b8c93; }
    .role-card-footer {
        display: flex; gap: 0.5rem; margin-top: 1rem;
        padding-top: 0.85rem; border-top: 1px solid var(--border);
    }
    .role-card-footer .btn { flex: 1; font-size: 0.8rem; font-weight: 600; border-radius: var(--radius-sm); }

    /* Modal */
    .modal-content { border: none; border-radius: var(--radius-lg); box-shadow: var(--shadow-lg); }
    .modal-header { border-bottom: 1px solid var(--border); padding: 1.25rem 1.5rem; }
    .modal-title { font-weight: 700; font-size: 1rem; color: var(--text-dark); }
    .modal-body { padding: 1.5rem; max-height: 70vh; overflow-y: auto; }
    .modal-footer { border-top: 1px solid var(--border); padding: 1rem 1.5rem; }
    .modal-footer .btn-primary { background: var(--primary); border: none; border-radius: var(--radius-sm); font-weight: 600; }
    .modal-footer .btn-primary:hover { background: var(--primary-dark); }

    .empty-state {
        background: var(--surface); border: 1px solid var(--border);
        border-radius: var(--radius-lg); text-align: center; padding: 3rem;
    }

    /* ── Permission Menu ── */
    .perm-menu { display: flex; flex-direction: column; gap: 2px; }

    .perm-section {
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        overflow: hidden;
    }

    .perm-section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.65rem 1rem;
        background: var(--cream);
        cursor: pointer;
        user-select: none;
        transition: background 0.15s;
    }
    .perm-section-header:hover { background: var(--cream-dark); }

    .perm-section-left {
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 600;
        font-size: 0.88rem;
        color: var(--text-dark);
    }
    .perm-section-left i { font-size: 1rem; color: var(--primary); width: 20px; text-align: center; }

    .perm-section-right {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .perm-count {
        font-size: 0.7rem;
        font-weight: 600;
        padding: 2px 8px;
        border-radius: 999px;
        background: rgba(42,139,146,0.1);
        color: var(--primary);
    }

    .perm-chevron {
        font-size: 0.75rem;
        color: var(--text-light);
        transition: transform 0.2s;
    }
    .perm-section.open .perm-chevron { transform: rotate(180deg); }

    .perm-section-body {
        display: none;
        padding: 0.75rem 1rem;
        background: var(--surface);
        border-top: 1px solid var(--border);
    }
    .perm-section.open .perm-section-body { display: block; }

    /* Action chips */
    .perm-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        padding: 2px 0;
    }

    .perm-chip {
        position: relative;
    }
    .perm-chip input { display: none; }
    .perm-chip-label {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 5px 12px;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 500;
        border: 1px solid var(--border);
        background: var(--surface);
        color: var(--text-mid);
        cursor: pointer;
        transition: all 0.15s;
    }
    .perm-chip-label:hover {
        border-color: var(--primary);
        color: var(--primary);
    }
    .perm-chip input:checked + .perm-chip-label {
        background: var(--primary);
        border-color: var(--primary);
        color: #fff;
    }
    .perm-chip-label i { font-size: 0.7rem; }

    /* Sub-item inside a section */
    .perm-sub {
        padding: 0.5rem 0;
    }
    .perm-sub + .perm-sub {
        border-top: 1px solid var(--border);
    }
    .perm-sub-label {
        font-weight: 600;
        font-size: 0.82rem;
        color: var(--text-dark);
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .perm-sub-label i {
        font-size: 0.75rem;
        color: var(--text-light);
    }

    .select-all-link {
        font-size: 0.72rem;
        color: var(--primary);
        font-weight: 600;
        cursor: pointer;
        background: none;
        border: none;
        padding: 0;
        margin-left: auto;
    }
    .select-all-link:hover { text-decoration: underline; }
</style>
@endsection

@section('content')
    <div class="page-header">
        <h3 class="fw-bold mb-0">Roles & Permissions</h3>
        <button class="btn-add" data-bs-toggle="modal" data-bs-target="#roleModal" onclick="openCreateModal()">
            <i class="bi bi-plus-lg me-1"></i> Add Role
        </button>
    </div>

    @if($roles->count())
        <div class="role-grid">
            @foreach($roles as $role)
                <div class="role-card">
                    <div class="role-card-top">
                        <span class="role-icon"><i class="bi bi-shield-check"></i></span>
                        <span class="{{ $role->is_default ? 'badge-default' : 'badge-custom' }}">
                            {{ $role->is_default ? 'Default' : 'Custom' }}
                        </span>
                    </div>
                    <div class="role-name">{{ $role->display_name }}</div>
                    @if($role->description)
                        <div class="role-desc">{{ $role->description }}</div>
                    @endif

                    <div class="role-meta">
                        <span class="role-tag"><i class="bi bi-people"></i> {{ $role->users_count }} users</span>
                        <span class="role-tag"><i class="bi bi-key"></i> {{ $role->permissions->count() }} permissions</span>
                    </div>

                    <div class="role-card-footer">
                        <button class="btn btn-sm btn-outline-primary" onclick="editRole({{ $role->id }}, {{ json_encode($role->toArray()) }}, {{ json_encode($role->permissions->pluck('id')) }})">
                            <i class="bi bi-pencil me-1"></i>Edit
                        </button>
                        @if(!$role->is_default && $role->users_count === 0)
                            <form action="{{ route('employee.roles.destroy', $role) }}" method="POST" class="flex-fill" onsubmit="return confirm('Delete this role?')">
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
            <i class="bi bi-shield-check" style="font-size:2.5rem; color:var(--text-light);"></i>
            <p class="mt-2 mb-0 text-muted">No roles found.</p>
        </div>
    @endif

    {{-- Role Modal --}}
    <div class="modal fade" id="roleModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="roleForm" action="{{ route('employee.roles.store') }}" method="POST">
                    @csrf
                    <div id="roleMethod"></div>
                    <div class="modal-header">
                        <h5 class="modal-title" id="roleModalLabel"><i class="bi bi-shield-check me-2 text-primary"></i>Add Role</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Name (slug) <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="role_name" class="form-control" required
                                       pattern="[a-z_]+" title="Lowercase letters and underscores only"
                                       placeholder="e.g. marketing_team">
                                <div class="form-text">Lowercase letters and underscores only</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Display Name <span class="text-danger">*</span></label>
                                <input type="text" name="display_name" id="role_display_name" class="form-control" required
                                       placeholder="e.g. Marketing Team">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Description</label>
                                <input type="text" name="description" id="role_description" class="form-control"
                                       placeholder="Short description">
                            </div>
                        </div>

                        <h6 class="fw-bold mb-3"><i class="bi bi-key me-1"></i> Page Access & Permissions</h6>

                        <div class="perm-menu">
                            @foreach($menuPermissions as $section)
                                @php
                                    $sectionId = Str::slug($section['label']);
                                    $hasChildren = !empty($section['children']);
                                    $allPerms = $hasChildren
                                        ? collect($section['children'])->pluck('permissions')->flatten(1)
                                        : collect($section['permissions']);
                                @endphp
                                <div class="perm-section" data-section="{{ $sectionId }}">
                                    <div class="perm-section-header" onclick="toggleSection('{{ $sectionId }}')">
                                        <span class="perm-section-left">
                                            <i class="bi {{ $section['icon'] }}"></i>
                                            {{ $section['label'] }}
                                        </span>
                                        <span class="perm-section-right">
                                            <span class="perm-count" data-count-for="{{ $sectionId }}">0 / {{ $allPerms->count() }}</span>
                                            <button type="button" class="select-all-link" onclick="event.stopPropagation(); toggleAllInSection('{{ $sectionId }}')">All</button>
                                            <i class="bi bi-chevron-down perm-chevron"></i>
                                        </span>
                                    </div>
                                    <div class="perm-section-body">
                                        @if($hasChildren)
                                            @foreach($section['children'] as $child)
                                                <div class="perm-sub">
                                                    <div class="perm-sub-label">
                                                        <i class="bi bi-dot"></i> {{ $child['label'] }}
                                                    </div>
                                                    <div class="perm-actions">
                                                        @foreach($child['permissions'] as $perm)
                                                            <label class="perm-chip">
                                                                <input type="checkbox" name="permissions[]" value="{{ $perm->id }}"
                                                                       data-section="{{ $sectionId }}"
                                                                       onchange="updateCount('{{ $sectionId }}')">
                                                                <span class="perm-chip-label">
                                                                    @if($perm->action === 'view')<i class="bi bi-eye"></i>
                                                                    @elseif($perm->action === 'create')<i class="bi bi-plus-circle"></i>
                                                                    @elseif($perm->action === 'edit')<i class="bi bi-pencil"></i>
                                                                    @elseif($perm->action === 'delete')<i class="bi bi-trash"></i>
                                                                    @elseif($perm->action === 'import')<i class="bi bi-file-earmark-arrow-up"></i>
                                                                    @elseif($perm->action === 'export')<i class="bi bi-download"></i>
                                                                    @elseif($perm->action === 'manage')<i class="bi bi-sliders"></i>
                                                                    @elseif($perm->action === 'manage_budget')<i class="bi bi-calculator"></i>
                                                                    @elseif($perm->action === 'manage_mappings')<i class="bi bi-sliders"></i>
                                                                    @elseif($perm->action === 'transfer')<i class="bi bi-arrow-left-right"></i>
                                                                    @elseif($perm->action === 'upload')<i class="bi bi-upload"></i>
                                                                    @elseif($perm->action === 'download')<i class="bi bi-download"></i>
                                                                    @else<i class="bi bi-check-circle"></i>
                                                                    @endif
                                                                    {{ ucfirst(str_replace('_', ' ', $perm->action)) }}
                                                                </span>
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="perm-actions">
                                                @foreach($section['permissions'] as $perm)
                                                    <label class="perm-chip">
                                                        <input type="checkbox" name="permissions[]" value="{{ $perm->id }}"
                                                               data-section="{{ $sectionId }}"
                                                               onchange="updateCount('{{ $sectionId }}')">
                                                        <span class="perm-chip-label">
                                                            @if($perm->action === 'view')<i class="bi bi-eye"></i>
                                                            @elseif($perm->action === 'create')<i class="bi bi-plus-circle"></i>
                                                            @elseif($perm->action === 'edit')<i class="bi bi-pencil"></i>
                                                            @elseif($perm->action === 'delete')<i class="bi bi-trash"></i>
                                                            @elseif($perm->action === 'export')<i class="bi bi-download"></i>
                                                            @elseif($perm->action === 'manage')<i class="bi bi-sliders"></i>
                                                            @elseif($perm->action === 'manage_budget')<i class="bi bi-calculator"></i>
                                                            @elseif($perm->action === 'manage_mappings')<i class="bi bi-sliders"></i>
                                                            @elseif($perm->action === 'transfer')<i class="bi bi-arrow-left-right"></i>
                                                            @elseif($perm->action === 'upload')<i class="bi bi-upload"></i>
                                                            @elseif($perm->action === 'download')<i class="bi bi-download"></i>
                                                            @elseif($perm->action === 'advance')<i class="bi bi-arrow-right-circle"></i>
                                                            @elseif($perm->action === 'cancel')<i class="bi bi-x-circle"></i>
                                                            @elseif($perm->action === 'deal_slip')<i class="bi bi-receipt"></i>
                                                            @elseif($perm->action === 'remarks')<i class="bi bi-chat-left-text"></i>
                                                            @else<i class="bi bi-check-circle"></i>
                                                            @endif
                                                            {{ ucfirst(str_replace('_', ' ', $perm->action)) }}
                                                        </span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
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
function toggleSection(id) {
    document.querySelector(`[data-section="${id}"]`).classList.toggle('open');
}

function updateCount(sectionId) {
    const cbs = document.querySelectorAll(`input[data-section="${sectionId}"]`);
    const checked = Array.from(cbs).filter(c => c.checked).length;
    const el = document.querySelector(`[data-count-for="${sectionId}"]`);
    if (el) el.textContent = checked + ' / ' + cbs.length;
}

function updateAllCounts() {
    document.querySelectorAll('.perm-section').forEach(section => {
        updateCount(section.dataset.section);
    });
}

function toggleAllInSection(sectionId) {
    const cbs = document.querySelectorAll(`input[data-section="${sectionId}"]`);
    const allChecked = Array.from(cbs).every(c => c.checked);
    cbs.forEach(c => c.checked = !allChecked);
    updateCount(sectionId);
}

function openCreateModal() {
    document.getElementById('roleForm').action = '{{ route("employee.roles.store") }}';
    document.getElementById('roleMethod').innerHTML = '';
    document.getElementById('roleModalLabel').innerHTML = '<i class="bi bi-shield-check me-2 text-primary"></i>Add Role';
    document.getElementById('role_name').value = '';
    document.getElementById('role_name').readOnly = false;
    document.getElementById('role_display_name').value = '';
    document.getElementById('role_description').value = '';
    document.querySelectorAll('#roleForm input[name="permissions[]"]').forEach(cb => cb.checked = false);
    // Collapse all sections
    document.querySelectorAll('.perm-section').forEach(s => s.classList.remove('open'));
    updateAllCounts();
}

function editRole(id, role, permissionIds) {
    document.getElementById('roleForm').action = '{{ url("employee/roles") }}/' + id;
    document.getElementById('roleMethod').innerHTML = '@method("PUT")';
    document.getElementById('roleModalLabel').innerHTML = '<i class="bi bi-shield-check me-2 text-primary"></i>Edit Role';
    document.getElementById('role_name').value = role.name;
    document.getElementById('role_name').readOnly = true;
    document.getElementById('role_display_name').value = role.display_name;
    document.getElementById('role_description').value = role.description || '';

    document.querySelectorAll('#roleForm input[name="permissions[]"]').forEach(cb => {
        cb.checked = permissionIds.includes(parseInt(cb.value));
    });

    // Open sections that have checked items, close others
    document.querySelectorAll('.perm-section').forEach(section => {
        const sectionId = section.dataset.section;
        const cbs = section.querySelectorAll('input[name="permissions[]"]');
        const hasChecked = Array.from(cbs).some(c => c.checked);
        section.classList.toggle('open', hasChecked);
    });

    updateAllCounts();
    new bootstrap.Modal(document.getElementById('roleModal')).show();
}
</script>
@endsection
