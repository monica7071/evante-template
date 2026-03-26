@extends('super-admin.layout')

@section('title', 'Edit ' . $plan->name)

@section('content')
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="{{ route('super-admin.plans.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h3 class="fw-bold mb-0">Edit Plan</h3>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8 col-xl-7">
            <div class="card mb-4">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('super-admin.plans.update', $plan) }}">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            {{-- Name --}}
                            <div class="col-md-6">
                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name', $plan->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Slug --}}
                            <div class="col-md-6">
                                <label class="form-label">Slug <span class="text-danger">*</span></label>
                                <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror"
                                       value="{{ old('slug', $plan->slug) }}" required>
                                @error('slug')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Storage Limit --}}
                            <div class="col-md-6">
                                <label class="form-label">Storage Limit (GB) <span class="text-danger">*</span></label>
                                <input type="number" name="storage_limit_gb" class="form-control @error('storage_limit_gb') is-invalid @enderror"
                                       value="{{ old('storage_limit_gb', $plan->storageLimitInGB()) }}" min="1" step="0.01" required>
                                @error('storage_limit_gb')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Price --}}
                            <div class="col-md-6">
                                <label class="form-label">Price <span class="text-danger">*</span></label>
                                <input type="number" name="price" class="form-control @error('price') is-invalid @enderror"
                                       value="{{ old('price', $plan->price) }}" min="0" step="0.01" required>
                                @error('price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Description --}}
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $plan->description) }}</textarea>
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
                                           {{ old('is_active', $plan->is_active) ? 'checked' : '' }} id="isActive">
                                    <label class="form-check-label" for="isActive">Active</label>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('super-admin.plans.index') }}" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-1"></i> Update Plan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Info Block --}}
            <div class="card">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3" style="color:var(--text-mid);">Plan Info</h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="kpi-card-title">Organizations Using</div>
                            <div style="font-size:1.1rem; font-weight:700;">{{ $plan->organizations_count }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="kpi-card-title">Storage Limit</div>
                            <div style="font-size:1.1rem; font-weight:700;">{{ $plan->storageLimitInGB() }} GB</div>
                        </div>
                        <div class="col-md-4">
                            <div class="kpi-card-title">Created At</div>
                            <div style="font-size:0.88rem;">{{ $plan->created_at->format('d M Y, H:i') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
