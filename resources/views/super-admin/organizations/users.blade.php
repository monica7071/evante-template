@extends('super-admin.layout')

@section('title', $organization->name . ' — Users')

@section('content')
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="{{ route('super-admin.organizations.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h3 class="fw-bold mb-0">{{ $organization->name }}</h3>
            <div style="font-size:0.82rem; color:var(--text-light);">{{ $users->count() }} users</div>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
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
                                            <i class="bi bi-box-arrow-in-right"></i> Impersonate
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
                        <td colspan="6">
                            <div class="empty-state">
                                <i class="bi bi-people"></i>
                                No users in this organization.
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
