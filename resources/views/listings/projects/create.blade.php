@extends('layouts.app')

@section('title', 'Add Project')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">Add Project</div>
                <div class="card-body">
                    <form action="{{ route('projects.store') }}" method="POST">
                        @csrf
                        @include('listings.projects._form')
                        <div class="mt-4">
                            <button class="btn btn-primary">Save</button>
                            <a href="{{ route('projects.index') }}" class="btn btn-outline-secondary ms-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
