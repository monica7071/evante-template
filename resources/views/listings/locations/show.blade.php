@extends('layouts.app')

@section('title', 'Location Detail')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Location Detail</span>
                    <a href="{{ route('locations.edit', $location) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Project Name</dt>
                        <dd class="col-sm-8">{{ $location->project_name }}</dd>

                        <dt class="col-sm-4">Province</dt>
                        <dd class="col-sm-8">{{ $location->province }}</dd>

                        <dt class="col-sm-4">District</dt>
                        <dd class="col-sm-8">{{ $location->district ?? '-' }}</dd>

                        <dt class="col-sm-4">Subdistrict</dt>
                        <dd class="col-sm-8">{{ $location->subdistrict ?? '-' }}</dd>

                        <dt class="col-sm-4">Address</dt>
                        <dd class="col-sm-8">{{ $location->address ?? '-' }}</dd>

                        <dt class="col-sm-4">Projects</dt>
                        <dd class="col-sm-8"><span class="badge bg-secondary">{{ $location->projects_count }}</span></dd>

                        <dt class="col-sm-4">Created</dt>
                        <dd class="col-sm-8">{{ $location->created_at->format('Y-m-d H:i') }}</dd>
                    </dl>
                </div>
                <div class="card-footer">
                    <a href="{{ route('locations.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
                </div>
            </div>
        </div>
    </div>
@endsection
