@extends('layouts.app')

@section('title', 'Reservation Contract')

@section('content')
    <h2 class="mb-4">Reservation Contract</h2>

    <form action="{{ route('contracts.reservation.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="row g-3">
            <div class="col-md-6">
                <label for="listing_id" class="form-label">Listing / Unit <span class="text-danger">*</span></label>
                <select class="form-select @error('listing_id') is-invalid @enderror" id="listing_id" name="listing_id" required>
                    <option value="">-- Select Unit --</option>
                    @foreach($listings as $listing)
                        <option value="{{ $listing->id }}" data-reservation="{{ $listing->reservation_deposit ?? 0 }}" {{ old('listing_id') == $listing->id ? 'selected' : '' }}>
                            {{ $listing->unit_code ?? $listing->room_number }} — {{ $listing->project->name ?? 'N/A' }}
                        </option>
                    @endforeach
                </select>
                @error('listing_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-3">
                <label for="reservation_date" class="form-label">Reservation Date <span class="text-danger">*</span></label>
                <input type="date" class="form-control @error('reservation_date') is-invalid @enderror" id="reservation_date" name="reservation_date" value="{{ old('reservation_date') }}">
                @error('reservation_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-3">
                <label class="form-label">Reservation Amount</label>
                <div class="form-control bg-light" id="reservation_amount_display">{{ old('listing_id') ? optional($listings->firstWhere('id', old('listing_id')))->reservation_deposit : '—' }}</div>
            </div>

            <div class="col-md-6">
                <label class="form-label">Buyer Name <span class="text-danger">*</span></label>
                <div class="row g-2">
                    <div class="col">
                        <input type="text" class="form-control @error('buyer_first_name') is-invalid @enderror" name="buyer_first_name" placeholder="First Name" value="{{ old('buyer_first_name') }}">
                        @error('buyer_first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col">
                        <input type="text" class="form-control @error('buyer_last_name') is-invalid @enderror" name="buyer_last_name" placeholder="Last Name" value="{{ old('buyer_last_name') }}">
                        @error('buyer_last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <label for="buyer_id_number" class="form-label">ID Number <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('buyer_id_number') is-invalid @enderror" id="buyer_id_number" name="buyer_id_number" value="{{ old('buyer_id_number') }}">
                @error('buyer_id_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label for="buyer_phone" class="form-label">Phone <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('buyer_phone') is-invalid @enderror" id="buyer_phone" name="buyer_phone" value="{{ old('buyer_phone') }}">
                @error('buyer_phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label for="buyer_email" class="form-label">Email</label>
                <input type="email" class="form-control @error('buyer_email') is-invalid @enderror" id="buyer_email" name="buyer_email" value="{{ old('buyer_email') }}">
                @error('buyer_email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-12">
                <label for="buyer_address" class="form-label">Address</label>
                <textarea class="form-control @error('buyer_address') is-invalid @enderror" id="buyer_address" name="buyer_address" rows="2">{{ old('buyer_address') }}</textarea>
                @error('buyer_address') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4">
                <label for="amount_paid_number" class="form-label">Amount Paid (Number) <span class="text-danger">*</span></label>
                <input type="number" step="0.01" class="form-control @error('amount_paid_number') is-invalid @enderror" id="amount_paid_number" name="amount_paid_number" value="{{ old('amount_paid_number') }}">
                @error('amount_paid_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4">
                <label for="amount_paid_text" class="form-label">Amount Paid (Text) <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('amount_paid_text') is-invalid @enderror" id="amount_paid_text" name="amount_paid_text" value="{{ old('amount_paid_text') }}">
                @error('amount_paid_text') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4">
                <label for="contract_start_date" class="form-label">Contract Start Date <span class="text-danger">*</span></label>
                <input type="date" class="form-control @error('contract_start_date') is-invalid @enderror" id="contract_start_date" name="contract_start_date" value="{{ old('contract_start_date') }}">
                @error('contract_start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Buyer Signature <span class="text-danger">*</span></label>
                <input type="text" class="form-control mb-2 @error('buyer_signature_name') is-invalid @enderror" name="buyer_signature_name" placeholder="Buyer Printed Name" value="{{ old('buyer_signature_name') }}">
                @error('buyer_signature_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                <input type="file" class="form-control @error('buyer_signature_file') is-invalid @enderror" name="buyer_signature_file" accept="image/*">
                @error('buyer_signature_file') <div class="invalid-feedback">{{ $message }}</div> @enderror
                <small class="text-muted">Upload signature image.</small>
            </div>

            <div class="col-md-6">
                <label class="form-label">Seller Signature <span class="text-danger">*</span></label>
                <input type="text" class="form-control mb-2 @error('seller_name') is-invalid @enderror" name="seller_name" placeholder="Seller Name" value="{{ old('seller_name') }}">
                @error('seller_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                <input type="file" class="form-control @error('seller_signature_file') is-invalid @enderror" name="seller_signature_file" accept="image/*">
                @error('seller_signature_file') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Witness #1 <span class="text-danger">*</span></label>
                <input type="text" class="form-control mb-2 @error('witness_one_name') is-invalid @enderror" name="witness_one_name" placeholder="Witness Name" value="{{ old('witness_one_name') }}">
                @error('witness_one_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                <input type="file" class="form-control @error('witness_one_signature_file') is-invalid @enderror" name="witness_one_signature_file" accept="image/*">
                @error('witness_one_signature_file') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Witness #2 <span class="text-danger">*</span></label>
                <input type="text" class="form-control mb-2 @error('witness_two_name') is-invalid @enderror" name="witness_two_name" placeholder="Witness Name" value="{{ old('witness_two_name') }}">
                @error('witness_two_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                <input type="file" class="form-control @error('witness_two_signature_file') is-invalid @enderror" name="witness_two_signature_file" accept="image/*">
                @error('witness_two_signature_file') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary">Save Reservation</button>
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary ms-2">Cancel</a>
        </div>
    </form>

    <div class="mt-5">
        <h4 class="mb-3">Submitted Reservations</h4>
        @if($reservations->isEmpty())
            <div class="alert alert-light border">No reservations yet.</div>
        @else
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Buyer</th>
                                    <th>Listing</th>
                                    <th>Reservation Date</th>
                                    <th>Amount Paid</th>
                                    <th>Created</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($reservations as $reservation)
                                <tr>
                                    <td class="fw-semibold">{{ $reservation->buyer_first_name }} {{ $reservation->buyer_last_name }}</td>
                                    <td>{{ $reservation->listing->unit_code ?? $reservation->listing->room_number }}</td>
                                    <td>{{ optional($reservation->reservation_date)->format('Y-m-d') }}</td>
                                    <td>{{ $reservation->amount_paid_number ? number_format($reservation->amount_paid_number, 2) : '—' }}</td>
                                    <td>{{ $reservation->created_at?->format('Y-m-d H:i') }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>

    @section('scripts')
        <script>
            (function () {
                const listingSelect = document.getElementById('listing_id');
                const amountDisplay = document.getElementById('reservation_amount_display');
                const amountNumberInput = document.getElementById('amount_paid_number');
                const amountTextInput = document.getElementById('amount_paid_text');

                const updateAmount = () => {
                    const option = listingSelect.options[listingSelect.selectedIndex];
                    const value = option ? parseFloat(option.dataset.reservation || '0') : 0;
                    amountDisplay.textContent = value ? new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value) : '—';
                };

                const thNums = ['ศูนย์','หนึ่ง','สอง','สาม','สี่','ห้า','หก','เจ็ด','แปด','เก้า'];
                const thUnits = ['','สิบ','ร้อย','พัน','หมื่น','แสน','ล้าน'];

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
                            if (digit === 1) {
                                text += 'สิบ';
                            } else if (digit === 2) {
                                text += 'ยี่สิบ';
                            } else {
                                text += thNums[digit] + 'สิบ';
                            }
                        } else {
                            text += thNums[digit] + thUnits[unit];
                        }
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
                    if (value < 0) {
                        text = 'ลบ' + text;
                    }
                    return text;
                }

                function syncAmountText() {
                    if (!amountNumberInput || !amountTextInput) return;
                    const raw = parseFloat(amountNumberInput.value);
                    if (Number.isFinite(raw)) {
                        amountTextInput.value = convertNumberToThaiBaht(raw);
                    } else {
                        amountTextInput.value = '';
                    }
                }

                listingSelect.addEventListener('change', updateAmount);
                if (amountNumberInput) {
                    amountNumberInput.addEventListener('input', syncAmountText);
                    amountNumberInput.addEventListener('blur', syncAmountText);
                }
                updateAmount();
                syncAmountText();
            })();
        </script>
    @endsection
@endsection
