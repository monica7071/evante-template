@extends('layouts.app')

@section('title', 'Listing Detail')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Listing Detail</span>
                    <a href="{{ route('units.edit', $unit) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                </div>
                <div class="card-body">
                    @php
                        $colors = ['available'=>'success','reserved'=>'warning','contract'=>'info','installment'=>'primary','transferred'=>'secondary'];
                    @endphp
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Room Number</dt>
                        <dd class="col-sm-8">{{ $unit->room_number }}</dd>

                        <dt class="col-sm-4">Unit Code</dt>
                        <dd class="col-sm-8">{{ $unit->unit_code ?? '-' }}</dd>

                        <dt class="col-sm-4">Project</dt>
                        <dd class="col-sm-8">{{ $unit->project->name ?? '-' }}</dd>

                        <dt class="col-sm-4">Location</dt>
                        <dd class="col-sm-8">{{ $unit->location->project_name ?? '-' }}</dd>

                        <dt class="col-sm-4">Floor</dt>
                        <dd class="col-sm-8">{{ $unit->floor ?? '-' }}</dd>

                        <dt class="col-sm-4">Bedrooms</dt>
                        <dd class="col-sm-8">{{ $unit->bedrooms ?? '-' }}</dd>

                        <dt class="col-sm-4">Area (sqm)</dt>
                        <dd class="col-sm-8">{{ $unit->area ? number_format($unit->area, 2) : '-' }}</dd>

                        <dt class="col-sm-4">Unit Type</dt>
                        <dd class="col-sm-8">{{ $unit->unit_type ?? '-' }}</dd>

                        <dt class="col-sm-4">Price per Room</dt>
                        <dd class="col-sm-8">{{ $unit->price_per_room ? number_format($unit->price_per_room, 2) : '-' }}</dd>

                        <dt class="col-sm-4">Price per SQM</dt>
                        <dd class="col-sm-8">{{ $unit->price_per_sqm ? number_format($unit->price_per_sqm, 2) : '-' }}</dd>

                        <dt class="col-sm-4">Status</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-{{ $colors[$unit->status] ?? 'dark' }}">{{ ucfirst($unit->status) }}</span>
                        </dd>

                        <dt class="col-sm-4">Created</dt>
                        <dd class="col-sm-8">{{ $unit->created_at->format('Y-m-d H:i') }}</dd>
                    </dl>
                </div>
                <div class="card-footer">
                    <a href="{{ route('units.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
                </div>
            </div>
        </div>
    </div>
@endsection
