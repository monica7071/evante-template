@extends('super-admin.layout')

@section('title', 'Create Organization')

@section('content')
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="{{ route('super-admin.organizations.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h3 class="fw-bold mb-0">Create New Organization</h3>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8 col-xl-7">
            <div class="card">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('super-admin.organizations.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="row g-3">
                            {{-- Name --}}
                            <div class="col-md-6">
                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="orgName" class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Name TH --}}
                            <div class="col-md-6">
                                <label class="form-label">Name (Thai)</label>
                                <input type="text" name="name_th" class="form-control @error('name_th') is-invalid @enderror"
                                       value="{{ old('name_th') }}">
                                @error('name_th')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Slug --}}
                            <div class="col-md-6">
                                <label class="form-label">Slug <span class="text-danger">*</span></label>
                                <input type="text" name="slug" id="orgSlug" class="form-control @error('slug') is-invalid @enderror"
                                       value="{{ old('slug') }}" required>
                                <div class="form-text">URL-friendly identifier (auto-generated from name)</div>
                                @error('slug')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Primary Color --}}
                            <div class="col-md-3">
                                <label class="form-label">Primary Color</label>
                                <div class="d-flex align-items-center gap-2">
                                    <input type="color" name="primary_color" id="orgColor" class="form-control form-control-color"
                                           value="{{ old('primary_color', '#2A8B92') }}" style="width:50px; height:38px;">
                                    <code id="colorPreview" style="font-size:0.82rem;">#2A8B92</code>
                                </div>
                            </div>

                            {{-- Plan --}}
                            <div class="col-md-6">
                                <label class="form-label">Plan <span class="text-danger">*</span></label>
                                <select name="plan_id" id="planSelect" class="form-select @error('plan_id') is-invalid @enderror" required>
                                    <option value="">-- Select Plan --</option>
                                    @foreach($plans as $plan)
                                        <option value="{{ $plan->id }}"
                                                data-storage="{{ $plan->storageLimitInGB() }}"
                                                data-price="{{ number_format($plan->price, 2) }}"
                                                {{ old('plan_id') == $plan->id ? 'selected' : '' }}>
                                            {{ $plan->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('plan_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Plan Info --}}
                            <div class="col-md-6" id="planInfo" style="display:none;">
                                <label class="form-label">Plan Details</label>
                                <div class="d-flex gap-3 mt-1">
                                    <div>
                                        <span class="text-muted" style="font-size:0.78rem;">Storage:</span>
                                        <span class="fw-semibold" id="planStorage">-</span>
                                    </div>
                                    <div>
                                        <span class="text-muted" style="font-size:0.78rem;">Price:</span>
                                        <span class="fw-semibold" id="planPrice">-</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Logo --}}
                            <div class="col-md-6">
                                <label class="form-label">Logo</label>
                                <input type="file" name="logo" class="form-control @error('logo') is-invalid @enderror" accept="image/*">
                                @error('logo')
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
                            <a href="{{ route('super-admin.organizations.index') }}" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-1"></i> Create Organization
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
    // Auto-generate slug from name
    document.getElementById('orgName').addEventListener('input', function() {
        const slug = this.value.toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9-]/g, '');
        document.getElementById('orgSlug').value = slug;
    });

    // Color preview
    document.getElementById('orgColor').addEventListener('input', function() {
        document.getElementById('colorPreview').textContent = this.value;
    });

    // Plan selection → show storage & price
    document.getElementById('planSelect').addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        const infoEl = document.getElementById('planInfo');

        if (this.value) {
            document.getElementById('planStorage').textContent = selected.dataset.storage + ' GB';
            document.getElementById('planPrice').textContent = selected.dataset.price;
            infoEl.style.display = '';
        } else {
            infoEl.style.display = 'none';
        }
    });

    // Trigger on page load if old value is selected
    document.getElementById('planSelect').dispatchEvent(new Event('change'));
</script>
@endsection
