@extends('layouts.app')

@section('title', 'Make ' . $formTitle . ' — #' . $sale->sale_number)

@section('styles')
<style>
    /* ── Form sections (matching listing form) ── */
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

    /* ── ID Type toggle ── */
    .id-type-toggle {
        display: inline-flex;
        background: var(--cream, #f5f5f0);
        border-radius: 8px;
        padding: 3px;
        gap: 3px;
    }
    .id-type-option {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 16px;
        border-radius: 6px;
        font-size: 0.82rem;
        font-weight: 500;
        color: var(--text-mid, #666);
        cursor: pointer;
        transition: all 0.2s;
        user-select: none;
        margin: 0;
    }
    .id-type-option input[type="radio"] { display: none; }
    .id-type-option:hover { color: var(--text-dark, #333); }
    .id-type-option.active {
        background: #fff;
        color: var(--primary, #2A8B92);
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        font-weight: 600;
    }
    .id-type-option i { font-size: 0.9rem; }

    /* ── Page header ── */
    .page-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.25rem;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    .page-top h4 {
        font-family: 'Playfair Display', serif;
        font-weight: 700;
        margin: 0;
    }
    .sale-badge {
        background: var(--primary-muted);
        color: var(--primary);
        font-weight: 600;
        font-size: 0.78rem;
        padding: 4px 12px;
        border-radius: 20px;
        margin-left: 0.75rem;
    }
    .status-pill {
        font-size: 0.72rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        padding: 4px 12px;
        border-radius: 20px;
        background: var(--cream);
        color: var(--text-mid);
    }

    /* ── Unit summary ── */
    .unit-summary-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 0.75rem;
    }
    @media (max-width: 768px) {
        .unit-summary-grid { grid-template-columns: repeat(2, 1fr); }
    }
    .summary-item .label {
        font-size: 0.68rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-light);
        margin-bottom: 2px;
    }
    .summary-item .value {
        font-size: 0.9rem;
        font-weight: 600;
        color: var(--text-dark);
    }

    /* ── Contract step indicator ── */
    .step-indicator {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }
    .step-dot {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        border: 2px solid var(--border);
        background: transparent;
        color: var(--text-light);
        font-weight: 700;
        font-size: 0.82rem;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
    }
    .step-dot.active {
        background: var(--primary);
        border-color: var(--primary);
        color: #fff;
        box-shadow: 0 2px 8px rgba(42,139,146,0.3);
    }
    .step-dot:hover { opacity: 0.8; }
    .step-line {
        width: 32px;
        height: 2px;
        background: var(--border);
        border-radius: 1px;
    }

    /* ── Section titles inside form sections ── */
    .section-title {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--text-light);
        font-weight: 700;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid var(--border);
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }
    .section-title i { color: var(--primary); }

    .form-label {
        font-weight: 600;
        font-size: 0.8rem;
        color: var(--text-mid);
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    /* ── Contract steps ── */
    .contract-step { display: none; }
    .contract-step.active { display: block; }

    /* ── Validation ── */
    .field-error {
        color: #dc2626;
        font-size: 0.78rem;
    }
    input.is-invalid,
    select.is-invalid,
    textarea.is-invalid {
        border-color: #dc2626 !important;
        box-shadow: 0 0 0 1px rgba(220, 38, 38, 0.08);
    }

    /* ── Footer buttons ── */
    .form-footer {
        display: flex;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 0.75rem;
        margin-top: 0.5rem;
        padding-top: 1rem;
    }

    @media (max-width: 768px) {
        .page-top { flex-wrap: wrap; gap: 0.5rem; }
        .page-top .d-flex.align-items-center.gap-2 { width: 100%; justify-content: flex-end; }
    }
</style>
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-xl-9 col-lg-10">

            {{-- Page header --}}
            <div class="page-top">
                <div class="d-flex align-items-center">
                    <h4>Make {{ $formTitle }}</h4>
                    <span class="sale-badge">#{{ $sale->sale_number }}</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="status-pill">{{ $statusFlow[$sale->status]['label'] }}</span>
                    <a href="{{ route('buy-sale.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Back
                    </a>
                </div>
            </div>

            {{-- Validation alert --}}
            <div class="alert alert-danger mb-3 rounded-3" id="formValidationAlert" style="display:none;"></div>

            {{-- Unit Summary --}}
            <div class="form-section">
                <div class="form-section-header">
                    <i class="bi bi-building"></i> Unit Summary
                    @if($type === 'contract')
                        <div class="ms-auto step-indicator">
                            <button class="step-dot active" type="button" data-step="1">1</button>
                            <div class="step-line"></div>
                            <button class="step-dot" type="button" data-step="2">2</button>
                        </div>
                    @endif
                </div>
                <div class="unit-summary-grid">
                    <div class="summary-item">
                        <div class="label">Project</div>
                        <div class="value">{{ $sale->listing->project->location->project_name ?? '-' }}</div>
                    </div>
                    <div class="summary-item">
                        <div class="label">Room</div>
                        <div class="value">{{ $sale->listing->room_number ?? '-' }}</div>
                    </div>
                    <div class="summary-item">
                        <div class="label">Floor</div>
                        <div class="value">{{ $sale->listing->floor ?? '-' }}</div>
                    </div>
                    <div class="summary-item">
                        <div class="label">Type</div>
                        <div class="value">{{ $sale->listing->unit_type ?? '-' }}</div>
                    </div>
                    <div class="summary-item">
                        <div class="label">Price</div>
                        <div class="value">{{ $sale->listing->price_per_room ? number_format($sale->listing->price_per_room, 0) . ' THB' : '-' }}</div>
                    </div>
                </div>
            </div>

            <form action="{{ route('buy-sale.advance', $sale) }}" method="POST" autocomplete="off">
                @csrf

                @if($type === 'reserved')
                    {{-- ══════════ Reservation Form ══════════ --}}
                    <div class="form-section">
                        <div class="form-section-header">
                            <i class="bi bi-person"></i> Customer Information
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" name="reservation_first_name" class="form-control" value="{{ old('reservation_first_name', $sale->reservation_data['first_name'] ?? '') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" name="reservation_last_name" class="form-control" value="{{ old('reservation_last_name', $sale->reservation_data['last_name'] ?? '') }}" required>
                            </div>
                            @php
                                $idType = old('reservation_id_type', $sale->reservation_data['id_type'] ?? 'id_card');
                            @endphp
                            <div class="col-12">
                                <label class="form-label">ID / Passport <span class="text-danger">*</span></label>
                                <div class="id-type-toggle mb-2">
                                    <label class="id-type-option {{ $idType === 'id_card' ? 'active' : '' }}">
                                        <input type="radio" name="reservation_id_type" id="id_type_card" value="id_card" {{ $idType === 'id_card' ? 'checked' : '' }}>
                                        <i class="bi bi-person-vcard"></i> Identification Number
                                    </label>
                                    <label class="id-type-option {{ $idType === 'passport' ? 'active' : '' }}">
                                        <input type="radio" name="reservation_id_type" id="id_type_passport" value="passport" {{ $idType === 'passport' ? 'checked' : '' }}>
                                        <i class="bi bi-globe2"></i> Passport Number
                                    </label>
                                </div>
                                <div class="row g-3">
                                    <div class="{{ $idType === 'passport' ? 'col-md-6' : 'col-12' }}" id="id_number_col">
                                        <input type="text" name="reservation_id_number" id="reservation_id_number" class="form-control"
                                               value="{{ old('reservation_id_number', $sale->reservation_data['id_number'] ?? '') }}"
                                               maxlength="{{ $idType === 'passport' ? 8 : 13 }}"
                                               placeholder="{{ $idType === 'passport' ? 'e.g. AB123456' : 'e.g. 1234567890123' }}" required>
                                    </div>
                                    @php
                                        $nationalities = [
                                            'Afghan' => '🇦🇫', 'Albanian' => '🇦🇱', 'Algerian' => '🇩🇿', 'American' => '🇺🇸', 'Andorran' => '🇦🇩',
                                            'Angolan' => '🇦🇴', 'Argentine' => '🇦🇷', 'Armenian' => '🇦🇲', 'Australian' => '🇦🇺', 'Austrian' => '🇦🇹',
                                            'Azerbaijani' => '🇦🇿', 'Bahamian' => '🇧🇸', 'Bahraini' => '🇧🇭', 'Bangladeshi' => '🇧🇩', 'Barbadian' => '🇧🇧',
                                            'Belarusian' => '🇧🇾', 'Belgian' => '🇧🇪', 'Belizean' => '🇧🇿', 'Beninese' => '🇧🇯', 'Bhutanese' => '🇧🇹',
                                            'Bolivian' => '🇧🇴', 'Bosnian' => '🇧🇦', 'Brazilian' => '🇧🇷', 'British' => '🇬🇧', 'Bruneian' => '🇧🇳',
                                            'Bulgarian' => '🇧🇬', 'Burkinabe' => '🇧🇫', 'Burmese' => '🇲🇲', 'Burundian' => '🇧🇮', 'Cambodian' => '🇰🇭',
                                            'Cameroonian' => '🇨🇲', 'Canadian' => '🇨🇦', 'Cape Verdean' => '🇨🇻', 'Central African' => '🇨🇫', 'Chadian' => '🇹🇩',
                                            'Chilean' => '🇨🇱', 'Chinese' => '🇨🇳', 'Colombian' => '🇨🇴', 'Comorian' => '🇰🇲', 'Congolese' => '🇨🇬',
                                            'Costa Rican' => '🇨🇷', 'Croatian' => '🇭🇷', 'Cuban' => '🇨🇺', 'Cypriot' => '🇨🇾', 'Czech' => '🇨🇿',
                                            'Danish' => '🇩🇰', 'Djiboutian' => '🇩🇯', 'Dominican' => '🇩🇴', 'Dutch' => '🇳🇱', 'Ecuadorian' => '🇪🇨',
                                            'Egyptian' => '🇪🇬', 'Emirati' => '🇦🇪', 'Equatorial Guinean' => '🇬🇶', 'Eritrean' => '🇪🇷', 'Estonian' => '🇪🇪',
                                            'Ethiopian' => '🇪🇹', 'Fijian' => '🇫🇯', 'Filipino' => '🇵🇭', 'Finnish' => '🇫🇮', 'French' => '🇫🇷',
                                            'Gabonese' => '🇬🇦', 'Gambian' => '🇬🇲', 'Georgian' => '🇬🇪', 'German' => '🇩🇪', 'Ghanaian' => '🇬🇭',
                                            'Greek' => '🇬🇷', 'Grenadian' => '🇬🇩', 'Guatemalan' => '🇬🇹', 'Guinean' => '🇬🇳', 'Guyanese' => '🇬🇾',
                                            'Haitian' => '🇭🇹', 'Honduran' => '🇭🇳', 'Hungarian' => '🇭🇺', 'Icelandic' => '🇮🇸', 'Indian' => '🇮🇳',
                                            'Indonesian' => '🇮🇩', 'Iranian' => '🇮🇷', 'Iraqi' => '🇮🇶', 'Irish' => '🇮🇪', 'Israeli' => '🇮🇱',
                                            'Italian' => '🇮🇹', 'Ivorian' => '🇨🇮', 'Jamaican' => '🇯🇲', 'Japanese' => '🇯🇵', 'Jordanian' => '🇯🇴',
                                            'Kazakh' => '🇰🇿', 'Kenyan' => '🇰🇪', 'Kiribati' => '🇰🇮', 'Korean' => '🇰🇷', 'Kuwaiti' => '🇰🇼',
                                            'Kyrgyz' => '🇰🇬', 'Lao' => '🇱🇦', 'Latvian' => '🇱🇻', 'Lebanese' => '🇱🇧', 'Liberian' => '🇱🇷',
                                            'Libyan' => '🇱🇾', 'Lithuanian' => '🇱🇹', 'Luxembourgish' => '🇱🇺', 'Malagasy' => '🇲🇬', 'Malawian' => '🇲🇼',
                                            'Malaysian' => '🇲🇾', 'Maldivian' => '🇲🇻', 'Malian' => '🇲🇱', 'Maltese' => '🇲🇹', 'Mauritanian' => '🇲🇷',
                                            'Mauritian' => '🇲🇺', 'Mexican' => '🇲🇽', 'Moldovan' => '🇲🇩', 'Mongolian' => '🇲🇳', 'Montenegrin' => '🇲🇪',
                                            'Moroccan' => '🇲🇦', 'Mozambican' => '🇲🇿', 'Namibian' => '🇳🇦', 'Nepalese' => '🇳🇵', 'New Zealander' => '🇳🇿',
                                            'Nicaraguan' => '🇳🇮', 'Nigerian' => '🇳🇬', 'North Korean' => '🇰🇵', 'Norwegian' => '🇳🇴', 'Omani' => '🇴🇲',
                                            'Pakistani' => '🇵🇰', 'Panamanian' => '🇵🇦', 'Paraguayan' => '🇵🇾', 'Peruvian' => '🇵🇪', 'Polish' => '🇵🇱',
                                            'Portuguese' => '🇵🇹', 'Qatari' => '🇶🇦', 'Romanian' => '🇷🇴', 'Russian' => '🇷🇺', 'Rwandan' => '🇷🇼',
                                            'Saudi' => '🇸🇦', 'Senegalese' => '🇸🇳', 'Serbian' => '🇷🇸', 'Singaporean' => '🇸🇬', 'Slovak' => '🇸🇰',
                                            'Slovenian' => '🇸🇮', 'Somali' => '🇸🇴', 'South African' => '🇿🇦', 'Spanish' => '🇪🇸', 'Sri Lankan' => '🇱🇰',
                                            'Sudanese' => '🇸🇩', 'Surinamese' => '🇸🇷', 'Swedish' => '🇸🇪', 'Swiss' => '🇨🇭', 'Syrian' => '🇸🇾',
                                            'Taiwanese' => '🇹🇼', 'Tajik' => '🇹🇯', 'Tanzanian' => '🇹🇿', 'Thai' => '🇹🇭', 'Togolese' => '🇹🇬',
                                            'Trinidadian' => '🇹🇹', 'Tunisian' => '🇹🇳', 'Turkish' => '🇹🇷', 'Turkmen' => '🇹🇲', 'Ugandan' => '🇺🇬',
                                            'Ukrainian' => '🇺🇦', 'Uruguayan' => '🇺🇾', 'Uzbek' => '🇺🇿', 'Venezuelan' => '🇻🇪', 'Vietnamese' => '🇻🇳',
                                            'Yemeni' => '🇾🇪', 'Zambian' => '🇿🇲', 'Zimbabwean' => '🇿🇼',
                                        ];
                                        $selectedNat = old('reservation_nationality', $sale->reservation_data['nationality'] ?? '');
                                    @endphp
                                    <div class="col-md-6 {{ $idType === 'passport' ? '' : 'd-none' }}" id="nationality_field">
                                        <select name="reservation_nationality" id="reservation_nationality" class="form-select">
                                            <option value="">-- Select Nationality --</option>
                                            @foreach($nationalities as $nat => $flag)
                                                <option value="{{ $nat }}" {{ $selectedNat === $nat ? 'selected' : '' }}>{{ $flag }} {{ $nat }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone <span class="text-danger">*</span></label>
                                <input type="text" name="reservation_phone" class="form-control" value="{{ old('reservation_phone', $sale->reservation_data['phone'] ?? '') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="reservation_email" class="form-control" value="{{ old('reservation_email', $sale->reservation_data['email'] ?? '') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Reservation Date <span class="text-danger">*</span></label>
                                <input type="date" name="reservation_date" class="form-control" value="{{ old('reservation_date', $sale->reservation_data['reservation_date'] ?? '') }}" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Address <span class="text-danger">*</span></label>
                                <textarea name="reservation_address" rows="2" class="form-control" required>{{ old('reservation_address', $sale->reservation_data['address'] ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="form-section-header">
                            <i class="bi bi-cash-stack"></i> Payment
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Reservation Amount (Listing)</label>
                                <input type="text" class="form-control bg-light" value="{{ $sale->listing->reservation_deposit ? number_format($sale->listing->reservation_deposit, 2) : '-' }}" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Amount Paid (Number) <span class="text-danger">*</span></label>
                                <input type="text" inputmode="decimal" id="reservation_amount_paid_number" name="reservation_amount_paid_number" class="form-control formatted-number-input" value="{{ old('reservation_amount_paid_number', $sale->reservation_data['amount_paid_number'] ?? '') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Amount Paid (Text) <span class="text-danger">*</span></label>
                                <input type="text" id="reservation_amount_paid_text" name="reservation_amount_paid_text" class="form-control" value="{{ old('reservation_amount_paid_text', $sale->reservation_data['amount_paid_text'] ?? '') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Contract Start Date <span class="text-danger">*</span></label>
                                <input type="date" name="reservation_contract_start_date" class="form-control" value="{{ old('reservation_contract_start_date', $sale->reservation_data['contract_start_date'] ?? '') }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="form-section-header">
                            <i class="bi bi-pencil-square"></i> Signatures
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Buyer Name (Signature) <span class="text-danger">*</span></label>
                                <input type="text" name="reservation_buyer_signature_name" class="form-control" value="{{ old('reservation_buyer_signature_name', $sale->reservation_data['buyer_signature_name'] ?? '') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Seller Name <span class="text-danger">*</span></label>
                                <input type="text" name="reservation_seller_name" class="form-control" value="{{ old('reservation_seller_name', $sale->reservation_data['seller_name'] ?? auth()->user()->name) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Witness 1</label>
                                <input type="text" name="reservation_witness_one_name" class="form-control" value="{{ old('reservation_witness_one_name', $sale->reservation_data['witness_one_name'] ?? '') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Witness 2</label>
                                <input type="text" name="reservation_witness_two_name" class="form-control" value="{{ old('reservation_witness_two_name', $sale->reservation_data['witness_two_name'] ?? '') }}">
                            </div>
                        </div>
                    </div>

                @elseif($type === 'contract')
                    @php
                        $contractData = $sale->contract_data ?? [];
                        $reservationRecordData = $reservationRecord ? $reservationRecord->toArray() : null;
                        $defaultFullName = $reservationRecordData['buyer_full_name']
                            ?? trim(($sale->reservation_data['first_name'] ?? '') . ' ' . ($sale->reservation_data['last_name'] ?? ''));
                        $defaultPhone = $reservationRecordData['buyer_phone'] ?? ($sale->reservation_data['phone'] ?? '');
                        $defaultDepositDate = $sale->reservation_data['reservation_date'] ?? optional($reservationRecordData['reservation_date'] ?? null)->format('Y-m-d');
                        $listing = $sale->listing;
                        $existingInstallments = data_get($contractData, 'pricing.installments', []);
                        $maxInstallmentsAllowed = 36;
                        $defaultInstallmentCount = data_get($contractData, 'pricing.installment_count');
                        $oldInstallmentAmounts = old('contract_installment_amount_number', []);
                        $oldDataCount = is_array($oldInstallmentAmounts) ? count($oldInstallmentAmounts) : 0;
                        if (old('contract_installment_count') !== null) {
                            $selectedInstallmentCount = min(max((int) old('contract_installment_count'), 1), $maxInstallmentsAllowed);
                            $maxInstallmentRows = max($selectedInstallmentCount, $oldDataCount, count($existingInstallments));
                        } elseif ($defaultInstallmentCount !== null || count($existingInstallments) > 0) {
                            $selectedInstallmentCount = min(max((int) ($defaultInstallmentCount ?? count($existingInstallments)), 1), $maxInstallmentsAllowed);
                            $maxInstallmentRows = max($selectedInstallmentCount, count($existingInstallments));
                        } else {
                            $selectedInstallmentCount = 0;
                            $maxInstallmentRows = 0;
                        }
                        if ($maxInstallmentRows > 0) {
                            $maxInstallmentRows = min(max($maxInstallmentRows, 12), $maxInstallmentsAllowed);
                        }
                    @endphp

                    {{-- ══════════ Contract Step 1 ══════════ --}}
                    <div class="contract-step active" data-step="1">
                        <div class="form-section">
                            <div class="form-section-header">
                                <i class="bi bi-file-earmark-text"></i> Contract Information
                            </div>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Contract No.</label>
                                    <input type="text" name="contract_number" class="form-control" value="{{ old('contract_number', data_get($contractData, 'contract_number', $listing->unit_code)) }}" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Contract Date <span class="text-danger">*</span></label>
                                    <input type="date" name="contract_date" class="form-control" value="{{ old('contract_date', data_get($contractData, 'contract_date')) }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="contract_full_name" class="form-control" value="{{ old('contract_full_name', data_get($contractData, 'buyer_full_name', $defaultFullName)) }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Phone <span class="text-danger">*</span></label>
                                    <input type="text" name="contract_phone" class="form-control" value="{{ old('contract_phone', data_get($contractData, 'phone', $defaultPhone)) }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Customer Type <span class="text-danger">*</span></label>
                                    <select name="contract_payment_type" class="form-select" required data-label="Customer Type">
                                        <option value="">-- Select --</option>
                                        <option value="bank_loan" {{ old('contract_payment_type', data_get($contractData, 'payment_type')) === 'bank_loan' ? 'selected' : '' }}>Bank Loan</option>
                                        <option value="cash_transfer" {{ old('contract_payment_type', data_get($contractData, 'payment_type')) === 'cash_transfer' ? 'selected' : '' }}>Cash Transfer</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <div class="form-section-header">
                                <i class="bi bi-geo-alt"></i> Address
                            </div>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">House No. <span class="text-danger">*</span></label>
                                    <input type="text" name="contract_house_no" class="form-control" value="{{ old('contract_house_no', data_get($contractData, 'address.house_no')) }}" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Village No.</label>
                                    <input type="text" name="contract_village_no" class="form-control" value="{{ old('contract_village_no', data_get($contractData, 'address.village_no')) }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Street</label>
                                    <input type="text" name="contract_street" class="form-control" value="{{ old('contract_street', data_get($contractData, 'address.street')) }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Province <span class="text-danger">*</span></label>
                                    <select id="contract_province" name="contract_province" class="form-select" data-selected="{{ old('contract_province', data_get($contractData, 'address.province')) }}" required>
                                        <option value="">-- Select --</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">District <span class="text-danger">*</span></label>
                                    <select id="contract_district" name="contract_district" class="form-select" data-selected="{{ old('contract_district', data_get($contractData, 'address.district')) }}" required>
                                        <option value="">-- Select --</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Subdistrict <span class="text-danger">*</span></label>
                                    <select id="contract_subdistrict" name="contract_subdistrict" class="form-select" data-selected="{{ old('contract_subdistrict', data_get($contractData, 'address.subdistrict')) }}" required>
                                        <option value="">-- Select --</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Postal Code</label>
                                    <input type="text" id="contract_postal_code" name="contract_postal_code" class="form-control" value="{{ old('contract_postal_code', data_get($contractData, 'address.postal_code')) }}" maxlength="5" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <div class="form-section-header">
                                <i class="bi bi-building"></i> Project Details
                            </div>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Project Name <span class="text-danger">*</span></label>
                                    <input type="text" name="contract_project_name" class="form-control" value="{{ old('contract_project_name', data_get($contractData, 'project.name', $listing->project_name ?? $listing->project->location->project_name ?? '')) }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Floor <span class="text-danger">*</span></label>
                                    <input type="text" name="contract_floor" class="form-control" value="{{ old('contract_floor', data_get($contractData, 'project.floor', $listing->floor)) }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Room No. <span class="text-danger">*</span></label>
                                    <input type="text" name="contract_room_number" class="form-control" value="{{ old('contract_room_number', data_get($contractData, 'project.room_number', $listing->room_number)) }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Unit Type <span class="text-danger">*</span></label>
                                    <input type="text" name="contract_unit_type" class="form-control" value="{{ old('contract_unit_type', data_get($contractData, 'project.unit_type', $listing->unit_type)) }}" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Qty (Units) <span class="text-danger">*</span></label>
                                    <input type="number" min="1" name="contract_quantity" class="form-control" value="{{ old('contract_quantity', data_get($contractData, 'project.quantity', 1)) }}" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Price / SQM <span class="text-danger">*</span></label>
                                    <input type="text" inputmode="decimal" name="contract_price_per_sqm_number" class="form-control formatted-number-input" value="{{ old('contract_price_per_sqm_number', data_get($contractData, 'project.price_per_sqm_number', $listing->price_per_sqm)) }}" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Area (SQM) <span class="text-danger">*</span></label>
                                    <input type="text" inputmode="decimal" name="contract_area_sqm" class="form-control formatted-number-input" value="{{ old('contract_area_sqm', data_get($contractData, 'project.area_sqm', $listing->area)) }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <div class="form-section-header">
                                <i class="bi bi-cash-stack"></i> Pricing Details
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Total Price (Number) <span class="text-danger">*</span></label>
                                    <input type="text" inputmode="decimal" class="form-control thai-number-input formatted-number-input" data-thai-target="contract_total_price_text" name="contract_total_price_number" value="{{ old('contract_total_price_number', data_get($contractData, 'pricing.total_price_number', $listing->price_per_room)) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Total Price (Text) <span class="text-danger">*</span></label>
                                    <input type="text" id="contract_total_price_text" name="contract_total_price_text" class="form-control" value="{{ old('contract_total_price_text', data_get($contractData, 'pricing.total_price_text')) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Adjustment (Number) <span class="text-danger">*</span></label>
                                    <input type="text" inputmode="decimal" class="form-control thai-number-input formatted-number-input" data-thai-target="contract_adjustment_text" name="contract_adjustment_number" value="{{ old('contract_adjustment_number', data_get($contractData, 'pricing.adjustment_number', 0)) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Adjustment (Text) <span class="text-danger">*</span></label>
                                    <input type="text" id="contract_adjustment_text" name="contract_adjustment_text" class="form-control" value="{{ old('contract_adjustment_text', data_get($contractData, 'pricing.adjustment_text')) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Deposit Paid (Number) <span class="text-danger">*</span></label>
                                    <input type="text" inputmode="decimal" class="form-control thai-number-input formatted-number-input" data-thai-target="contract_deposit_text" name="contract_deposit_number" value="{{ old('contract_deposit_number', data_get($contractData, 'pricing.deposit_number', $sale->reservation_data['amount_paid_number'] ?? $listing->reservation_deposit)) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Deposit Paid (Text) <span class="text-danger">*</span></label>
                                    <input type="text" id="contract_deposit_text" name="contract_deposit_text" class="form-control" value="{{ old('contract_deposit_text', data_get($contractData, 'pricing.deposit_text', $sale->reservation_data['amount_paid_text'] ?? '')) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Deposit Date <span class="text-danger">*</span></label>
                                    <input type="date" name="contract_deposit_date" class="form-control" value="{{ old('contract_deposit_date', data_get($contractData, 'pricing.deposit_date', $sale->reservation_data['reservation_date'] ?? null)) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Contract Payment (Number) <span class="text-danger">*</span></label>
                                    <input type="text" inputmode="decimal" class="form-control thai-number-input formatted-number-input" data-thai-target="contract_payment_text" name="contract_payment_number" value="{{ old('contract_payment_number', data_get($contractData, 'pricing.contract_payment_number', $listing->contract_payment)) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Contract Payment (Text) <span class="text-danger">*</span></label>
                                    <input type="text" id="contract_payment_text" name="contract_payment_text" class="form-control" value="{{ old('contract_payment_text', data_get($contractData, 'pricing.contract_payment_text')) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Contract Payment Date <span class="text-danger">*</span></label>
                                    <input type="date" name="contract_payment_date" class="form-control" value="{{ old('contract_payment_date', data_get($contractData, 'pricing.contract_payment_date')) }}" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ══════════ Contract Step 2 ══════════ --}}
                    <div class="contract-step" data-step="2">
                        <div class="form-section">
                            <div class="form-section-header">
                                <i class="bi bi-list-ol"></i> Installment Schedule
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Total Installment (Number) <span class="text-danger">*</span></label>
                                    <input type="text" inputmode="decimal" class="form-control thai-number-input formatted-number-input" data-thai-target="contract_installment_total_text" name="contract_installment_total_number" value="{{ old('contract_installment_total_number', data_get($contractData, 'pricing.installment_total_number', $listing->installment_12_terms)) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Total Installment (Text) <span class="text-danger">*</span></label>
                                    <input type="text" id="contract_installment_total_text" name="contract_installment_total_text" class="form-control" value="{{ old('contract_installment_total_text', data_get($contractData, 'pricing.installment_total_text')) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Remaining Amount (Number) <span class="text-danger">*</span></label>
                                    <input type="text" inputmode="decimal" class="form-control thai-number-input formatted-number-input" data-thai-target="contract_remaining_text" name="contract_remaining_number" value="{{ old('contract_remaining_number', data_get($contractData, 'pricing.remaining_number', $listing->transfer_amount)) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Remaining Amount (Text) <span class="text-danger">*</span></label>
                                    <input type="text" id="contract_remaining_text" name="contract_remaining_text" class="form-control" value="{{ old('contract_remaining_text', data_get($contractData, 'pricing.remaining_text')) }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Number of Installments <span class="text-danger">*</span></label>
                                    <input type="number" min="1" max="{{ $maxInstallmentsAllowed }}" id="contract_installment_count" name="contract_installment_count" class="form-control" value="{{ $selectedInstallmentCount > 0 ? $selectedInstallmentCount : '' }}" required data-label="Number of Installments">
                                </div>
                            </div>

                            <div class="table-responsive mt-4" id="installmentTableWrapper"{{ $selectedInstallmentCount === 0 ? ' style="display:none;"' : '' }}>
                                <table class="table align-middle">
                                    <thead>
                                        <tr>
                                            <th>No.</th>
                                            <th>Amount (Number)</th>
                                            <th>Amount (Text)</th>
                                            <th>Due Date</th>
                                        </tr>
                                    </thead>
                                    <tbody id="installmentTableBody">
                                        @for($i = 0; $i < $maxInstallmentRows; $i++)
                                            @php
                                                $installmentNumber = $i + 1;
                                                $existing = $existingInstallments[$i] ?? [];
                                                $amountNumber = old('contract_installment_amount_number.' . $i, data_get($existing, 'amount_number'));
                                                $amountText = old('contract_installment_amount_text.' . $i, data_get($existing, 'amount_text'));
                                                $dateValue = old('contract_installment_date.' . $i, data_get($existing, 'date'));
                                                $rowVisible = $i < $selectedInstallmentCount;
                                            @endphp
                                            <tr class="installment-row {{ $rowVisible ? '' : 'd-none' }}" data-row-index="{{ $i }}">
                                                <td class="fw-semibold">#{{ $installmentNumber }}</td>
                                                <td>
                                                    <input type="text" inputmode="decimal" name="contract_installment_amount_number[]" class="form-control thai-number-input formatted-number-input" data-thai-target="installment_text_{{ $i }}" value="{{ $amountNumber }}" {{ $rowVisible ? '' : 'disabled' }}>
                                                </td>
                                                <td>
                                                    <input type="text" id="installment_text_{{ $i }}" name="contract_installment_amount_text[]" class="form-control" value="{{ $amountText }}" {{ $rowVisible ? '' : 'disabled' }}>
                                                </td>
                                                <td>
                                                    <input type="date" name="contract_installment_date[]" class="form-control" value="{{ $dateValue }}" {{ $rowVisible ? '' : 'disabled' }}>
                                                </td>
                                            </tr>
                                        @endfor
                                    </tbody>
                                </table>
                                <template id="installmentRowTemplate">
                                    <tr class="installment-row d-none" data-row-index="">
                                        <td class="fw-semibold"></td>
                                        <td>
                                            <input type="text" inputmode="decimal" name="contract_installment_amount_number[]" class="form-control thai-number-input formatted-number-input" data-thai-target="">
                                        </td>
                                        <td>
                                            <input type="text" name="contract_installment_amount_text[]" class="form-control">
                                        </td>
                                        <td>
                                            <input type="date" name="contract_installment_date[]" class="form-control">
                                        </td>
                                    </tr>
                                </template>
                            </div>
                        </div>

                        <div class="form-section">
                            <div class="form-section-header">
                                <i class="bi bi-pencil-square"></i> Signatures
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Seller Name <span class="text-danger">*</span></label>
                                    <input type="text" name="contract_seller_name" class="form-control" value="{{ old('contract_seller_name', data_get($contractData, 'signatures.seller_name', $sale->reservation_data['seller_name'] ?? auth()->user()->name)) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Buyer Name <span class="text-danger">*</span></label>
                                    <input type="text" name="contract_buyer_signature_name" class="form-control" value="{{ old('contract_buyer_signature_name', data_get($contractData, 'signatures.buyer_name', $sale->reservation_data['buyer_signature_name'] ?? $defaultFullName)) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Witness 1</label>
                                    <input type="text" name="contract_witness_one_name" class="form-control" value="{{ old('contract_witness_one_name', data_get($contractData, 'signatures.witness_one_name', $sale->reservation_data['witness_one_name'] ?? '')) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Witness 2</label>
                                    <input type="text" name="contract_witness_two_name" class="form-control" value="{{ old('contract_witness_two_name', data_get($contractData, 'signatures.witness_two_name', $sale->reservation_data['witness_two_name'] ?? '')) }}">
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Footer buttons --}}
                @if($type === 'reserved')
                    <div class="form-footer">
                        <a href="{{ route('buy-sale.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                        <div class="d-flex gap-2">
                            <button type="submit" name="save_action" value="draft" class="btn btn-outline-secondary px-4">
                                <i class="bi bi-save me-1"></i>Save Draft
                            </button>
                            <button type="submit" name="save_action" value="submit" class="btn btn-primary px-4">
                                <i class="bi bi-check-lg me-1"></i>Submit & Advance
                            </button>
                        </div>
                    </div>
                @else
                    {{-- Contract Step 1: Next only --}}
                    <div class="contract-footer-step1">
                        <div class="form-footer justify-content-end">
                            <button type="button" class="btn btn-primary px-4 contract-next-btn">
                                Next <i class="bi bi-arrow-right ms-1"></i>
                            </button>
                        </div>
                    </div>
                    {{-- Contract Step 2: Back | Save Draft + Submit --}}
                    <div class="contract-footer-step2" style="display:none;">
                        <div class="form-footer">
                            <button type="button" class="btn btn-outline-secondary px-4 contract-prev-btn">
                                <i class="bi bi-arrow-left me-1"></i>Back
                            </button>
                            <div class="d-flex gap-2">
                                <button type="submit" name="save_action" value="draft" class="btn btn-outline-secondary px-4">
                                    <i class="bi bi-save me-1"></i>Save Draft
                                </button>
                                <button type="submit" name="save_action" value="submit" class="btn btn-primary px-4">
                                    <i class="bi bi-check-lg me-1"></i>Submit & Advance
                                </button>
                            </div>
                        </div>
                        <div id="nextStepError" class="text-danger small fw-semibold text-center mt-2" style="display:none;">
                            <i class="bi bi-exclamation-circle me-1"></i>Please fill in all required fields before continuing.
                        </div>
                    </div>
                @endif
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    @parent
    <script>
        (function () {
            let setStep = null;

            const thNums = ['ศูนย์','หนึ่ง','สอง','สาม','สี่','ห้า','หก','เจ็ด','แปด','เก้า'];
            const thUnits = ['','สิบ','ร้อย','พัน','หมื่น','แสน','ล้าน'];

            document.querySelectorAll('input').forEach((input) => {
                if (!input.getAttribute('autocomplete')) {
                    input.setAttribute('autocomplete', 'off');
                }
            });

            // ── ID Type toggle ──
            const idTypeRadios = document.querySelectorAll('input[name="reservation_id_type"]');
            const idNumberInput = document.getElementById('reservation_id_number');
            const idNumberCol = document.getElementById('id_number_col');
            const nationalityField = document.getElementById('nationality_field');

            if (idTypeRadios.length && idNumberInput) {
                idTypeRadios.forEach(radio => {
                    radio.addEventListener('change', function () {
                        document.querySelectorAll('.id-type-option').forEach(el => el.classList.remove('active'));
                        this.closest('.id-type-option').classList.add('active');

                        const isPassport = this.value === 'passport';
                        idNumberInput.maxLength = isPassport ? 8 : 13;
                        idNumberInput.placeholder = isPassport ? 'e.g. AB123456' : 'e.g. 1234567890123';
                        idNumberCol.className = isPassport ? 'col-md-6' : 'col-12';
                        nationalityField.classList.toggle('d-none', !isPassport);

                        idNumberInput.value = '';
                        idNumberInput.focus();
                    });
                });
            }

            const unformatNumber = (value) => {
                if (value === null || value === undefined) return '';
                return value.toString().replace(/,/g, '');
            };

            const parseNumberValue = (value) => {
                const normalized = unformatNumber(value).trim();
                if (normalized === '') return null;
                const parsed = parseFloat(normalized);
                return Number.isFinite(parsed) ? parsed : null;
            };

            const formatNumberDisplay = (value) => {
                const normalized = unformatNumber(value);
                if (normalized === '') return '';
                const [integerPart, decimalPart] = normalized.split('.');
                const withCommas = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                return decimalPart !== undefined ? `${withCommas}.${decimalPart}` : withCommas;
            };

            function convertChunk(num) {
                let text = '';
                const digits = num.toString().padStart(6, '0');
                const len = digits.length;
                for (let i = 0; i < len; i++) {
                    const digit = parseInt(digits[i], 10);
                    const unit = len - i - 1;
                    if (digit === 0) continue;
                    if (unit === 0) {
                        text += (digit === 1 && digits.length > 1 ? 'เอ็ด' : thNums[digit]);
                    } else if (unit === 1) {
                        if (digit === 1) text += 'สิบ';
                        else if (digit === 2) text += 'ยี่สิบ';
                        else text += thNums[digit] + 'สิบ';
                    } else {
                        text += thNums[digit] + thUnits[unit];
                    }
                }
                return text;
            }

            function convertIntegerToThai(num) {
                if (num === 0) return thNums[0];
                let text = '';
                let unitIdx = 0;
                while (num > 0) {
                    const chunk = num % 1000000;
                    if (chunk !== 0) {
                        text = convertChunk(chunk) + (unitIdx > 0 ? thUnits[6] : '') + text;
                    }
                    num = Math.floor(num / 1000000);
                    unitIdx++;
                }
                return text;
            }

            function convertNumberToThaiBaht(value) {
                if (!Number.isFinite(value)) return '';
                const absolute = Math.abs(value);
                const integerPart = Math.floor(absolute);
                const satang = Math.round((absolute - integerPart) * 100);
                let text = convertIntegerToThai(integerPart) + 'บาท';
                if (satang > 0) {
                    text += convertIntegerToThai(satang) + 'สตางค์';
                } else {
                    text += 'ถ้วน';
                }
                if (value < 0) text = 'ลบ' + text;
                return text;
            }

            // Reservation amount auto text
            const reservationAmountNumber = document.getElementById('reservation_amount_paid_number');
            const reservationAmountText = document.getElementById('reservation_amount_paid_text');
            if (reservationAmountNumber && reservationAmountText) {
                const syncReservationText = () => {
                    const raw = parseNumberValue(reservationAmountNumber.value);
                    reservationAmountText.value = raw !== null ? convertNumberToThaiBaht(raw) : '';
                };
                reservationAmountNumber.addEventListener('input', syncReservationText);
                reservationAmountNumber.addEventListener('blur', syncReservationText);
                syncReservationText();
            }

            // Thai number converter
            const initializeThaiNumberInput = (input) => {
                if (!input || input.dataset.thaiNumberInitialized === 'true') return;
                const resolveTarget = () => {
                    const targetId = input.dataset.thaiTarget;
                    return targetId ? document.getElementById(targetId) : null;
                };
                const sync = () => {
                    const targetEl = resolveTarget();
                    const value = parseNumberValue(input.value);
                    if (targetEl) targetEl.value = value !== null ? convertNumberToThaiBaht(value) : '';
                };
                input.addEventListener('input', sync);
                input.addEventListener('blur', sync);
                sync();
                input.dataset.thaiNumberInitialized = 'true';
            };
            document.querySelectorAll('.thai-number-input').forEach(initializeThaiNumberInput);

            // Thousand separator
            const initializeFormattedInput = (input) => {
                if (!input || input.dataset.formattedInitialized === 'true') return;
                input.dataset.formattedInitialized = 'true';
                const formatValue = () => { input.value = formatNumberDisplay(input.value); };
                input.addEventListener('focus', () => {
                    input.value = unformatNumber(input.value);
                    setTimeout(() => input.select(), 0);
                });
                input.addEventListener('blur', () => {
                    formatValue();
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                });
                formatValue();
            };
            const formattedInputs = Array.from(document.querySelectorAll('.formatted-number-input'));
            formattedInputs.forEach(initializeFormattedInput);

            // Field error helpers
            const getErrorEl = (input) => {
                const errorId = input.dataset.errorId;
                return errorId ? document.getElementById(errorId) : null;
            };
            const clearFieldError = (input) => {
                input.classList.remove('is-invalid');
                const existing = getErrorEl(input);
                if (existing) existing.remove();
            };
            const showFieldError = (input) => {
                input.classList.add('is-invalid');
                let errorEl = getErrorEl(input);
                if (!errorEl) {
                    const errorId = `error-${Math.random().toString(36).slice(2, 9)}`;
                    input.dataset.errorId = errorId;
                    errorEl = document.createElement('div');
                    errorEl.id = errorId;
                    errorEl.className = 'field-error';
                    input.insertAdjacentElement('afterend', errorEl);
                }
                errorEl.textContent = input.dataset.errorMessage || 'This field is required.';
            };
            const validateField = (input) => {
                clearFieldError(input);
                if (!input.checkValidity()) { showFieldError(input); return false; }
                return true;
            };

            // Form validation
            const buySaleForm = document.querySelector('form[action*="buy-sale"]');
            if (buySaleForm) {
                const requiredFields = buySaleForm.querySelectorAll('input[required], select[required], textarea[required]');
                let lastSubmitAction = null;
                const submitButtons = buySaleForm.querySelectorAll('button[type="submit"][name="save_action"]');
                const validationAlert = document.getElementById('formValidationAlert');
                const draftButton = buySaleForm.querySelector('button[type="submit"][name="save_action"][value="draft"]');

                const disableNativeRequired = () => {
                    requiredFields.forEach((input) => {
                        if (!input.dataset.initiallyRequired) input.dataset.initiallyRequired = 'true';
                        input.removeAttribute('required');
                    });
                };
                const enableNativeRequired = () => {
                    requiredFields.forEach((input) => {
                        if (input.dataset.initiallyRequired === 'true') input.setAttribute('required', 'required');
                    });
                };

                submitButtons.forEach((button) => {
                    button.addEventListener('click', () => {
                        lastSubmitAction = button.value || null;
                        if (button.value === 'draft') disableNativeRequired();
                        else enableNativeRequired();
                    });
                });
                buySaleForm.addEventListener('resetNativeRequired', enableNativeRequired);
                if (draftButton) {
                    draftButton.addEventListener('blur', () => {
                        if (lastSubmitAction !== 'draft') enableNativeRequired();
                    });
                }

                const getFieldLabel = (input) => {
                    if (input.dataset.label) return input.dataset.label;
                    const labelledBy = input.getAttribute('aria-labelledby');
                    if (labelledBy) { const el = document.getElementById(labelledBy); if (el) return el.textContent.trim(); }
                    if (input.id) { const lf = buySaleForm.querySelector(`label[for="${input.id}"]`); if (lf) return lf.textContent.replace('*', '').trim(); }
                    const nearestLabel = input.closest('.col-12, .col-md-12, .col-md-6, .col-md-4, .col-md-3, .mb-3, .form-group')?.querySelector('label');
                    if (nearestLabel) return nearestLabel.textContent.replace('*', '').trim();
                    return input.name || 'Required field';
                };

                const showValidationAlert = (missingLabels = []) => {
                    if (!validationAlert) return;
                    if (!missingLabels.length) { validationAlert.style.display = 'none'; validationAlert.innerHTML = ''; return; }
                    const listItems = missingLabels.map((l) => `<li>${l}</li>`).join('');
                    validationAlert.innerHTML = `<strong>Please fill in all required fields:</strong><ul class="mb-0 ps-3">${listItems}</ul>`;
                    validationAlert.style.display = 'block';
                };

                requiredFields.forEach((input) => {
                    const events = input.tagName === 'SELECT' ? ['change'] : ['input', 'blur'];
                    events.forEach((e) => input.addEventListener(e, () => validateField(input)));
                });

                buySaleForm.addEventListener('submit', (event) => {
                    const requiresFullValidation = !lastSubmitAction || lastSubmitAction === 'submit';
                    if (requiresFullValidation) {
                        enableNativeRequired();
                        let firstInvalid = null;
                        const missingLabels = [];
                        requiredFields.forEach((input) => {
                            if (!validateField(input) && !firstInvalid) firstInvalid = input;
                            if (input.classList.contains('is-invalid')) missingLabels.push(getFieldLabel(input));
                        });
                        if (firstInvalid) {
                            event.preventDefault();
                            const parentStep = firstInvalid.closest('.contract-step');
                            if (parentStep && typeof setStep === 'function') {
                                const stepNum = parseInt(parentStep.dataset.step, 10);
                                if (!isNaN(stepNum)) setStep(stepNum);
                            }
                            const nextStepError = document.getElementById('nextStepError');
                            if (nextStepError) nextStepError.style.display = 'block';
                            firstInvalid.focus();
                            firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            showValidationAlert(missingLabels);
                            return;
                        }
                    } else {
                        requiredFields.forEach(clearFieldError);
                        showValidationAlert();
                    }
                    showValidationAlert();
                    formattedInputs.forEach((input) => { input.value = unformatNumber(input.value); });
                });
            }

            // Thai province cascading selects
            const provinceSelect = document.getElementById('contract_province');
            const districtSelect = document.getElementById('contract_district');
            const subdistrictSelect = document.getElementById('contract_subdistrict');
            if (provinceSelect && districtSelect && subdistrictSelect) {
                const provinces = @json($provinceOptions ?? []);
                const districts = @json($districtOptions ?? []);
                const subDistricts = @json($subDistrictOptions ?? []);

                const renderOptions = (select, items, labelKey, idKey, selectedValue) => {
                    select.innerHTML = '<option value="">-- Select --</option>';
                    items.forEach((item) => {
                        const option = document.createElement('option');
                        option.value = item[labelKey];
                        option.dataset.id = item[idKey];
                        option.textContent = item[labelKey];
                        if (selectedValue && selectedValue === item[labelKey]) option.selected = true;
                        select.appendChild(option);
                    });
                };
                const getSelectedId = (select) => parseInt(select.selectedOptions[0]?.dataset.id || '0', 10) || null;

                const selectedProvinceName = provinceSelect.dataset.selected || provinceSelect.value;
                renderOptions(provinceSelect, provinces, 'PROVINCE_THAI', 'PROVINCE_ID', selectedProvinceName);

                const populateDistricts = () => {
                    const provinceId = getSelectedId(provinceSelect);
                    const filtered = districts.filter((d) => d.PROVINCE_ID === provinceId);
                    const selectedDistrictName = districtSelect.dataset.selected || districtSelect.value;
                    renderOptions(districtSelect, filtered, 'DISTRICT_THAI', 'DISTRICT_ID', selectedDistrictName);
                    populateSubdistricts();
                };
                const postalCodeInput = document.getElementById('contract_postal_code');
                const populateSubdistricts = () => {
                    const districtId = getSelectedId(districtSelect);
                    const filtered = subDistricts.filter((s) => s.DISTRICT_ID === districtId);
                    const selectedSubdistrictName = subdistrictSelect.dataset.selected || subdistrictSelect.value;
                    subdistrictSelect.innerHTML = '<option value="">-- Select --</option>';
                    filtered.forEach((item) => {
                        const option = document.createElement('option');
                        option.value = item['SUB_DISTRICT_THAI'];
                        option.dataset.id = item['SUB_DISTRICT_ID'];
                        option.dataset.postalCode = item['POSTAL_CODE'] || '';
                        option.textContent = item['SUB_DISTRICT_THAI'];
                        if (selectedSubdistrictName && selectedSubdistrictName === item['SUB_DISTRICT_THAI']) option.selected = true;
                        subdistrictSelect.appendChild(option);
                    });
                    updatePostalCode();
                };
                const updatePostalCode = () => {
                    if (!postalCodeInput) return;
                    const selected = subdistrictSelect.selectedOptions[0];
                    postalCodeInput.value = (selected && selected.value) ? (selected.dataset.postalCode || '') : '';
                };

                provinceSelect.addEventListener('change', populateDistricts);
                districtSelect.addEventListener('change', populateSubdistricts);
                subdistrictSelect.addEventListener('change', updatePostalCode);
                populateDistricts();
            }

            // Contract step navigation
            const contractSteps = document.querySelectorAll('.contract-step');
            const stepDots = document.querySelectorAll('.step-indicator .step-dot');
            const nextBtn = document.querySelector('.contract-next-btn');
            const prevBtn = document.querySelector('.contract-prev-btn');
            const footerStep1 = document.querySelector('.contract-footer-step1');
            const footerStep2 = document.querySelector('.contract-footer-step2');

            if (contractSteps.length > 0 && stepDots.length > 0 && nextBtn && prevBtn) {
                let currentStep = 1;
                setStep = (step) => {
                    currentStep = step;
                    contractSteps.forEach((el) => el.classList.toggle('active', parseInt(el.dataset.step, 10) === step));
                    stepDots.forEach((dot) => dot.classList.toggle('active', parseInt(dot.dataset.step, 10) === step));
                    if (footerStep1) footerStep1.style.display = step === 1 ? '' : 'none';
                    if (footerStep2) footerStep2.style.display = step === 2 ? '' : 'none';
                };

                const nextStepError = document.getElementById('nextStepError');
                nextBtn.addEventListener('click', () => {
                    if (nextStepError) nextStepError.style.display = 'none';
                    setStep(2);
                });
                prevBtn.addEventListener('click', () => { if (nextStepError) nextStepError.style.display = 'none'; setStep(1); });
                stepDots.forEach((dot) => dot.addEventListener('click', () => setStep(parseInt(dot.dataset.step, 10))));
                setStep(1);
            }

            // Installment rows
            const installmentCountInput = document.getElementById('contract_installment_count');
            const installmentTableBody = document.getElementById('installmentTableBody');
            const installmentRowTemplate = document.getElementById('installmentRowTemplate');
            const maxInstallmentsAllowed = installmentCountInput ? parseInt(installmentCountInput.max || '36', 10) : 36;

            const updateInstallmentRowMeta = (row, index) => {
                if (!row) return;
                row.dataset.rowIndex = index;
                const sequenceCell = row.querySelector('td.fw-semibold');
                if (sequenceCell) sequenceCell.textContent = `#${index + 1}`;
                const textInputId = `installment_text_${index}`;
                const numberInput = row.querySelector('input[name="contract_installment_amount_number[]"]');
                const textInput = row.querySelector('input[name="contract_installment_amount_text[]"]');
                if (textInput) textInput.id = textInputId;
                if (numberInput) numberInput.dataset.thaiTarget = textInputId;
            };

            const setRowVisibility = (row, visible) => {
                if (!row) return;
                row.classList.toggle('d-none', !visible);
                row.querySelectorAll('input').forEach((input) => {
                    if (visible) input.removeAttribute('disabled');
                    else input.setAttribute('disabled', 'disabled');
                });
            };

            const initializeInstallmentRowInputs = (row) => {
                if (!row || row.dataset.installmentInitialized === 'true') return;
                row.dataset.installmentInitialized = 'true';
                row.querySelectorAll('.formatted-number-input').forEach(initializeFormattedInput);
                row.querySelectorAll('.thai-number-input').forEach(initializeThaiNumberInput);
            };

            const ensureInstallmentRowCapacity = (desiredCount) => {
                if (!installmentTableBody || !installmentRowTemplate) return [];
                const rows = Array.from(installmentTableBody.querySelectorAll('.installment-row'));
                while (rows.length < desiredCount && rows.length < maxInstallmentsAllowed) {
                    const fragment = installmentRowTemplate.content.cloneNode(true);
                    const newRow = fragment.querySelector('.installment-row');
                    installmentTableBody.appendChild(newRow);
                    rows.push(newRow);
                }
                return rows;
            };

            const formatDate = (date) => `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
            const addMonths = (date, months) => { const d = new Date(date.getTime()); d.setMonth(d.getMonth() + months); return d; };
            const getInstallmentAmountInputs = () => Array.from(document.querySelectorAll('input[name="contract_installment_amount_number[]"]'));
            const getInstallmentDateInputs = () => Array.from(document.querySelectorAll('input[name="contract_installment_date[]"]'));

            const autoFillInstallmentDates = () => {
                const amountInputs = getInstallmentAmountInputs();
                const dateInputs = getInstallmentDateInputs();
                let referenceDate = null;
                dateInputs.forEach((input, index) => {
                    if (input.value) { referenceDate = new Date(input.value); return; }
                    if (!amountInputs[index]?.value || !referenceDate) return;
                    referenceDate = addMonths(referenceDate, 1);
                    input.value = formatDate(referenceDate);
                });
            };

            const bindInstallmentAutoFillEvents = () => {
                getInstallmentDateInputs().forEach((input) => {
                    if (input.dataset.autofillChangeBound === 'true') return;
                    input.addEventListener('change', autoFillInstallmentDates);
                    input.dataset.autofillChangeBound = 'true';
                });
                getInstallmentAmountInputs().forEach((input) => {
                    if (input.dataset.autofillInputBound === 'true') return;
                    input.addEventListener('input', autoFillInstallmentDates);
                    input.dataset.autofillInputBound = 'true';
                });
            };

            const updateInstallmentRows = () => {
                if (!installmentCountInput) return;
                const tableWrapper = document.getElementById('installmentTableWrapper');
                const rawValue = installmentCountInput.value.trim();
                if (rawValue === '') { if (tableWrapper) tableWrapper.style.display = 'none'; return; }
                let desiredCount = parseInt(rawValue, 10);
                if (!Number.isFinite(desiredCount) || desiredCount < 1) desiredCount = 1;
                desiredCount = Math.min(desiredCount, maxInstallmentsAllowed);
                installmentCountInput.value = desiredCount;
                if (tableWrapper) tableWrapper.style.display = '';
                const rows = ensureInstallmentRowCapacity(desiredCount);
                rows.forEach((row, index) => {
                    updateInstallmentRowMeta(row, index);
                    setRowVisibility(row, index < desiredCount);
                    initializeInstallmentRowInputs(row);
                });
                bindInstallmentAutoFillEvents();
            };

            if (installmentCountInput && installmentTableBody && installmentRowTemplate) {
                updateInstallmentRows();
                installmentCountInput.addEventListener('input', updateInstallmentRows);
                installmentCountInput.addEventListener('change', updateInstallmentRows);
            }
        })();
    </script>
@endsection
