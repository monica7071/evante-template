@extends('layouts.app')

@section('title', 'Project Detail')

@section('styles')
<style>
    .img-thumb {
        width: 80px; height: 80px;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid #e4e7ec;
    }
    .asset-row {
        display: flex; align-items: center; gap: 12px;
        padding: 8px 0; border-bottom: 1px solid #f2f4f7;
    }
    .asset-row:last-child { border-bottom: none; }
    .asset-label { width: 60px; font-weight: 600; font-size: .85rem; color: #344054; flex-shrink: 0; }
    .asset-placeholder {
        width: 80px; height: 80px; border-radius: 8px;
        background: var(--cream); border: 2px dashed var(--border);
        display: flex; align-items: center; justify-content: center;
        color: #98a2b3; font-size: 1.5rem; flex-shrink: 0;
    }
    .upload-form { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
</style>
@endsection

@section('content')
<div class="row g-4">
    {{-- Project Info --}}
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-semibold">Project Detail</span>
                <a href="{{ route('projects.edit', $project) }}" class="btn btn-sm btn-outline-primary">Edit</a>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-5">Building</dt>
                    <dd class="col-sm-7">{{ $project->name }}</dd>
                    <dt class="col-sm-5">Location</dt>
                    <dd class="col-sm-7">{{ $project->location->project_name }}</dd>
                    <dt class="col-sm-5">Province</dt>
                    <dd class="col-sm-7">{{ $project->location->province }}</dd>
                    <dt class="col-sm-5">Total Floors</dt>
                    <dd class="col-sm-7">{{ $project->total_floors }}</dd>
                    <dt class="col-sm-5">Total Units</dt>
                    <dd class="col-sm-7">{{ $project->total_units ?? '-' }}</dd>
                    <dt class="col-sm-5">Listings</dt>
                    <dd class="col-sm-7"><span class="badge bg-secondary">{{ $project->listings_count }}</span></dd>
                </dl>
            </div>
            <div class="card-footer">
                <a href="{{ route('projects.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
            </div>
        </div>
    </div>

    {{-- Floor Plan Images --}}
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header fw-semibold">
                <i class="bi bi-map me-2 text-secondary"></i>Floor Plan Images
                <small class="text-muted fw-normal ms-2">— แต่ละชั้นของตึก {{ $project->name }}</small>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success py-2">{{ session('success') }}</div>
                @endif

                @for ($f = 1; $f <= $project->total_floors; $f++)
                <div class="asset-row">
                    <div class="asset-label">{{ $project->name }}{{ $f }}</div>
                    @if(isset($floorPlanImages[$f]))
                        <img src="{{ asset('storage/' . $floorPlanImages[$f]) }}" class="img-thumb" alt="Floor {{ $f }}">
                    @else
                        <div class="asset-placeholder"><i class="bi bi-image"></i></div>
                    @endif
                    <div class="flex-grow-1">
                        <form method="POST" action="{{ route('projects.floor-plan.upload', $project) }}" enctype="multipart/form-data" class="upload-form">
                            @csrf
                            <input type="hidden" name="floor" value="{{ $f }}">
                            <input type="file" name="image" accept="image/*" class="form-control form-control-sm" style="max-width:220px" required>
                            <button class="btn btn-sm btn-dark">Upload</button>
                        </form>
                    </div>
                    @if(isset($floorPlanImages[$f]))
                    <form method="POST" action="{{ route('projects.floor-plan.remove', $project) }}">
                        @csrf @method('DELETE')
                        <input type="hidden" name="floor" value="{{ $f }}">
                        <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Remove floor {{ $f }} image?')">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                    @endif
                </div>
                @endfor
            </div>
        </div>

        {{-- Room Layout Images --}}
        <div class="card">
            <div class="card-header fw-semibold">
                <i class="bi bi-grid me-2 text-secondary"></i>Room Layout Images
                <small class="text-muted fw-normal ms-2">— ตาม Unit Type (ใช้ร่วมทุกตึก)</small>
            </div>
            <div class="card-body">
                @php
                    $layoutTypes = ['A','B','C','D','E','F','H','J','K','L','M','N','O','P'];
                @endphp
                @foreach($layoutTypes as $type)
                <div class="asset-row">
                    <div class="asset-label">Type {{ $type }}</div>
                    @if(isset($roomLayoutImages[$type]))
                        <img src="{{ asset('storage/' . $roomLayoutImages[$type]) }}" class="img-thumb" alt="Type {{ $type }}">
                    @else
                        <div class="asset-placeholder"><i class="bi bi-image"></i></div>
                    @endif
                    <div class="flex-grow-1">
                        <form method="POST" action="{{ route('projects.room-layout.upload', $project) }}" enctype="multipart/form-data" class="upload-form">
                            @csrf
                            <input type="hidden" name="unit_type" value="{{ $type }}">
                            <input type="file" name="image" accept="image/*" class="form-control form-control-sm" style="max-width:220px" required>
                            <button class="btn btn-sm btn-dark">Upload</button>
                        </form>
                    </div>
                    @if(isset($roomLayoutImages[$type]))
                    <form method="POST" action="{{ route('projects.room-layout.remove', $project) }}">
                        @csrf @method('DELETE')
                        <input type="hidden" name="unit_type" value="{{ $type }}">
                        <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Remove type {{ $type }} layout?')">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
