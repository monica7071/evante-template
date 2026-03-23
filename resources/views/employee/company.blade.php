@extends('layouts.app')

@section('title', 'Company Information')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold mb-0">Company Information</h4>
    </div>

    <div class="card border-0 shadow-sm" style="border-radius:16px;">
        <div class="card-body p-4">
            <form action="{{ route('employee.company.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row g-4">
                    {{-- Logo --}}
                    <div class="col-12">
                        <label class="form-label fw-semibold">Logo</label>
                        <div class="d-flex align-items-center gap-3">
                            @if($company && $company->logo)
                                <img src="{{ asset('storage/' . $company->logo) }}" alt="Logo" class="rounded" style="width:80px;height:80px;object-fit:contain;background:#f8f9fa;padding:8px;">
                            @else
                                <div class="rounded d-flex align-items-center justify-content-center" style="width:80px;height:80px;background:#f2f4f7;">
                                    <i class="bi bi-image text-muted fs-4"></i>
                                </div>
                            @endif
                            <input type="file" name="logo" class="form-control @error('logo') is-invalid @enderror" accept="image/*" style="max-width:300px;">
                            @error('logo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Company Name (EN) <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $company->name ?? '') }}" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Company Name (TH)</label>
                        <input type="text" name="name_th" class="form-control" value="{{ old('name_th', $company->name_th ?? '') }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Tax ID</label>
                        <input type="text" name="tax_id" class="form-control" value="{{ old('tax_id', $company->tax_id ?? '') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Social Security Number</label>
                        <input type="text" name="social_security_number" class="form-control" value="{{ old('social_security_number', $company->social_security_number ?? '') }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Phone</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $company->phone ?? '') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $company->email ?? '') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Website</label>
                        <input type="text" name="website" class="form-control" value="{{ old('website', $company->website ?? '') }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Established Date</label>
                        <input type="date" name="established_date" class="form-control" value="{{ old('established_date', $company->established_date?->format('Y-m-d') ?? '') }}">
                    </div>

                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Address (EN)</label>
                        <textarea name="address" rows="2" class="form-control">{{ old('address', $company->address ?? '') }}</textarea>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Address (TH)</label>
                        <textarea name="address_th" rows="2" class="form-control">{{ old('address_th', $company->address_th ?? '') }}</textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" rows="3" class="form-control">{{ old('description', $company->description ?? '') }}</textarea>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-dark px-4">
                        <i class="bi bi-check-lg me-1"></i> Save
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
