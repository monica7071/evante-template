@extends('super-admin.layout')

@section('title', 'All Users')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0">All Users</h3>
    </div>

    {{-- Filter Bar --}}
    <div class="card mb-4">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('super-admin.users.index') }}" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Organization</label>
                    <select name="org" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach($organizations as $org)
                            <option value="{{ $org->id }}" {{ request('org') == $org->id ? 'selected' : '' }}>
                                {{ $org->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="leader" {{ request('role') === 'leader' ? 'selected' : '' }}>Leader</option>
                        <option value="agent" {{ request('role') === 'agent' ? 'selected' : '' }}>Agent</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                </div>
                @if(request()->hasAny(['org', 'role', 'status']))
                <div class="col-md-2">
                    <a href="{{ route('super-admin.users.index') }}" class="btn btn-outline-secondary btn-sm w-100">
                        Clear
                    </a>
                </div>
                @endif
            </form>
        </div>
    </div>

    {{-- Users Table --}}
    <div class="card">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Organization</th>
                        <th class="text-center">Role</th>
                        <th class="text-center">Status</th>
                        <th>Last Login</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                @if($user->avatar)
                                    <img src="{{ asset('storage/' . $user->avatar) }}" alt=""
                                         class="rounded-circle" style="width:30px; height:30px; object-fit:cover;">
                                @else
                                    <span class="d-flex align-items-center justify-content-center rounded-circle fw-bold"
                                          style="width:30px; height:30px; font-size:0.65rem; background:var(--primary-muted); color:var(--primary);">
                                        {{ $user->initials }}
                                    </span>
                                @endif
                                <span class="fw-semibold">{{ $user->name }}</span>
                            </div>
                        </td>
                        <td style="font-size:0.85rem;">{{ $user->email }}</td>
                        <td>
                            @if($user->organization)
                                <span style="font-size:0.85rem;">{{ $user->organization->name }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge badge-role-{{ $user->role }} rounded-pill px-2 py-1" style="font-size:0.72rem;">
                                {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if($user->is_active)
                                <span class="badge badge-active rounded-pill px-2 py-1">Active</span>
                            @else
                                <span class="badge badge-inactive rounded-pill px-2 py-1">Inactive</span>
                            @endif
                        </td>
                        <td style="font-size:0.82rem; color:var(--text-light);">
                            {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                @if(!$user->isSuperAdmin())
                                    <form method="POST" action="{{ route('super-admin.impersonate', $user) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-secondary btn-sm">
                                            <i class="bi bi-box-arrow-in-right"></i>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('super-admin.users.toggle-active', $user) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-sm {{ $user->is_active ? 'btn-outline-danger' : 'btn-outline-secondary' }}">
                                            {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <i class="bi bi-people"></i>
                                No users found.
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
        <div class="card-body border-top d-flex justify-content-center">
            {{ $users->links() }}
        </div>
        @endif
    </div>
@endsection
