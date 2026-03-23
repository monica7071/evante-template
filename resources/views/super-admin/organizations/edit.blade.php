@extends('super-admin.layout')

@section('title', 'Edit ' . $organization->name)

@section('content')
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="{{ route('super-admin.organizations.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h3 class="fw-bold mb-0">Edit Organization</h3>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8 col-xl-7">
            <div class="card mb-4">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('super-admin.organizations.update', $organization) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            {{-- Name --}}
                            <div class="col-md-6">
                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name', $organization->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Name TH --}}
                            <div class="col-md-6">
                                <label class="form-label">Name (Thai)</label>
                                <input type="text" name="name_th" class="form-control @error('name_th') is-invalid @enderror"
                                       value="{{ old('name_th', $organization->name_th) }}">
                                @error('name_th')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Slug (read-only) --}}
                            <div class="col-md-6">
                                <label class="form-label">Slug</label>
                                <input type="text" class="form-control" value="{{ $organization->slug }}" disabled>
                                <div class="form-text">Slug cannot be changed after creation</div>
                            </div>

                            {{-- Domain --}}
                            <div class="col-md-6">
                                <label class="form-label">Custom Domain</label>
                                <input type="text" name="domain" class="form-control @error('domain') is-invalid @enderror"
                                       value="{{ old('domain', $organization->domain) }}" placeholder="e.g. crm.example.com">
                                @error('domain')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Primary Color --}}
                            <div class="col-md-3">
                                <label class="form-label">Primary Color</label>
                                <div class="d-flex align-items-center gap-2">
                                    <input type="color" name="primary_color" id="orgColor" class="form-control form-control-color"
                                           value="{{ old('primary_color', $organization->primary_color) }}" style="width:50px; height:38px;">
                                    <code id="colorPreview" style="font-size:0.82rem;">{{ $organization->primary_color }}</code>
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
                                                {{ old('plan_id', $organization->plan_id) == $plan->id ? 'selected' : '' }}>
                                            {{ $plan->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('plan_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Plan Info --}}
                            <div class="col-md-3" id="planInfo" style="display:none;">
                                <label class="form-label">Plan Storage</label>
                                <div class="mt-1">
                                    <span class="fw-semibold" id="planStorage">-</span>
                                </div>
                            </div>

                            {{-- Logo --}}
                            <div class="col-md-6">
                                <label class="form-label">Logo</label>
                                @if($organization->logo)
                                    <div class="mb-2">
                                        <img src="{{ asset('storage/' . $organization->logo) }}" alt="" style="height:40px; border-radius:8px;">
                                    </div>
                                @endif
                                <input type="file" name="logo" class="form-control @error('logo') is-invalid @enderror" accept="image/*">
                                <div class="form-text">Leave empty to keep current logo</div>
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
                                           {{ old('is_active', $organization->is_active) ? 'checked' : '' }} id="isActive">
                                    <label class="form-check-label" for="isActive">Active</label>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('super-admin.organizations.index') }}" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-1"></i> Update Organization
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Info Block --}}
            <div class="card">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3" style="color:var(--text-mid);">Organization Info</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="kpi-card-title">Storage Used</div>
                            <div class="storage-bar-wrap mb-1" style="height:8px;">
                                <div class="storage-bar-fill" style="width:{{ $organization->storagePercentage() }}%; background:{{ $organization->storageBarColor() }};"></div>
                            </div>
                            <div style="font-size:0.82rem; font-weight:600;">
                                {{ $organization->storageUsedGB() }} GB / {{ $organization->storageLimitGB() }} GB
                                ({{ $organization->storagePercentage() }}%)
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="kpi-card-title">Total Users</div>
                            <div style="font-size:1.1rem; font-weight:700;">{{ $organization->users_count }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="kpi-card-title">Total Listings</div>
                            <div style="font-size:1.1rem; font-weight:700;">{{ $organization->listings_count }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="kpi-card-title">Created At</div>
                            <div style="font-size:0.88rem;">{{ $organization->created_at->format('d M Y, H:i') }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="kpi-card-title">Total Sales</div>
                            <div style="font-size:1.1rem; font-weight:700;">{{ $organization->sales_count }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    document.getElementById('orgColor').addEventListener('input', function() {
        document.getElementById('colorPreview').textContent = this.value;
    });

    // Plan selection → show storage info
    document.getElementById('planSelect').addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        const infoEl = document.getElementById('planInfo');

        if (this.value) {
            document.getElementById('planStorage').textContent = selected.dataset.storage + ' GB';
            infoEl.style.display = '';
        } else {
            infoEl.style.display = 'none';
        }
    });

    // Trigger on page load
    document.getElementById('planSelect').dispatchEvent(new Event('change'));
</script>
@endsection
