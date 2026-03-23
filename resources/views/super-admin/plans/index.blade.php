@extends('super-admin.layout')

@section('title', 'Plans')

@section('content')
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h3 class="fw-bold mb-0">Plans</h3>
        <a href="{{ route('super-admin.plans.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> New Plan
        </a>
    </div>

    <div class="card">
        <div class="card-body p-0">
            @if($plans->isEmpty())
                <div class="empty-state">
                    <i class="bi bi-box-seam"></i>
                    No plans yet. Create your first plan.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Slug</th>
                                <th>Storage</th>
                                <th>Price</th>
                                <th>Orgs</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($plans as $plan)
                                <tr>
                                    <td class="fw-semibold">{{ $plan->name }}</td>
                                    <td><code style="font-size:0.82rem;">{{ $plan->slug }}</code></td>
                                    <td>{{ $plan->storageLimitInGB() }} GB</td>
                                    <td>{{ number_format($plan->price, 2) }}</td>
                                    <td>{{ $plan->organizations_count }}</td>
                                    <td>
                                        <span class="badge rounded-pill {{ $plan->is_active ? 'badge-active' : 'badge-inactive' }}">
                                            {{ $plan->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex gap-1 justify-content-end">
                                            <a href="{{ route('super-admin.plans.edit', $plan) }}" class="btn btn-outline-secondary btn-sm">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form method="POST" action="{{ route('super-admin.plans.toggle-active', $plan) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-sm {{ $plan->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                                        title="{{ $plan->is_active ? 'Deactivate' : 'Activate' }}">
                                                    <i class="bi {{ $plan->is_active ? 'bi-pause-circle' : 'bi-play-circle' }}"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
