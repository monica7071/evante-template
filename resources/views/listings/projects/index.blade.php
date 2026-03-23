@extends('layouts.app')

@section('title', 'Projects')

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
        .project-card {
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            transition: all 0.2s ease;
            height: 100%;
        }
        .project-card:hover {
            box-shadow: 0 4px 16px rgba(0,0,0,0.08);
            transform: translateY(-2px);
        }
        .project-card .card-body {
            padding: 1.25rem;
        }
        .project-card .card-title {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
        }
        .project-card .card-title a {
            color: inherit;
            text-decoration: none;
        }
        .project-card .card-title a:hover {
            color: var(--primary);
        }
        .project-card .info-row {
            display: flex;
            justify-content: space-between;
            padding: 0.35rem 0;
            font-size: 0.875rem;
            border-bottom: 1px solid #f3f4f6;
        }
        .project-card .info-row:last-child {
            border-bottom: none;
        }
        .project-card .info-label {
            color: #6b7280;
        }
        .project-card .info-value {
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
        <h3 class="mb-0 fw-bold">Projects (Buildings)</h3>
        <a href="{{ route('projects.create') }}" class="btn-create">
            <i class="bi bi-plus me-1"></i> Add Project
        </a>
    </div>

    <form method="GET" class="row g-2 mb-3">
        <div class="col-auto">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search..." value="{{ request('search') }}">
        </div>
        <div class="col-auto">
            <button class="btn btn-sm btn-outline-secondary">Search</button>
            @if(request('search'))
                <a href="{{ route('projects.index') }}" class="btn btn-sm btn-outline-danger">Clear</a>
            @endif
        </div>
    </form>

    <div class="row g-3">
        @forelse($projects as $project)
            <div class="col-md-6 col-lg-4">
                <div class="card project-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <h5 class="card-title mb-0">
                                <a href="{{ route('projects.show', $project) }}">Building : {{ $project->name }}</a>
                            </h5>
                            <div class="dropdown">
                                <button class="btn-dots" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-actions">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('projects.edit', $project) }}">
                                            <i class="bi bi-pencil me-2"></i>Edit
                                        </a>
                                    </li>
                                    <li>
                                        <form action="{{ route('projects.destroy', $project) }}" method="POST" onsubmit="return confirm('Delete this project?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="bi bi-trash me-2"></i>Delete
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="mt-3">
                            <div class="info-row">
                                <span class="info-label"><i class="bi bi-geo-alt me-1"></i>Location</span>
                                <span class="info-value">{{ $project->location->project_name }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label"><i class="bi bi-layers me-1"></i>Floors</span>
                                <span class="info-value">{{ $project->total_floors }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label"><i class="bi bi-door-open me-1"></i>Units</span>
                                <span class="info-value">{{ $project->total_units ?? '-' }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label"><i class="bi bi-card-list me-1"></i>Listings</span>
                                <span class="info-value"><span class="badge bg-secondary">{{ $project->listings_count }}</span></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="text-muted text-center py-5">No projects found.</div>
            </div>
        @endforelse
    </div>

    @if($projects->hasPages())
        <div class="mt-3">{{ $projects->links() }}</div>
    @endif
@endsection
