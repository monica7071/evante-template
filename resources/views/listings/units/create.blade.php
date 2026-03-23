@extends('layouts.app')

@section('title', 'Add Listing')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="fw-bold mb-0">Add Listing</h4>
                <a href="{{ route('units.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Back
                </a>
            </div>

            <form action="{{ route('units.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @include('listings.units._form')
                <div class="d-flex gap-2 mt-3 mb-2">
                    <button class="btn btn-primary px-4">
                        <i class="bi bi-check-lg me-1"></i>Save
                    </button>
                    <a href="{{ route('units.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
