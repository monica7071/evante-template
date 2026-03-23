@extends('super-admin.layout')

@section('title', 'Create Plan')

@section('content')
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="{{ route('super-admin.plans.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h3 class="fw-bold mb-0">Create New Plan</h3>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8 col-xl-7">
            <div class="card">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('super-admin.plans.store') }}">
                        @csrf

                        <div class="row g-3">
                            {{-- Name --}}
                            <div class="col-md-6">
                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="planName" class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name') }}" required placeholder="e.g. Starter, Pro, Enterprise">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Slug --}}
                            <div class="col-md-6">
                                <label class="form-label">Slug <span class="text-danger">*</span></label>
                                <input type="text" name="slug" id="planSlug" class="form-control @error('slug') is-invalid @enderror"
                                       value="{{ old('slug') }}" required>
                                <div class="form-text">URL-friendly identifier (auto-generated from name)</div>
                                @error('slug')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Storage Limit --}}
                            <div class="col-md-6">
                                <label class="form-label">Storage Limit (GB) <span class="text-danger">*</span></label>
                                <input type="number" name="storage_limit_gb" class="form-control @error('storage_limit_gb') is-invalid @enderror"
                                       value="{{ old('storage_limit_gb', 10) }}" min="1" step="0.01" required>
                                @error('storage_limit_gb')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Price --}}
                            <div class="col-md-6">
                                <label class="form-label">Price <span class="text-danger">*</span></label>
                                <input type="number" name="price" class="form-control @error('price') is-invalid @enderror"
                                       value="{{ old('price', 0) }}" min="0" step="0.01" required>
                                @error('price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Description --}}
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror"
                                          placeholder="Optional plan description">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Is Active --}}
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <div class="form-check form-switch mt-1">
                                    <input type="hidden" name="is_active" value="0">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                           {{ old('is_active', '1') === '1' ? 'checked' : '' }} id="isActive">
                                    <label class="form-check-label" for="isActive">Active</label>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('super-admin.plans.index') }}" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-1"></i> Create Plan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    document.getElementById('planName').addEventListener('input', function() {
        const slug = this.value.toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9-]/g, '');
        document.getElementById('planSlug').value = slug;
    });
</script>
@endsection
