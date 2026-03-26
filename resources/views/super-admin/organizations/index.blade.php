@extends('super-admin.layout')

@section('title', 'Organizations')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0">Organizations</h3>
        <a href="{{ route('super-admin.organizations.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> New Organization
        </a>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Organization</th>
                        <th>Slug</th>
                        <th class="text-center">Users</th>
                        <th class="text-center">Listings</th>
                        <th>Storage</th>
                        <th class="text-center">Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($organizations as $org)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                @if($org->logo)
                                    <img src="{{ asset('storage/' . $org->logo) }}" alt="" style="width:28px; height:28px; border-radius:8px; object-fit:cover;">
                                @else
                                    <span class="d-flex align-items-center justify-content-center rounded" style="width:28px; height:28px; font-size:0.7rem; font-weight:700; background:var(--primary-muted); color:var(--primary);">
                                        {{ strtoupper(substr($org->name, 0, 2)) }}
                                    </span>
                                @endif
                                <div>
                                    <div class="fw-semibold">{{ $org->name }}</div>
                                    @if($org->name_th)
                                        <div style="font-size:0.72rem; color:var(--text-light);">{{ $org->name_th }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td><code style="font-size:0.8rem;">{{ $org->slug }}</code></td>
                        <td class="text-center">{{ $org->users_count }}</td>
                        <td class="text-center">{{ $org->listings_count }}</td>
                        <td style="min-width:130px;">
                            <div class="storage-bar-wrap mb-1">
                                <div class="storage-bar-fill" style="width:{{ $org->storagePercentage() }}%; background:{{ $org->storageBarColor() }};"></div>
                            </div>
                            <div style="font-size:0.72rem; color:var(--text-light);">
                                {{ $org->storageUsedGB() }} GB / {{ $org->storageLimitGB() }} GB
                            </div>
                        </td>
                        <td class="text-center">
                            @if($org->is_active)
                                <span class="badge badge-active rounded-pill px-2 py-1">Active</span>
                            @else
                                <span class="badge badge-inactive rounded-pill px-2 py-1">Inactive</span>
                            @endif
                        </td>
                        <td style="font-size:0.8rem; color:var(--text-light);">
                            {{ $org->created_at->format('d M Y') }}
                        </td>
                        <td>
                            <div class="d-flex gap-1 flex-wrap">
                                <a href="{{ route('super-admin.organizations.edit', $org) }}" class="btn btn-outline-secondary btn-sm">Edit</a>
                                <a href="{{ route('super-admin.organizations.users', $org) }}" class="btn btn-outline-secondary btn-sm">Users</a>
                                <form method="POST" action="{{ route('super-admin.organizations.toggle-active', $org) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm {{ $org->is_active ? 'btn-outline-danger' : 'btn-outline-secondary' }}">
                                        {{ $org->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <i class="bi bi-buildings"></i>
                                No organizations yet. Create your first one.
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
