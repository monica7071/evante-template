@extends('layouts.app')

@section('title', 'Edit Location')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">Edit Location</div>
                <div class="card-body">
                    <form action="{{ route('locations.update', $location) }}" method="POST">
                        @csrf @method('PUT')
                        @include('listings.locations._form')
                        <div class="mt-4">
                            <button class="btn btn-primary">Update</button>
                            <a href="{{ route('locations.index') }}" class="btn btn-outline-secondary ms-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @include('listings.locations._thai-selects-script')
@endsection
