<div class="row g-3">
    <div class="col-12">
        <label for="project_name" class="form-label">Project Name <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('project_name') is-invalid @enderror"
               id="project_name" name="project_name"
               value="{{ old('project_name', $location->project_name ?? '') }}" required>
        @error('project_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <label for="address" class="form-label">Address</label>
        <textarea class="form-control @error('address') is-invalid @enderror"
                  id="address" name="address" rows="3">{{ old('address', $location->address ?? '') }}</textarea>
        @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label for="location_province" class="form-label">Province <span class="text-danger">*</span></label>
        <select id="location_province" name="province"
                class="form-select @error('province') is-invalid @enderror"
                data-selected="{{ old('province', $location->province ?? '') }}" required>
            <option value="">Loading...</option>
        </select>
        @error('province') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label for="location_district" class="form-label">District</label>
        <select id="location_district" name="district"
                class="form-select @error('district') is-invalid @enderror"
                data-selected="{{ old('district', $location->district ?? '') }}">
            <option value="">-- เลือกจังหวัดก่อน --</option>
        </select>
        @error('district') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label for="location_subdistrict" class="form-label">Subdistrict</label>
        <select id="location_subdistrict" name="subdistrict"
                class="form-select @error('subdistrict') is-invalid @enderror"
                data-selected="{{ old('subdistrict', $location->subdistrict ?? '') }}">
            <option value="">-- เลือกอำเภอก่อน --</option>
        </select>
        @error('subdistrict') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label for="location_postal_code" class="form-label">Postal Code</label>
        <input type="text" id="location_postal_code" name="postal_code"
               class="form-control @error('postal_code') is-invalid @enderror"
               value="{{ old('postal_code', $location->postal_code ?? '') }}" maxlength="10">
        @error('postal_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>
