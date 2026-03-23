@extends('layouts.app')

@section('title', 'Quotation Contract')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Quotation Templates</h2>
        <a href="{{ route('buy-sale.index') }}" class="btn btn-outline-secondary">Back to Pipeline</a>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <p class="text-muted mb-0">Select a listing below to preview or download the quotation PDF in Thai or English. All data is pulled directly from the listing record.</p>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}">
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="quotation_date" class="form-label">Quotation Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('quotation_date') is-invalid @enderror" id="quotation_date" name="quotation_date" value="{{ old('quotation_date') }}">
                                @error('quotation_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="deposit" class="form-label">Deposit</label>
                                <input type="number" step="0.01" class="form-control @error('deposit') is-invalid @enderror" id="deposit" name="deposit" value="{{ old('deposit') }}" placeholder="Auto from listing, editable">
                                @error('deposit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="image" class="form-label">Supporting Image</label>
                                <input type="file" class="form-control @error('image') is-invalid @enderror" id="image" name="image" accept="image/*">
                                @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <small class="text-muted">Optional: Upload reference photo or signature.</small>
                            </div>
                        </div>

                        <div class="mt-4 d-flex gap-2">
                            <button type="submit" class="btn btn-warning px-4">Save Quotation</button>
                            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white border-bottom-0">
                    <h6 class="mb-0 text-uppercase text-muted" style="letter-spacing:.08em;">Listing Details</h6>
                </div>
                <div class="card-body">
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Unit</th>
                            <th>Building</th>
                            <th>Project</th>
                            <th>Price</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($listings as $listing)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $listing->unit_code ?? $listing->room_number ?? '-' }}</div>
                                    <small class="text-muted">Floor {{ $listing->floor ?? '-' }}, {{ $listing->area ? number_format($listing->area, 2) : '-' }} sqm</small>
                                </td>
                                <td>{{ $listing->building ?? '-' }}</td>
                                <td>{{ $listing->project->name ?? '-' }}</td>
                                <td>{{ $listing->price_per_room ? '฿' . number_format($listing->price_per_room, 0) : '-' }}</td>
                                <td class="text-end">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('contracts.quotation.preview-listing', [$listing->id, 'th']) }}" class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-eye me-1"></i> Preview TH
                                        </a>
                                        <a href="{{ route('contracts.quotation.preview-listing', [$listing->id, 'en']) }}" class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-eye me-1"></i> Preview EN
                                        </a>
                                        <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            Download
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="{{ route('contracts.quotation.download-listing', [$listing->id, 'th']) }}">Download TH</a></li>
                                            <li><a class="dropdown-item" href="{{ route('contracts.quotation.download-listing', [$listing->id, 'en']) }}">Download EN</a></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No listings available.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    const listingData = @json($listings->mapWithKeys(fn($listing) => [
        $listing->id => [
            'unit_code' => $listing->unit_code,
            'room_number' => $listing->room_number,
            'floor' => $listing->floor,
            'bedrooms' => $listing->bedrooms,
            'area' => $listing->area,
            'price_per_room' => $listing->price_per_room,
            'price_per_sqm' => $listing->price_per_sqm,
            'unit_type' => $listing->unit_type,
            'project' => $listing->project->name ?? '-',
            'location' => $listing->location->project_name ?? '-',
            'province' => $listing->location->province ?? '-',
            'district' => $listing->location->district ?? '-',
            'reservation_deposit' => $listing->reservation_deposit,
            'contract_payment' => $listing->contract_payment,
            'installment_15_terms' => $listing->installment_15_terms,
            'installment_12_terms' => $listing->installment_12_terms,
            'special_installment_3_terms' => $listing->special_installment_3_terms,
            'transfer_amount' => $listing->transfer_amount,
            'transfer_fee' => $listing->transfer_fee,
            'annual_common_fee' => $listing->annual_common_fee,
            'sinking_fund' => $listing->sinking_fund,
            'utility_fee' => $listing->utility_fee,
            'total_misc_fee' => $listing->total_misc_fee,
        ]
    ]));

    const listingSelect = document.getElementById('listing_id');
    const detailsWrapper = document.getElementById('listing-details');
    const depositInput = document.getElementById('deposit');

    function formatNumber(value) {
        return value ? new Intl.NumberFormat('en-US', { maximumFractionDigits: 0 }).format(Number(value)) : '-';
    }

    function renderDetails(data) {
        return `
            <div class="mb-3">
                <div class="fw-semibold">${data.project}</div>
                <div class="text-muted">${data.location} (${data.province})</div>
            </div>
            <ul class="list-unstyled small mb-0">
                <li><strong>Unit Code:</strong> ${data.unit_code || data.room_number || '-'}</li>
                <li><strong>Floor / Type:</strong> ${data.floor ?? '-'} / ${data.unit_type ?? '-'}</li>
                <li><strong>Bedrooms:</strong> ${data.bedrooms ?? '-'} | <strong>Area:</strong> ${data.area ?? '-'} sqm</li>
                <li><strong>Price/Room:</strong> ${formatNumber(data.price_per_room)} THB</li>
                <li><strong>Price/SQM:</strong> ${formatNumber(data.price_per_sqm)} THB</li>
                <li><strong>Reservation Deposit:</strong> ${formatNumber(data.reservation_deposit)} THB</li>
                <li><strong>Contract Payment:</strong> ${formatNumber(data.contract_payment)} THB</li>
                <li><strong>Installment 15 terms:</strong> ${formatNumber(data.installment_15_terms)} THB</li>
                <li><strong>Installment 12 terms:</strong> ${formatNumber(data.installment_12_terms)} THB</li>
                <li><strong>Special Installment 3 terms:</strong> ${formatNumber(data.special_installment_3_terms)} THB</li>
                <li><strong>Transfer Amount:</strong> ${formatNumber(data.transfer_amount)} THB</li>
                <li><strong>Total Misc. Fee:</strong> ${formatNumber(data.total_misc_fee)} THB</li>
            </ul>
        `;
    }

    function handleListingChange() {
        const selectedId = listingSelect.value;
        const data = listingData[selectedId];
        if (!data) {
            detailsWrapper.innerHTML = '<p class="mb-0">Select a listing to view project, pricing, and financial breakdown.</p>';
            depositInput.value = '';
            return;
        }
        detailsWrapper.innerHTML = renderDetails(data);
        if (!depositInput.value && data.reservation_deposit) {
            depositInput.value = Math.round(Number(data.reservation_deposit));
        }
    }

    listingSelect.addEventListener('change', handleListingChange);
    handleListingChange();
</script>
@endsection
