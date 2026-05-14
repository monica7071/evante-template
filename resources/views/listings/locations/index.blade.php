@extends('layouts.app')

@section('title', 'Locations')

@section('styles')
    <style>
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        .btn-create {
            background: var(--primary);
            border: none;
            border-radius: var(--radius-sm);
            font-weight: 600;
            font-size: 0.875rem;
            padding: 0.6rem 1.4rem;
            color: #fff;
            box-shadow: var(--shadow-sm);
            transition: all 0.2s ease;
            text-decoration: none;
        }
        .btn-create:hover {
            background: var(--primary-dark);
            color: #fff;
            box-shadow: 0 4px 14px rgba(42,139,146,0.35);
            transform: translateY(-1px);
        }
        .location-card {
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            transition: all 0.2s ease;
            height: 100%;
        }
        .location-card:hover {
            box-shadow: 0 4px 16px rgba(0,0,0,0.08);
            transform: translateY(-2px);
        }
        .location-card .card-body {
            padding: 1.25rem;
        }
        .location-card .card-title {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
        }
        .location-card .card-title a {
            color: inherit;
            text-decoration: none;
        }
        .location-card .card-title a:hover {
            color: var(--primary);
        }
        .location-card .info-row {
            display: flex;
            justify-content: space-between;
            padding: 0.35rem 0;
            font-size: 0.875rem;
            border-bottom: 1px solid #f3f4f6;
        }
        .location-card .info-row:last-child {
            border-bottom: none;
        }
        .location-card .info-label {
            color: #6b7280;
        }
        .location-card .info-value {
            font-weight: 600;
            color: #1f2937;
        }
        .dropdown-menu-actions {
            min-width: 8rem;
        }
        .btn-dots {
            background: none;
            border: none;
            padding: 0.25rem 0;
            font-size: 1.25rem;
            color: #6b7280;
            line-height: 1;
        }
        .btn-dots:hover {
            color: #1f2937;
        }
    </style>
@endsection

@section('content')
    <div class="page-header">
        <h3 class="mb-0 fw-bold">Locations</h3>
        @permission('listing_locations.create')
        <a href="{{ route('locations.create') }}" class="btn-create">
            <i class="bi bi-plus me-1"></i> Add Location
        </a>
        @endpermission
    </div>

    <form method="GET" class="row g-2 mb-3">
        <div class="col-auto">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search..." value="{{ request('search') }}">
        </div>
        <div class="col-auto">
            <button class="btn btn-sm btn-outline-secondary">Search</button>
            @if(request('search'))
                <a href="{{ route('locations.index') }}" class="btn btn-sm btn-outline-danger">Clear</a>
            @endif
        </div>
    </form>

    <div class="row g-3">
        @forelse($locations as $location)
            <div class="col-md-6 col-lg-4">
                <div class="card location-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <h5 class="card-title mb-0">
                                <a href="{{ route('locations.show', $location) }}">{{ $location->project_name }}</a>
                            </h5>
                            @if(auth()->user()->hasAnyPermission(['listing_locations.edit', 'listing_locations.delete']))
                            <div class="dropdown">
                                <button class="btn-dots" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-actions">
                                    @permission('listing_locations.edit')
                                    <li>
                                        <a class="dropdown-item" href="{{ route('locations.edit', $location) }}">
                                            <i class="bi bi-pencil me-2"></i>Edit
                                        </a>
                                    </li>
                                    @endpermission
                                    @permission('listing_locations.delete')
                                    <li>
                                        <form action="{{ route('locations.destroy', $location) }}" method="POST" onsubmit="return confirm('Delete this location?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="bi bi-trash me-2"></i>Delete
                                            </button>
                                        </form>
                                    </li>
                                    @endpermission
                                </ul>
                            </div>
                            @endif
                        </div>

                        <div class="mt-3">
                            <div class="info-row">
                                <span class="info-label"><i class="bi bi-map me-1"></i>Province</span>
                                <span class="info-value">{{ $location->province }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label"><i class="bi bi-pin-map me-1"></i>District</span>
                                <span class="info-value">{{ $location->district ?? '-' }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label"><i class="bi bi-building me-1"></i>Projects</span>
                                <span class="info-value"><span class="badge bg-secondary">{{ $location->projects_count }}</span></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="text-muted text-center py-5">No locations found.</div>
            </div>
        @endforelse
    </div>

    @if($locations->hasPages())
        <div class="mt-3">{{ $locations->links() }}</div>
    @endif
@endsection
