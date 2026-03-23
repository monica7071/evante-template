@extends('super-admin.layout')

@section('title', 'Dashboard')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0">Dashboard</h3>
    </div>

    {{-- ═══ Stats Row ═══ --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="kpi-card">
                <div class="kpi-card-title">Organizations</div>
                <div class="kpi-card-value">{{ $stats['active_orgs'] }} <span style="font-size:0.85rem; font-weight:500; color:var(--text-light);">/ {{ $stats['total_orgs'] }}</span></div>
                <div class="kpi-card-sub">active / total</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="kpi-card">
                <div class="kpi-card-title">Total Users</div>
                <div class="kpi-card-value">{{ number_format($stats['total_users']) }}</div>
                <div class="kpi-card-sub">across all organizations</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="kpi-card">
                <div class="kpi-card-title">Total Listings</div>
                <div class="kpi-card-value">{{ number_format($stats['total_listings']) }}</div>
                <div class="kpi-card-sub">across all organizations</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="kpi-card" @if($stats['storage_alert_orgs'] > 0) style="border-color:#f59e0b;" @endif>
                <div class="kpi-card-title">Storage Alerts</div>
                <div class="kpi-card-value" @if($stats['storage_alert_orgs'] > 0) style="color:#f59e0b;" @endif>
                    {{ $stats['storage_alert_orgs'] }}
                </div>
                <div class="kpi-card-sub">orgs with >80% storage used</div>
            </div>
        </div>
    </div>

    {{-- ═══ Organizations Table ═══ --}}
    <div class="card mb-4">
        <div class="card-body p-0">
            <div class="d-flex align-items-center justify-content-between px-3 py-3 border-bottom" style="background:var(--cream); border-radius:var(--radius) var(--radius) 0 0;">
                <h6 class="fw-bold mb-0"><i class="bi bi-buildings me-2" style="color:var(--primary);"></i>Organizations Overview</h6>
                <a href="{{ route('super-admin.organizations.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg me-1"></i> New Organization
                </a>
            </div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Organization</th>
                            <th class="text-center">Users</th>
                            <th class="text-center">Listings</th>
                            <th>Storage</th>
                            <th class="text-center">Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($organizations as $org)
                        <tr>
                            <td class="text-muted">{{ $org->id }}</td>
                            <td>
                                <div class="fw-semibold">{{ $org->name }}</div>
                                @if($org->name_th)
                                    <div style="font-size:0.75rem; color:var(--text-light);">{{ $org->name_th }}</div>
                                @endif
                            </td>
                            <td class="text-center">{{ $org->users_count }}</td>
                            <td class="text-center">{{ $org->listings_count }}</td>
                            <td style="min-width:140px;">
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
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('super-admin.organizations.edit', $org) }}" class="btn btn-outline-secondary btn-sm">Edit</a>
                                    <a href="{{ route('super-admin.organizations.users', $org) }}" class="btn btn-outline-secondary btn-sm">Users</a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <i class="bi bi-buildings"></i>
                                    No organizations yet.
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ═══ Recent Activity ═══ --}}
    <div class="card">
        <div class="card-body p-0">
            <div class="px-3 py-3 border-bottom" style="background:var(--cream); border-radius:var(--radius) var(--radius) 0 0;">
                <h6 class="fw-bold mb-0"><i class="bi bi-lightning-charge me-2" style="color:var(--primary);"></i>Recent Activity (All Organizations)</h6>
            </div>
            @if($recentActivity->count())
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Sale No.</th>
                            <th>Changed By</th>
                            <th>From</th>
                            <th></th>
                            <th>To</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentActivity as $activity)
                        <tr>
                            <td class="fw-semibold">{{ $activity->sale->sale_number ?? '—' }}</td>
                            <td>{{ $activity->user->name ?? '—' }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $activity->previous_status ?? '—' }}</span></td>
                            <td class="text-center text-muted"><i class="bi bi-arrow-right"></i></td>
                            <td><span class="badge bg-light text-dark border">{{ $activity->status }}</span></td>
                            <td style="font-size:0.8rem; color:var(--text-light);">{{ $activity->created_at->diffForHumans() }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="empty-state">
                <i class="bi bi-clock-history"></i>
                No recent activity.
            </div>
            @endif
        </div>
    </div>
@endsection
