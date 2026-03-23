@extends('layouts.app')

@section('title', 'Teams')

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

    .team-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; }
    @media (max-width: 1199px) { .team-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 767px) { .team-grid { grid-template-columns: 1fr; } }

    .team-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        padding: 1.35rem;
        transition: all 0.2s ease;
        display: flex;
        flex-direction: column;
    }
    .team-card:hover {
        box-shadow: var(--shadow-md);
        border-color: var(--primary);
        transform: translateY(-2px);
    }

    .team-card-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        margin-bottom: 0.85rem;
    }
    .team-icon {
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
    .team-name {
        font-weight: 700;
        font-size: 0.95rem;
        color: var(--text-dark);
        margin-top: 0.75rem;
    }
    .team-name-th {
        font-size: 0.78rem;
        color: var(--text-light);
        margin-top: 1px;
    }

    .team-info {
        margin-top: 0.75rem;
        flex: 1;
    }
    .team-info-row {
        display: flex;
        align-items: center;
        padding: 0.35rem 0;
        font-size: 0.82rem;
    }
    .team-info-row + .team-info-row {
        border-top: 1px solid rgba(0,0,0,0.04);
    }
    .team-info-label {
        color: var(--text-light);
        font-weight: 500;
        width: 70px;
        flex-shrink: 0;
    }
    .team-info-value {
        color: var(--text-dark);
        font-weight: 500;
    }

    .members-link {
        background: none;
        border: none;
        padding: 0;
        color: var(--primary);
        font-weight: 600;
        font-size: 0.82rem;
        cursor: pointer;
        text-decoration: underline;
        text-decoration-style: dotted;
        text-underline-offset: 2px;
    }
    .members-link:hover { color: var(--primary-dark); }

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

    .team-card-footer {
        display: flex;
        gap: 0.5rem;
        margin-top: 1rem;
        padding-top: 0.85rem;
        border-top: 1px solid var(--border);
    }
    .team-card-footer .btn { flex: 1; font-size: 0.8rem; font-weight: 600; border-radius: var(--radius-sm); }

    /* Modal */
    .modal-content { border: none; border-radius: var(--radius-lg); box-shadow: var(--shadow-lg); }
    .modal-header { border-bottom: 1px solid var(--border); padding: 1.25rem 1.5rem; }
    .modal-title { font-weight: 700; font-size: 1rem; color: var(--text-dark); }
    .modal-body { padding: 1.5rem; }
    .modal-footer { border-top: 1px solid var(--border); padding: 1rem 1.5rem; }
    .modal-footer .btn-primary { background: var(--primary); border: none; border-radius: var(--radius-sm); font-weight: 600; }
    .modal-footer .btn-primary:hover { background: var(--primary-dark); }

    /* Members modal */
    .member-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.65rem 0;
    }
    .member-item + .member-item { border-top: 1px solid rgba(0,0,0,0.06); }
    .member-name { font-weight: 600; font-size: 0.875rem; color: var(--text-dark); }
    .member-pos { font-size: 0.78rem; color: var(--text-light); }
    .member-code {
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.2rem 0.5rem;
        border-radius: 999px;
        background: var(--primary-muted);
        color: var(--primary);
    }

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
        <h3 class="fw-bold mb-0">Teams</h3>
        <button class="btn-add" data-bs-toggle="modal" data-bs-target="#teamModal" onclick="resetTeamForm()">
            <i class="bi bi-plus-lg me-1"></i> Add Team
        </button>
    </div>

    @if($teams->count())
        <div class="team-grid">
            @foreach($teams as $team)
                <div class="team-card">
                    <div class="team-card-top">
                        <span class="team-icon"><i class="bi bi-people-fill"></i></span>
                        <form action="{{ route('employee.teams.toggle', $team) }}" method="POST">
                            @csrf
                            <button type="submit" class="status-toggle">
                                <span class="{{ $team->is_active ? 'status-active' : 'status-inactive' }}">
                                    {{ $team->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </button>
                        </form>
                    </div>
                    <div class="team-name">{{ $team->name }}</div>
                    @if($team->name_th)
                        <div class="team-name-th">{{ $team->name_th }}</div>
                    @endif

                    <div class="team-info">
                        <div class="team-info-row">
                            <span class="team-info-label">Leader</span>
                            <span class="team-info-value">{{ $team->leader ? $team->leader->full_name : '-' }}</span>
                        </div>
                        <div class="team-info-row">
                            <span class="team-info-label">Parent</span>
                            <span class="team-info-value">{{ $team->parentTeam->name ?? '-' }}</span>
                        </div>
                        <div class="team-info-row">
                            <span class="team-info-label">Members</span>
                            <button type="button" class="members-link" onclick="viewMembers({{ $team->id }}, '{{ $team->name }}')">
                                {{ $team->employees_count }} members
                            </button>
                        </div>
                    </div>

                    <div class="team-card-footer">
                        <button class="btn btn-sm btn-outline-primary" onclick="editTeam({{ $team->toJson() }})">
                            <i class="bi bi-pencil me-1"></i>Edit
                        </button>
                        @if($team->employees_count === 0)
                            <form action="{{ route('employee.teams.destroy', $team) }}" method="POST" class="flex-fill" onsubmit="return confirm('Delete this team?')">
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
            <i class="bi bi-people-fill" style="font-size:2.5rem; color:var(--text-light);"></i>
            <p class="mt-2 mb-0 text-muted">No teams found.</p>
        </div>
    @endif

    {{-- Team Modal --}}
    <div class="modal fade" id="teamModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="teamForm" action="{{ route('employee.teams.store') }}" method="POST">
                    @csrf
                    <div id="teamMethod"></div>
                    <div class="modal-header">
                        <h5 class="modal-title" id="teamModalLabel"><i class="bi bi-people-fill me-2 text-primary"></i>Add Team</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Name (EN) <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="team_name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Name (TH)</label>
                                <input type="text" name="name_th" id="team_name_th" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Leader</label>
                                <select name="leader_id" id="team_leader" class="form-select">
                                    <option value="">-- None --</option>
                                    @foreach($employees as $emp)
                                        <option value="{{ $emp->id }}">{{ $emp->full_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Parent Team</label>
                                <select name="parent_team_id" id="team_parent" class="form-select">
                                    <option value="">-- None --</option>
                                    @foreach($allTeams as $t)
                                        <option value="{{ $t->id }}">{{ $t->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Description</label>
                                <textarea name="description" id="team_description" rows="2" class="form-control"></textarea>
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

    {{-- Members Modal --}}
    <div class="modal fade" id="membersModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="membersModalLabel"><i class="bi bi-people me-2 text-primary"></i>Team Members</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="membersList"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
function resetTeamForm() {
    document.getElementById('teamForm').reset();
    document.getElementById('teamForm').action = '{{ route('employee.teams.store') }}';
    document.getElementById('teamMethod').innerHTML = '';
    document.getElementById('teamModalLabel').innerHTML = '<i class="bi bi-people-fill me-2 text-primary"></i>Add Team';
}

function editTeam(t) {
    document.getElementById('teamForm').action = '{{ url("employee/teams") }}/' + t.id;
    document.getElementById('teamMethod').innerHTML = '@method("PUT")';
    document.getElementById('teamModalLabel').innerHTML = '<i class="bi bi-people-fill me-2 text-primary"></i>Edit Team';
    document.getElementById('team_name').value = t.name;
    document.getElementById('team_name_th').value = t.name_th || '';
    document.getElementById('team_leader').value = t.leader_id || '';
    document.getElementById('team_parent').value = t.parent_team_id || '';
    document.getElementById('team_description').value = t.description || '';
    new bootstrap.Modal(document.getElementById('teamModal')).show();
}

function viewMembers(teamId, teamName) {
    document.getElementById('membersModalLabel').innerHTML = '<i class="bi bi-people me-2 text-primary"></i>' + teamName + ' - Members';
    document.getElementById('membersList').innerHTML = '<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary"></div></div>';
    new bootstrap.Modal(document.getElementById('membersModal')).show();

    fetch('{{ url("employee/teams") }}/' + teamId + '/members')
        .then(r => r.json())
        .then(members => {
            if (members.length === 0) {
                document.getElementById('membersList').innerHTML = '<p class="text-muted text-center mb-0">No members in this team.</p>';
                return;
            }
            let html = '';
            members.forEach(m => {
                html += `<div class="member-item">
                    <div>
                        <div class="member-name">${m.first_name} ${m.last_name}</div>
                        <div class="member-pos">${m.position ? m.position.name : '-'}</div>
                    </div>
                    <span class="member-code">${m.employee_code}</span>
                </div>`;
            });
            document.getElementById('membersList').innerHTML = html;
        });
}
</script>
@endsection
