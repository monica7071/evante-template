@extends('layouts.app')

@section('title', 'Edit Project')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">Edit Project</div>
                <div class="card-body">
                    <form action="{{ route('projects.update', $project) }}" method="POST">
                        @csrf @method('PUT')
                        @include('listings.projects._form')
                        <div class="mt-4">
                            <button class="btn btn-primary">Update</button>
                            <a href="{{ route('projects.index') }}" class="btn btn-outline-secondary ms-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
