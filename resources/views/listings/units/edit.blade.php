@extends('layouts.app')

@section('title', 'Edit Listing')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="fw-bold mb-0">Edit Listing</h4>
                <a href="{{ route('units.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Back
                </a>
            </div>

            <form action="{{ route('units.update', $unit) }}" method="POST" enctype="multipart/form-data">
                @csrf @method('PUT')
                @include('listings.units._form')
                <div class="d-flex gap-2 mt-3 mb-2">
                    <button class="btn btn-primary px-4">
                        <i class="bi bi-check-lg me-1"></i>Update
                    </button>
                    <a href="{{ route('units.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>

            {{-- Delete Listing --}}
            <div class="form-section" style="border-color: rgba(220,53,69,0.2); background: rgba(220,53,69,0.03);">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-danger mb-1 fw-semibold" style="font-size:0.85rem;">
                            <i class="bi bi-exclamation-triangle me-1"></i>Delete this listing
                        </h6>
                        <small class="text-muted">This action cannot be undone. All room images will be deleted.</small>
                    </div>
                    <form action="{{ route('units.destroy', $unit) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this listing? This cannot be undone.')">
                        @csrf @method('DELETE')
                        <button class="btn btn-outline-danger btn-sm">
                            <i class="bi bi-trash me-1"></i>Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
