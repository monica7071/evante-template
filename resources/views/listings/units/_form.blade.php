{{-- ══════════ Section: Location & Building ══════════ --}}
<div class="form-section">
    <div class="form-section-header">
        <i class="bi bi-geo-alt"></i> Location
    </div>
    <div class="row g-3">
        <div class="col-md-6">
            <label for="location_id" class="form-label">Project <span class="text-danger">*</span></label>
            <select class="form-select @error('location_id') is-invalid @enderror" id="location_id" name="location_id" required>
                <option value="">-- Select Location --</option>
                @foreach($locations as $loc)
                    <option value="{{ $loc->id }}" {{ old('location_id', $unit->location_id ?? '') == $loc->id ? 'selected' : '' }}>
                        {{ $loc->project_name }} ({{ $loc->province }})
                    </option>
                @endforeach
            </select>
            @error('location_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-6">
            <label for="project_id" class="form-label">Tower <span class="text-danger">*</span></label>
            <select class="form-select @error('project_id') is-invalid @enderror" id="project_id" name="project_id" required>
                <option value="">-- Select Project --</option>
                @foreach($projects as $proj)
                    @php
                        $projectDisplay = trim($proj->name);
                        $lastToken = \Illuminate\Support\Str::of($projectDisplay)->afterLast(' ')->trim();
                        $latinToken = preg_replace('/[^A-Za-z0-9]/', '', $lastToken);
                        if ($latinToken === '') {
                            $latinToken = preg_replace('/[^A-Za-z0-9]/', '', $projectDisplay);
                        }
                        $buildingCode = $latinToken !== '' ? $latinToken : preg_replace('/\s+/', '', $projectDisplay);
                    @endphp
                    <option value="{{ $proj->id }}"
                            data-location="{{ $proj->location_id }}"
                            data-building-code="{{ $buildingCode }}"
                            {{ old('project_id', $unit->project_id ?? '') == $proj->id ? 'selected' : '' }}>
                        {{ $proj->name }}
                    </option>
                @endforeach
            </select>
            @error('project_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>
</div>

{{-- ══════════ Section: Unit Information ══════════ --}}
<div class="form-section">
    <div class="form-section-header">
        <i class="bi bi-door-open"></i> Unit Information
    </div>
    <div class="row g-3">
        <div class="col-md-4">
            <label for="room_number" class="form-label">Room no <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('room_number') is-invalid @enderror"
                   id="room_number" name="room_number"
                   value="{{ old('room_number', $unit->room_number ?? '') }}" placeholder="e.g. 01, 02" required>
            @error('room_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-4">
            <label for="unit_code" class="form-label">Unit Code</label>
            <input type="text" class="form-control @error('unit_code') is-invalid @enderror"
                   id="unit_code" name="unit_code"
                   value="{{ old('unit_code', $unit->unit_code ?? '') }}" placeholder="e.g. A101, A102">
            @error('unit_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-4">
            <label for="floor" class="form-label">Floor</label>
            <input type="number" class="form-control @error('floor') is-invalid @enderror"
                   id="floor" name="floor"
                   value="{{ old('floor', $unit->floor ?? '') }}" placeholder="e.g. 1, 2" min="0">
            @error('floor') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        @php
            $bedroomOptions = ['1 Bed Plus', '2 Beds Smart', '2 Beds Plus', '1 Bed Smart', 'Grand Retail'];
            $selectedBedroom = old('bedrooms', $unit->bedrooms ?? '');
        @endphp
        <div class="col-md-4">
            <label for="bedrooms" class="form-label">Bedrooms</label>
            <select class="form-select @error('bedrooms') is-invalid @enderror" id="bedrooms" name="bedrooms">
                <option value="">-- Select --</option>
                @foreach($bedroomOptions as $option)
                    <option value="{{ $option }}" {{ $selectedBedroom === $option ? 'selected' : '' }}>{{ $option }}</option>
                @endforeach
            </select>
            @error('bedrooms') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-4">
            <label for="area" class="form-label">Area (sqm)</label>
            <input type="number" step="0.01" class="form-control @error('area') is-invalid @enderror"
                   id="area" name="area"
                   value="{{ old('area', $unit->area ?? '') }}" min="0">
            @error('area') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-4">
            <label for="unit_type" class="form-label">Unit Type</label>
            <input type="text" class="form-control @error('unit_type') is-invalid @enderror"
                   id="unit_type" name="unit_type"
                   value="{{ old('unit_type', $unit->unit_type ?? '') }}" placeholder="e.g. A, B">
            @error('unit_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>
</div>

{{-- ══════════ Section: Pricing & Status ══════════ --}}
<div class="form-section">
    <div class="form-section-header">
        <i class="bi bi-tag"></i> Pricing & Status
    </div>
    <div class="row g-3">
        @php
            $rawPricePerRoom = old('price_per_room', $unit->price_per_room ?? '');
            $roundedPricePerRoom = $rawPricePerRoom !== '' ? round((float) $rawPricePerRoom) : '';
            $displayPricePerRoom = $roundedPricePerRoom !== '' ? number_format($roundedPricePerRoom) : '';
        @endphp
        <div class="col-md-4">
            <label for="price_per_room_display" class="form-label">Price per Room</label>
            <input type="hidden" id="price_per_room" name="price_per_room" value="{{ $roundedPricePerRoom }}">
            <input type="text" class="form-control @error('price_per_room') is-invalid @enderror"
                   id="price_per_room_display" value="{{ $displayPricePerRoom }}" inputmode="numeric">
            @error('price_per_room') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        @php
            $rawPricePerSqm = old('price_per_sqm', $unit->price_per_sqm ?? '');
            $roundedPricePerSqm = $rawPricePerSqm !== '' ? round((float) $rawPricePerSqm) : '';
            $displayPricePerSqm = $roundedPricePerSqm !== '' ? number_format($roundedPricePerSqm) : '';
        @endphp
        <div class="col-md-4">
            <label for="price_per_sqm" class="form-label">Price per SQM</label>
            <input type="hidden" id="price_per_sqm" name="price_per_sqm" value="{{ $roundedPricePerSqm }}">
            <input type="text" class="form-control" id="price_per_sqm_display" value="{{ $displayPricePerSqm }}">
            @error('price_per_sqm') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-4">
            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                @foreach(['available','reserved','contract','installment','transferred'] as $s)
                    <option value="{{ $s }}" {{ old('status', $unit->status ?? 'available') == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
            @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>
</div>

{{-- ══════════ Section: Financial Breakdown (Collapsible) ══════════ --}}
<div class="form-section">
    <div class="form-section-header form-section-toggle" data-bs-toggle="collapse" data-bs-target="#financialCollapse" role="button">
        <span><i class="bi bi-calculator"></i> Financial Breakdown</span>
        <i class="bi bi-chevron-down collapse-icon"></i>
    </div>
    <div class="collapse {{ old('reservation_deposit', $unit->reservation_deposit ?? '') ? 'show' : '' }}" id="financialCollapse">
        <div class="row g-3 pt-3">
            @php
                $finFields = [
                    'reservation_deposit' => ['label' => 'Reservation fee', 'round' => 'up'],
                    'contract_payment' => ['label' => 'Signing contact', 'round' => 'up'],
                    'installment_15_terms' => ['label' => 'Down payment (TH 9.5%)', 'round' => 'nearest'],
                    'installment_15_terms_en' => ['label' => 'Down payment (EN 27%)', 'round' => 'nearest'],
                    'installment_12_terms' => ['label' => 'Normal down', 'round' => 'nearest'],
                    'special_installment_3_terms' => ['label' => 'Bullet on month', 'round' => 'nearest'],
                    'transfer_amount' => ['label' => 'Transfer (TH 87.5%)', 'round' => 'nearest'],
                    'transfer_amount_en' => ['label' => 'Transfer (EN 70%)', 'round' => 'nearest'],
                    'transfer_fee' => ['label' => 'Transfer fee', 'round' => 'up'],
                    'annual_common_fee' => ['label' => 'Common Fee', 'round' => 'nearest'],
                    'sinking_fund' => ['label' => 'Sinking Fund', 'round' => 'nearest'],
                ];
            @endphp

            @foreach($finFields as $field => $meta)
                @php
                    $rawValue = old($field, $unit->$field ?? '');
                    $roundedValue = $rawValue !== '' ? round((float) $rawValue) : '';
                    $displayValue = $roundedValue !== '' ? number_format($roundedValue) : '';
                @endphp
                <div class="col-md-6">
                    <label for="{{ $field }}" class="form-label">{{ $meta['label'] }}</label>
                    <input type="hidden" id="{{ $field }}" name="{{ $field }}" value="{{ $roundedValue }}">
                    <input type="text" class="form-control" id="{{ $field }}_display" value="{{ $displayValue }}">
                    @error($field) <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            @endforeach

            @php
                $rawUtilityFee = old('utility_fee', $unit->utility_fee ?? '');
                $roundedUtilityFee = $rawUtilityFee !== '' ? round((float) $rawUtilityFee) : '';
                $displayUtilityFee = $roundedUtilityFee !== '' ? number_format($roundedUtilityFee) : '';
            @endphp
            <div class="col-md-6">
                <label for="utility_fee_display" class="form-label">Utility Meter Fees Paid</label>
                <input type="hidden" id="utility_fee" name="utility_fee" value="{{ $roundedUtilityFee }}">
                <input type="text" class="form-control @error('utility_fee') is-invalid @enderror"
                       id="utility_fee_display" value="{{ $displayUtilityFee }}" inputmode="numeric">
                @error('utility_fee') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            @php
                $rawTotalMisc = old('total_misc_fee', $unit->total_misc_fee ?? '');
                $roundedTotalMisc = $rawTotalMisc !== '' ? round((float) $rawTotalMisc) : '';
                $displayTotalMisc = $roundedTotalMisc !== '' ? number_format($roundedTotalMisc) : '';
            @endphp
            <div class="col-md-6">
                <label for="total_misc_fee" class="form-label">Total</label>
                <input type="hidden" id="total_misc_fee" name="total_misc_fee" value="{{ $roundedTotalMisc }}">
                <input type="text" class="form-control" id="total_misc_fee_display" value="{{ $displayTotalMisc }}">
                @error('total_misc_fee') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>
    </div>
</div>

{{-- ══════════ Section: Room Photos ══════════ --}}
<div class="form-section">
    <div class="form-section-header">
        <i class="bi bi-images"></i> Room Photos
        <span class="form-section-badge">Max 10</span>
    </div>

    {{-- Existing room images (edit mode) --}}
    @if(!empty($unit) && $unit->listingImages && $unit->listingImages->count())
        <div class="existing-images-grid mb-3">
            @foreach($unit->listingImages as $img)
                <div class="existing-image-item">
                    <img src="{{ asset('storage/' . $img->image_path) }}" alt="Room photo">
                    <label class="delete-overlay" title="Mark for deletion">
                        <input type="checkbox" name="delete_room_images[]" value="{{ $img->id }}" class="d-none room-img-checkbox">
                        <i class="bi bi-trash"></i>
                    </label>
                </div>
            @endforeach
        </div>
    @endif

    <div class="upload-zone" id="roomImagesZone">
        <input type="file" class="d-none @error('room_images') is-invalid @enderror @error('room_images.*') is-invalid @enderror"
               id="room_images" name="room_images[]" accept="image/*" multiple>
        <div class="upload-zone-content" id="roomImagesPlaceholder">
            <i class="bi bi-cloud-arrow-up"></i>
            <span>Click to upload or drag photos here</span>
            <small>JPG, PNG, WebP &middot; max 4MB each</small>
        </div>
        <div id="room_images_preview" class="upload-preview-grid d-none"></div>
    </div>
    @error('room_images') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
    @error('room_images.*') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
</div>

{{-- ══════════ Section: Floor Plan & Layout ══════════ --}}
<div class="form-section">
    <div class="form-section-header">
        <i class="bi bi-layout-wtf"></i> Floor Plan & Room Layout
    </div>
    <div class="row g-3">
        <div class="col-md-6">
            <label for="floor_plan_image" class="form-label">Floor Plan</label>
            <input type="file" class="form-control @error('floor_plan_image') is-invalid @enderror"
                   id="floor_plan_image" name="floor_plan_image" accept="image/*">
            <small id="floor_plan_image_name" class="text-muted d-block mt-1"></small>
            @if(!empty($unit?->floor_plan_image))
                <div class="current-asset mt-2">
                    <img src="{{ asset('storage/' . $unit->floor_plan_image) }}" alt="Floor Plan">
                    <small>Current floor plan</small>
                </div>
            @endif
            <div id="floor_plan_auto_preview" class="mt-2 d-none">
                <small class="text-success"><i class="bi bi-magic"></i> Auto-assigned from project:</small>
                <img id="floor_plan_auto_img" src="" alt="" class="d-block mt-1 rounded border" style="max-height:80px;max-width:120px;object-fit:cover;">
            </div>
            @error('floor_plan_image') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-6">
            <label for="room_layout_image" class="form-label">Room Layout</label>
            <input type="file" class="form-control @error('room_layout_image') is-invalid @enderror"
                   id="room_layout_image" name="room_layout_image" accept="image/*">
            <small id="room_layout_image_name" class="text-muted d-block mt-1"></small>
            @if(!empty($unit?->room_layout_image))
                <div class="current-asset mt-2">
                    <img src="{{ asset('storage/' . $unit->room_layout_image) }}" alt="Room Layout">
                    <small>Current room layout</small>
                </div>
            @endif
            <div id="room_layout_auto_preview" class="mt-2 d-none">
                <small class="text-success"><i class="bi bi-magic"></i> Auto-assigned from project:</small>
                <img id="room_layout_auto_img" src="" alt="" class="d-block mt-1 rounded border" style="max-height:80px;max-width:120px;object-fit:cover;">
            </div>
            @error('room_layout_image') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>
</div>

<script>
    window._projectImages = {!! json_encode($projectImages) !!};
    window._storageBase   = "{{ asset('storage') }}/";
</script>

@section('styles')
<style>
    /* ── Form sections ── */
    .form-section {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 1.25rem 1.5rem;
        margin-bottom: 1rem;
    }
    .form-section-header {
        font-weight: 700;
        font-size: 0.85rem;
        color: var(--text-dark);
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .form-section-header i:first-child {
        color: var(--primary);
        font-size: 1rem;
    }
    .form-section-badge {
        font-size: 0.65rem;
        font-weight: 600;
        background: var(--primary-muted);
        color: var(--primary);
        padding: 2px 8px;
        border-radius: 20px;
        margin-left: auto;
    }

    /* Collapsible toggle */
    .form-section-toggle {
        cursor: pointer;
        user-select: none;
        margin-bottom: 0;
        justify-content: space-between;
    }
    .form-section-toggle .collapse-icon {
        transition: transform 0.2s;
        color: var(--text-light);
    }
    .form-section-toggle[aria-expanded="true"] .collapse-icon,
    .form-section-toggle:not(.collapsed) .collapse-icon {
        transform: rotate(180deg);
    }

    /* Upload zone */
    .upload-zone {
        border: 2px dashed var(--border);
        border-radius: var(--radius-sm);
        padding: 1.5rem;
        text-align: center;
        cursor: pointer;
        transition: border-color 0.2s, background 0.2s;
    }
    .upload-zone:hover {
        border-color: var(--primary);
        background: var(--primary-muted);
    }
    .upload-zone-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.25rem;
        color: var(--text-light);
    }
    .upload-zone-content i {
        font-size: 1.75rem;
        color: var(--primary);
    }
    .upload-zone-content span {
        font-size: 0.85rem;
        font-weight: 500;
        color: var(--text-mid);
    }
    .upload-zone-content small {
        font-size: 0.72rem;
    }

    /* Upload preview */
    .upload-preview-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        justify-content: center;
    }
    .upload-preview-grid img {
        width: 80px;
        height: 56px;
        object-fit: cover;
        border-radius: 6px;
        border: 1px solid var(--border);
    }

    /* Existing images */
    .existing-images-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
    }
    .existing-image-item {
        position: relative;
        width: 110px;
        height: 76px;
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid var(--border);
    }
    .existing-image-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: opacity 0.2s, filter 0.2s;
    }
    .existing-image-item .delete-overlay {
        position: absolute;
        top: 4px;
        right: 4px;
        width: 24px;
        height: 24px;
        background: rgba(220,53,69,0.85);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        opacity: 0;
        transition: opacity 0.15s;
        color: #fff;
        font-size: 0.7rem;
    }
    .existing-image-item:hover .delete-overlay {
        opacity: 1;
    }
    .existing-image-item.marked-delete img {
        opacity: 0.3;
        filter: grayscale(1);
    }
    .existing-image-item.marked-delete .delete-overlay {
        opacity: 1;
        background: rgba(220,53,69,1);
    }

    /* Current asset thumbnail */
    .current-asset {
        display: inline-block;
    }
    .current-asset img {
        max-height: 72px;
        max-width: 110px;
        object-fit: cover;
        border-radius: 6px;
        border: 1px solid var(--border);
        display: block;
    }
    .current-asset small {
        font-size: 0.7rem;
        color: var(--text-light);
        margin-top: 4px;
        display: block;
    }
</style>
@endsection

@section('scripts')
<script>
    (function () {
    const locationSelect = document.getElementById('location_id');
    const projectSelect = document.getElementById('project_id');
    const pricePerRoomInput = document.getElementById('price_per_room');
    const pricePerRoomDisplay = document.getElementById('price_per_room_display');
    const areaInput = document.getElementById('area');
    const utilityFeeInput = document.getElementById('utility_fee');
    const utilityFeeDisplay = document.getElementById('utility_fee_display');
    const floorInput = document.getElementById('floor');
    const roomNumberInput = document.getElementById('room_number');
    const unitCodeInput = document.getElementById('unit_code');
    const floorPlanInput = document.getElementById('floor_plan_image');
    const roomLayoutInput = document.getElementById('room_layout_image');
    const floorPlanName = document.getElementById('floor_plan_image_name');
    const roomLayoutName = document.getElementById('room_layout_image_name');
    const getBuildingCode = () => {
        if (!projectSelect) return '';
        const option = projectSelect.options[projectSelect.selectedIndex];
        if (!option) return '';
        const code = option.dataset.buildingCode || option.text.trim();
        return code ? code.replace(/\s+/g, '') : '';
    };
    const buildUnitCodeString = () => {
        const parts = [
            getBuildingCode(),
            floorInput?.value?.trim() || '',
            roomNumberInput?.value?.trim() || '',
        ].filter(Boolean);
        return parts.join('');
    };
    if (unitCodeInput) {
        const initialGenerated = buildUnitCodeString();
        if (initialGenerated && unitCodeInput.value.trim() === initialGenerated) {
            unitCodeInput.dataset.autogen = initialGenerated;
        } else if (!unitCodeInput.value.trim()) {
            unitCodeInput.dataset.autogen = '';
        }
    }

    const numericFields = {
        price_per_sqm: {
            hidden: document.getElementById('price_per_sqm'),
            display: document.getElementById('price_per_sqm_display'),
            round: 'nearest',
        },
        reservation_deposit: {
            hidden: document.getElementById('reservation_deposit'),
            display: document.getElementById('reservation_deposit_display'),
            round: 'up',
        },
        contract_payment: {
            hidden: document.getElementById('contract_payment'),
            display: document.getElementById('contract_payment_display'),
            round: 'up',
        },
        installment_15_terms: {
            hidden: document.getElementById('installment_15_terms'),
            display: document.getElementById('installment_15_terms_display'),
            round: 'nearest',
        },
        installment_15_terms_en: {
            hidden: document.getElementById('installment_15_terms_en'),
            display: document.getElementById('installment_15_terms_en_display'),
            round: 'nearest',
        },
        installment_12_terms: {
            hidden: document.getElementById('installment_12_terms'),
            display: document.getElementById('installment_12_terms_display'),
            round: 'nearest',
        },
        special_installment_3_terms: {
            hidden: document.getElementById('special_installment_3_terms'),
            display: document.getElementById('special_installment_3_terms_display'),
            round: 'nearest',
        },
        transfer_amount: {
            hidden: document.getElementById('transfer_amount'),
            display: document.getElementById('transfer_amount_display'),
            round: 'nearest',
        },
        transfer_amount_en: {
            hidden: document.getElementById('transfer_amount_en'),
            display: document.getElementById('transfer_amount_en_display'),
            round: 'nearest',
        },
        transfer_fee: {
            hidden: document.getElementById('transfer_fee'),
            display: document.getElementById('transfer_fee_display'),
            round: 'up',
        },
        annual_common_fee: {
            hidden: document.getElementById('annual_common_fee'),
            display: document.getElementById('annual_common_fee_display'),
            round: 'nearest',
        },
        sinking_fund: {
            hidden: document.getElementById('sinking_fund'),
            display: document.getElementById('sinking_fund_display'),
            round: 'nearest',
        },
        total_misc_fee: {
            hidden: document.getElementById('total_misc_fee'),
            display: document.getElementById('total_misc_fee_display'),
            round: 'nearest',
        },
    };

    function filterProjectsByLocation() {
        const locId = locationSelect.value;
        Array.from(projectSelect.options).forEach(function(opt) {
            if (!opt.value) return;
            opt.style.display = (!locId || opt.dataset.location === locId) ? '' : 'none';
        });
        const current = projectSelect.options[projectSelect.selectedIndex];
        if (current && current.style.display === 'none') {
            projectSelect.value = '';
        }
        autoFillUnitCode();
    }

    function autoFillUnitCode(forceFill = false) {
        if (!unitCodeInput) return;
        const generated = buildUnitCodeString();
        if (!generated) return;

        const current = unitCodeInput.value.trim();
        const wasGenerated = !current || unitCodeInput.dataset.autogen === current;
        if (!forceFill && !wasGenerated) {
            return;
        }

        unitCodeInput.value = generated;
        unitCodeInput.dataset.autogen = generated;
    }

    function formatNumber(value) {
        return value.toLocaleString('en-US', { maximumFractionDigits: 0 });
    }

    function roundValue(value, mode = 'nearest') {
        if (!Number.isFinite(value)) return null;
        const epsilon = Number.EPSILON;

        if (mode === 'up') {
            return Math.ceil(value + epsilon);
        }
        if (mode === 'down') {
            return Math.floor(value - epsilon);
        }
        return Math.round(value + epsilon);
    }

    function setNumericField(key, value) {
        const field = numericFields[key];
        if (!field) return;

        if (!Number.isFinite(value)) {
            field.hidden.value = '';
            field.display.value = '';
            return;
        }

        const rounded = roundValue(value, field.round);
        if (rounded === null) {
            field.hidden.value = '';
            field.display.value = '';
            return;
        }
        field.hidden.value = rounded;
        field.display.value = formatNumber(rounded);
    }

    function updateFinancials() {
        const price = parseFloat(pricePerRoomInput.value);
        const area = parseFloat(areaInput.value);
        const utility = parseFloat(utilityFeeInput.value);

        const pricePerSqm = (price > 0 && area > 0) ? price / area : NaN;
        setNumericField('price_per_sqm', pricePerSqm);

        const reservation = price * 0.0025;
        const contractPay = price * 0.0275;
        const installment15 = price * 0.095;
        const installment15En = price * 0.27;
        const installment12 = installment15 > 0 ? 0.8 * (installment15 / 15) : NaN;
        const special3 = installment15 > 0 ? (installment15 - (installment12 * 12)) / 3 : NaN;
        const transferAmount = price * 0.875;
        const transferAmountEn = price * 0.70;
        const transferFee = (price * 0.02) / 2;
        const annualCommon = area > 0 ? 55 * area * 12 : NaN;
        const sinkingFund = area > 0 ? area * 650 : NaN;
        const totalMisc = transferFee + (annualCommon || 0) + (sinkingFund || 0) + (utility || 0);

        setNumericField('reservation_deposit', reservation);
        setNumericField('contract_payment', contractPay);
        setNumericField('installment_15_terms', installment15);
        setNumericField('installment_15_terms_en', installment15En);
        setNumericField('installment_12_terms', installment12);
        setNumericField('special_installment_3_terms', special3);
        setNumericField('transfer_amount', transferAmount);
        setNumericField('transfer_amount_en', transferAmountEn);
        setNumericField('transfer_fee', transferFee);
        setNumericField('annual_common_fee', annualCommon);
        setNumericField('sinking_fund', sinkingFund);
        setNumericField('total_misc_fee', totalMisc);
    }

    function sanitizeNumberInput(value) {
        return value.replace(/,/g, '').replace(/[^0-9.]/g, '');
    }

    function syncPricePerRoomFromDisplay(triggerUpdate = true) {
        const sanitized = sanitizeNumberInput(pricePerRoomDisplay.value);
        const numericValue = sanitized ? Math.round(parseFloat(sanitized)) : NaN;

        if (Number.isFinite(numericValue)) {
            pricePerRoomInput.value = numericValue;
            if (triggerUpdate) {
                pricePerRoomDisplay.value = formatNumber(numericValue);
            }
        } else {
            pricePerRoomInput.value = '';
        }

        if (triggerUpdate) {
            updateFinancials();
        }
    }

    function attachFileNameDisplay(inputEl, targetEl) {
        if (!inputEl || !targetEl) return;
        const updateName = () => {
            if (inputEl.files && inputEl.files.length) {
                targetEl.textContent = `Selected: ${inputEl.files[0].name}`;
            } else {
                targetEl.textContent = '';
            }
        };
        inputEl.addEventListener('change', updateName);
    }

    locationSelect.addEventListener('change', filterProjectsByLocation);
    roomNumberInput?.addEventListener('input', () => autoFillUnitCode());
    unitCodeInput?.addEventListener('input', () => {
        const current = unitCodeInput.value.trim();
        const generated = buildUnitCodeString();
        if (!current) {
            unitCodeInput.dataset.autogen = '';
            autoFillUnitCode();
            return;
        }
        if (generated && current === generated) {
            unitCodeInput.dataset.autogen = generated;
        } else {
            unitCodeInput.dataset.autogen = '';
        }
    });
    autoFillUnitCode();
    pricePerRoomDisplay.addEventListener('input', () => {
        const caretPos = pricePerRoomDisplay.selectionStart;
        const beforeLength = pricePerRoomDisplay.value.length;
        const sanitized = sanitizeNumberInput(pricePerRoomDisplay.value);
        pricePerRoomDisplay.value = sanitized;
        pricePerRoomInput.value = sanitized ? Math.round(parseFloat(sanitized)) : '';
        updateFinancials();
        const afterLength = pricePerRoomDisplay.value.length;
        pricePerRoomDisplay.setSelectionRange(caretPos - (beforeLength - afterLength), caretPos - (beforeLength - afterLength));
    });
    pricePerRoomDisplay.addEventListener('blur', () => {
        syncPricePerRoomFromDisplay(true);
    });

    utilityFeeDisplay.addEventListener('input', () => {
        const caretPos = utilityFeeDisplay.selectionStart;
        const beforeLength = utilityFeeDisplay.value.length;
        const sanitized = sanitizeNumberInput(utilityFeeDisplay.value);
        utilityFeeDisplay.value = sanitized;
        utilityFeeInput.value = sanitized ? Math.round(parseFloat(sanitized)) : '';
        updateFinancials();
        const afterLength = utilityFeeDisplay.value.length;
        utilityFeeDisplay.setSelectionRange(caretPos - (beforeLength - afterLength), caretPos - (beforeLength - afterLength));
    });
    utilityFeeDisplay.addEventListener('blur', () => {
        const sanitized = sanitizeNumberInput(utilityFeeDisplay.value);
        const numericValue = sanitized ? Math.round(parseFloat(sanitized)) : NaN;
        if (Number.isFinite(numericValue)) {
            utilityFeeInput.value = numericValue;
            utilityFeeDisplay.value = formatNumber(numericValue);
        } else {
            utilityFeeInput.value = '';
            utilityFeeDisplay.value = '';
        }
    });
    areaInput?.addEventListener('input', updateFinancials);
    utilityFeeInput?.addEventListener('input', updateFinancials);

    const unitTypeInput       = document.getElementById('unit_type');
    const floorPlanPreview    = document.getElementById('floor_plan_auto_preview');
    const floorPlanAutoImg    = document.getElementById('floor_plan_auto_img');
    const roomLayoutPreview   = document.getElementById('room_layout_auto_preview');
    const roomLayoutAutoImg   = document.getElementById('room_layout_auto_img');

    function updateImagePreviews() {
        const projectId = projectSelect?.value;
        const floor     = floorInput?.value?.trim();
        const unitType  = unitTypeInput?.value?.trim().toUpperCase();

        if (projectId && floor && !floorPlanInput?.files?.length) {
            const path = window._projectImages?.floor_plan?.[projectId]?.[floor];
            if (path) {
                floorPlanAutoImg.src = window._storageBase + path;
                floorPlanPreview.classList.remove('d-none');
            } else {
                floorPlanPreview.classList.add('d-none');
            }
        } else {
            floorPlanPreview.classList.add('d-none');
        }

        if (unitType && !roomLayoutInput?.files?.length) {
            const path = window._projectImages?.room_layout?.[unitType];
            if (path) {
                roomLayoutAutoImg.src = window._storageBase + path;
                roomLayoutPreview.classList.remove('d-none');
            } else {
                roomLayoutPreview.classList.add('d-none');
            }
        } else {
            roomLayoutPreview.classList.add('d-none');
        }
    }

    projectSelect?.addEventListener('change', () => { autoFillUnitCode(); updateImagePreviews(); });
    floorInput?.addEventListener('input', () => { autoFillUnitCode(); updateImagePreviews(); });
    unitTypeInput?.addEventListener('input', updateImagePreviews);
    floorPlanInput?.addEventListener('change', () => { updateImagePreviews(); });
    roomLayoutInput?.addEventListener('change', () => { updateImagePreviews(); });

    filterProjectsByLocation();
    updateFinancials();
    autoFillUnitCode();
    updateImagePreviews();
    attachFileNameDisplay(floorPlanInput, floorPlanName);
    attachFileNameDisplay(roomLayoutInput, roomLayoutName);

    // ── Upload zone click-to-browse ──
    const uploadZone = document.getElementById('roomImagesZone');
    const roomImagesInput = document.getElementById('room_images');
    const roomImagesPreview = document.getElementById('room_images_preview');
    const roomImagesPlaceholder = document.getElementById('roomImagesPlaceholder');

    if (uploadZone && roomImagesInput) {
        uploadZone.addEventListener('click', () => roomImagesInput.click());

        roomImagesInput.addEventListener('change', function() {
            roomImagesPreview.innerHTML = '';
            const files = Array.from(this.files).slice(0, 10);
            if (files.length) {
                roomImagesPlaceholder.classList.add('d-none');
                roomImagesPreview.classList.remove('d-none');
                files.forEach(function(file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        roomImagesPreview.appendChild(img);
                    };
                    reader.readAsDataURL(file);
                });
            } else {
                roomImagesPlaceholder.classList.remove('d-none');
                roomImagesPreview.classList.add('d-none');
            }
        });

        // Drag & drop
        uploadZone.addEventListener('dragover', (e) => { e.preventDefault(); uploadZone.style.borderColor = 'var(--primary)'; uploadZone.style.background = 'var(--primary-muted)'; });
        uploadZone.addEventListener('dragleave', () => { uploadZone.style.borderColor = ''; uploadZone.style.background = ''; });
        uploadZone.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadZone.style.borderColor = '';
            uploadZone.style.background = '';
            if (e.dataTransfer.files.length) {
                roomImagesInput.files = e.dataTransfer.files;
                roomImagesInput.dispatchEvent(new Event('change'));
            }
        });
    }

    // ── Room image deletion toggle ──
    document.querySelectorAll('.room-img-checkbox').forEach(function(cb) {
        cb.addEventListener('change', function() {
            const container = this.closest('.existing-image-item');
            container.classList.toggle('marked-delete', this.checked);
        });
    });
    })();
</script>
@endsection
