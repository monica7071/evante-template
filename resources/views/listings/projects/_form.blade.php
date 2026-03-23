<div class="row g-3">
    <div class="col-md-12">
        <label for="location_id" class="form-label">Location <span class="text-danger">*</span></label>
        <select class="form-select @error('location_id') is-invalid @enderror" id="location_id" name="location_id" required>
            <option value="">-- Select Location --</option>
            @foreach($locations as $loc)
                <option value="{{ $loc->id }}" {{ old('location_id', $project->location_id ?? '') == $loc->id ? 'selected' : '' }}>
                    {{ $loc->project_name }} ({{ $loc->province }})
                </option>
            @endforeach
        </select>
        @error('location_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-12">
        <label for="name" class="form-label">Building Name <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('name') is-invalid @enderror"
               id="name" name="name"
               value="{{ old('name', $project->name ?? '') }}" required>
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label for="total_floors" class="form-label">Total Floors <span class="text-danger">*</span></label>
        <input type="number" class="form-control @error('total_floors') is-invalid @enderror"
               id="total_floors" name="total_floors"
               value="{{ old('total_floors', $project->total_floors ?? '') }}" min="1" required>
        @error('total_floors') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label for="total_units" class="form-label">Total Units</label>
        <input type="number" class="form-control @error('total_units') is-invalid @enderror"
               id="total_units" name="total_units"
               value="{{ old('total_units', $project->total_units ?? '') }}" min="0">
        @error('total_units') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>
