@extends('layouts.app')

@section('title', 'Employees')

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
        text-decoration: none;
        transition: all 0.15s;
    }
    .btn-add:hover {
        background: var(--primary-dark);
        color: #fff;
        box-shadow: var(--shadow-sm);
    }

    /* Filter */
    .filter-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        padding: 1rem 1.25rem;
        margin-bottom: 1.5rem;
    }

    /* Employee card */
    .emp-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        padding: 1.35rem;
        transition: all 0.2s ease;
        position: relative;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    .emp-card:hover {
        box-shadow: var(--shadow-md);
        border-color: var(--primary);
        transform: translateY(-2px);
    }

    .emp-card-top {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        margin-bottom: 1rem;
    }
    .emp-avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        object-fit: cover;
        flex-shrink: 0;
    }
    .emp-avatar-initials {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
        font-weight: 700;
        color: #fff;
        background: var(--primary);
        flex-shrink: 0;
    }
    .emp-name {
        font-weight: 700;
        font-size: 0.95rem;
        color: var(--text-dark);
        line-height: 1.3;
    }
    .emp-nickname {
        font-size: 0.78rem;
        color: var(--text-light);
    }
    .emp-code {
        font-size: 0.7rem;
        font-weight: 600;
        color: var(--primary);
        background: var(--primary-muted);
        padding: 0.15rem 0.5rem;
        border-radius: 999px;
        margin-left: auto;
        align-self: flex-start;
    }

    .emp-info {
        flex: 1;
    }
    .emp-info-row {
        display: flex;
        align-items: center;
        padding: 0.35rem 0;
        font-size: 0.82rem;
    }
    .emp-info-row + .emp-info-row {
        border-top: 1px solid rgba(0,0,0,0.04);
    }
    .emp-info-label {
        color: var(--text-light);
        font-weight: 500;
        width: 70px;
        flex-shrink: 0;
    }
    .emp-info-value {
        color: var(--text-dark);
        font-weight: 500;
    }

    .status-pill {
        display: inline-block;
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.2rem 0.6rem;
        border-radius: 999px;
    }
    .status-active { background: rgba(18,183,106,0.12); color: #12b76a; }
    .status-probation { background: rgba(247,144,9,0.12); color: #f79009; }
    .status-inactive { background: rgba(107,140,147,0.15); color: #6b8c93; }
    .status-resigned { background: rgba(220,53,69,0.12); color: #dc3545; }

    .role-badge {
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.2rem 0.6rem;
        border-radius: 999px;
        background: var(--primary-muted);
        color: var(--primary);
    }

    .emp-card-footer {
        display: flex;
        gap: 0.5rem;
        margin-top: 1rem;
        padding-top: 0.85rem;
        border-top: 1px solid var(--border);
    }
    .emp-card-footer .btn {
        flex: 1;
        font-size: 0.8rem;
        font-weight: 600;
        border-radius: var(--radius-sm);
    }

    /* Pagination */
    .pagination-shell {
        display: flex;
        flex-direction: column;
        gap: 20px;
        align-items: center;
        padding: 0.9rem 1.25rem;
        margin-top: 1.5rem;
    }
    .pagination-summary {
        font-size: 0.85rem;
        color: var(--text-mid);
        font-weight: 500;
    }
    .pagination-mini {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        font-size: 0.85rem;
    }
    .pagination-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border-radius: 50px;
        border: 1px solid var(--border);
        color: var(--text-mid);
        font-weight: 600;
        transition: all 0.15s ease;
        text-decoration: none;
        background: #fff;
    }
    .pagination-btn:hover { border-color: var(--primary); color: var(--primary); }
    .pagination-btn.active {
        border-color: var(--primary);
        background: var(--primary);
        color: #fff;
        box-shadow: 0 4px 12px rgba(42,139,146,0.25);
    }
    .pagination-btn.disabled,
    .pagination-btn.disabled:hover {
        border-color: var(--border);
        color: #c0c5cb;
        cursor: not-allowed;
        background: #f3f4f6;
        box-shadow: none;
    }
    .pagination-ellipsis { color: var(--text-light); font-weight: 600; }
</style>
@endsection

@section('content')
    <div class="page-header">
        <h3 class="fw-bold mb-0">Employees</h3>
        @permission('employee_list.create')
        <a href="{{ route('employee.list.create') }}" class="btn-add">
            <i class="bi bi-plus-lg me-1"></i> Add Employee
        </a>
        @endpermission
    </div>

    {{-- Filters --}}
    <div class="filter-card">
        <form action="{{ route('employee.list.index') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search name, code..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    @foreach(['active','probation','inactive','resigned'] as $s)
                        <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="position_id" class="form-select form-select-sm">
                    <option value="">All Positions</option>
                    @foreach($positions as $pos)
                        <option value="{{ $pos->id }}" {{ request('position_id') == $pos->id ? 'selected' : '' }}>{{ $pos->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="team_id" class="form-select form-select-sm">
                    <option value="">All Teams</option>
                    @foreach($teams as $team)
                        <option value="{{ $team->id }}" {{ request('team_id') == $team->id ? 'selected' : '' }}>{{ $team->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-outline-dark"><i class="bi bi-search me-1"></i>Filter</button>
                <a href="{{ route('employee.list.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>

    {{-- Card Grid --}}
    @if($employees->count())
        <div class="row g-3">
            @foreach($employees as $emp)
                @php
                    $statusClass = ['active'=>'status-active','probation'=>'status-probation','inactive'=>'status-inactive','resigned'=>'status-resigned'];
                @endphp
                <div class="col-xl-4 col-lg-6">
                    <div class="emp-card">
                        <div class="emp-card-top">
                            @if($emp->avatar)
                                <img src="{{ asset('storage/' . $emp->avatar) }}" class="emp-avatar" alt="">
                            @else
                                <span class="emp-avatar-initials">
                                    {{ strtoupper(substr($emp->first_name,0,1) . substr($emp->last_name,0,1)) }}
                                </span>
                            @endif
                            <div>
                                <div class="emp-name">{{ $emp->first_name }} {{ $emp->last_name }}</div>
                                @if($emp->nickname)
                                    <div class="emp-nickname">{{ $emp->nickname }}</div>
                                @endif
                            </div>
                            <span class="emp-code">{{ $emp->employee_code }}</span>
                        </div>

                        <div class="emp-info">
                            <div class="emp-info-row">
                                <span class="emp-info-label">Position</span>
                                <span class="emp-info-value">{{ $emp->position->name ?? '-' }}</span>
                            </div>
                            <div class="emp-info-row">
                                <span class="emp-info-label">Team</span>
                                <span class="emp-info-value">{{ $emp->team->name ?? '-' }}</span>
                            </div>
                            <div class="emp-info-row">
                                <span class="emp-info-label">Status</span>
                                <span class="status-pill {{ $statusClass[$emp->status] ?? '' }}">{{ ucfirst($emp->status) }}</span>
                            </div>
                            <div class="emp-info-row">
                                <span class="emp-info-label">Account</span>
                                @if($emp->user)
                                    <span class="role-badge">{{ ucfirst($emp->user->role) }}</span>
                                @else
                                    <span class="text-muted" style="font-size:0.82rem;">No Account</span>
                                @endif
                            </div>
                        </div>

                        <div class="emp-card-footer">
                            @permission('employee_list.edit')
                            <a href="{{ route('employee.list.edit', $emp) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil me-1"></i>Edit
                            </a>
                            @endpermission
                            @permission('employee_list.delete')
                            <form action="{{ route('employee.list.destroy', $emp) }}" method="POST" class="flex-fill" onsubmit="return confirm('Delete this employee?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger w-100">
                                    <i class="bi bi-trash me-1"></i>Delete
                                </button>
                            </form>
                            @endpermission
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if($employees->total() > 0)
            @php
                $firstItem = $employees->firstItem();
                $lastItem = $employees->lastItem();
                $totalItems = $employees->total();
                $currentPage = $employees->currentPage();
                $lastPage = $employees->lastPage();
            @endphp
            <div class="pagination-shell">
                <div class="pagination-summary">
                    Showing {{ $firstItem }} to {{ $lastItem }} of {{ number_format($totalItems) }} results
                </div>
                @if($employees->hasPages())
                    <div class="pagination-mini">
                        @php
                            $prevUrl = $employees->previousPageUrl();
                            $nextUrl = $employees->nextPageUrl();
                        @endphp
                        <a class="pagination-btn {{ $employees->onFirstPage() ? 'disabled' : '' }}" href="{{ $employees->onFirstPage() ? '#' : $prevUrl }}">
                            &lt;
                        </a>
                        <a class="pagination-btn {{ $currentPage === 1 ? 'active' : '' }}" href="{{ $employees->url(1) }}">1</a>
                        @if($currentPage > 2)
                            <span class="pagination-ellipsis">&hellip;</span>
                        @endif
                        @if($currentPage > 1 && $currentPage < $lastPage)
                            <a class="pagination-btn active" href="{{ $employees->url($currentPage) }}">{{ $currentPage }}</a>
                        @endif
                        @if($currentPage < $lastPage - 1)
                            <span class="pagination-ellipsis">&hellip;</span>
                        @endif
                        @if($lastPage > 1)
                            <a class="pagination-btn {{ $currentPage === $lastPage ? 'active' : '' }}" href="{{ $employees->url($lastPage) }}">{{ $lastPage }}</a>
                        @endif
                        <a class="pagination-btn {{ $currentPage === $lastPage ? 'disabled' : '' }}" href="{{ $currentPage === $lastPage ? '#' : $nextUrl }}">
                            &gt;
                        </a>
                    </div>
                @endif
            </div>
        @endif
    @else
        <div class="emp-card text-center py-5">
            <i class="bi bi-people" style="font-size:2.5rem; color:var(--text-light);"></i>
            <p class="mt-2 mb-0 text-muted">No employees found.</p>
        </div>
    @endif
@endsection
