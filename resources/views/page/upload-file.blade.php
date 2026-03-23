@extends('layouts.app')

@section('title', 'Upload PDF Template')

@section('styles')
<style>
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .form-section {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        padding: 0;
        margin-bottom: 1.25rem;
    }
    .form-section-header {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--border);
        font-weight: 700;
        font-size: 0.9rem;
        color: var(--text-dark);
        background: var(--bg);
        border-radius: var(--radius-lg) var(--radius-lg) 0 0;
    }
    .form-section-header i {
        font-size: 1.05rem;
        color: var(--primary);
    }
    .form-section-body {
        padding: 1.25rem;
    }

    .upload-zone {
        border: 2px dashed var(--border);
        border-radius: var(--radius);
        padding: 2.5rem 1.5rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s;
        background: var(--bg);
    }
    .upload-zone:hover {
        border-color: var(--primary);
        background: var(--primary-muted);
    }
    .upload-zone.dragover {
        border-color: var(--primary);
        background: rgba(42,139,146,0.08);
    }
    .upload-zone i {
        font-size: 2.5rem;
        color: var(--text-light);
        display: block;
        margin-bottom: 0.75rem;
    }
    .upload-zone p {
        color: var(--text-mid);
        font-size: 0.875rem;
        margin: 0;
    }
    .upload-zone .file-name {
        display: none;
        font-weight: 600;
        color: var(--primary);
        margin-top: 0.5rem;
        font-size: 0.85rem;
    }

    .form-footer {
        display: flex;
        gap: 0.75rem;
        justify-content: flex-end;
    }
    .form-footer .btn {
        border-radius: var(--radius-sm);
        font-weight: 600;
        font-size: 0.875rem;
        padding: 0.55rem 1.5rem;
    }
    .form-footer .btn-primary {
        background: var(--primary);
        border: none;
    }
    .form-footer .btn-primary:hover {
        background: var(--primary-dark);
    }
</style>
@endsection

@section('content')
    <div class="page-header">
        <h3 class="fw-bold mb-0">Upload PDF Template</h3>
        <a href="{{ route('templates.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back to Templates
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <form action="{{ route('upload-template.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="form-section">
                    <div class="form-section-header">
                        <i class="bi bi-gear"></i> Template Settings
                    </div>
                    <div class="form-section-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="contract_type" class="form-label fw-semibold">Contract Type <span class="text-danger">*</span></label>
                                <select class="form-select @error('contract_type') is-invalid @enderror" id="contract_type" name="contract_type">
                                    <option value="">-- Select --</option>
                                    <option value="quotation" {{ old('contract_type') == 'quotation' ? 'selected' : '' }}>Quotation</option>
                                    <option value="reservation_agreement" {{ old('contract_type') == 'reservation_agreement' ? 'selected' : '' }}>Reservation Agreement</option>
                                    <option value="addendum_to_agreement" {{ old('contract_type') == 'addendum_to_agreement' ? 'selected' : '' }}>Addendum to Agreement to Sell and Purchase</option>
                                    <option value="agreement_to_sell_and_purchase" {{ old('contract_type') == 'agreement_to_sell_and_purchase' ? 'selected' : '' }}>Agreement to Sell and Purchase</option>
                                    <option value="contract_amendment" {{ old('contract_type') == 'contract_amendment' ? 'selected' : '' }}>Contract Amendment</option>
                                    <option value="overdue_installment_reminder_1" {{ old('contract_type') == 'overdue_installment_reminder_1' ? 'selected' : '' }}>Overdue Installment Reminder (1st Notice)</option>
                                    <option value="overdue_installment_reminder_2" {{ old('contract_type') == 'overdue_installment_reminder_2' ? 'selected' : '' }}>Overdue Installment Reminder (2nd Notice)</option>
                                    <option value="property_ownership_transfer_appointment" {{ old('contract_type') == 'property_ownership_transfer_appointment' ? 'selected' : '' }}>Property Ownership Transfer Appointment</option>
                                    <option value="contract_termination_and_forfeiture" {{ old('contract_type') == 'contract_termination_and_forfeiture' ? 'selected' : '' }}>Contract Termination and Forfeiture Notice</option>
                                    <option value="deal_slip" {{ old('contract_type') == 'deal_slip' ? 'selected' : '' }}>Deal Slip</option>
                                </select>
                                @error('contract_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="language" class="form-label fw-semibold">Language <span class="text-danger">*</span></label>
                                <select class="form-select @error('language') is-invalid @enderror" id="language" name="language">
                                    <option value="">-- Select --</option>
                                    <option value="th" {{ old('language') == 'th' ? 'selected' : '' }}>Thai</option>
                                    <option value="en" {{ old('language') == 'en' ? 'selected' : '' }}>English</option>
                                </select>
                                @error('language') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-header">
                        <i class="bi bi-file-earmark-pdf"></i> PDF File
                    </div>
                    <div class="form-section-body">
                        <div class="upload-zone" id="uploadZone" onclick="document.getElementById('file').click()">
                            <i class="bi bi-cloud-arrow-up"></i>
                            <p>Click or drag & drop your PDF file here</p>
                            <small class="text-muted">Max file size: 10MB</small>
                            <div class="file-name" id="fileName"></div>
                        </div>
                        <input type="file" class="d-none @error('file') is-invalid @enderror" id="file" name="file" accept=".pdf">
                        @error('file') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="form-footer">
                    <a href="{{ route('templates.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-upload me-1"></i>Upload Template
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
<script>
(function() {
    const zone = document.getElementById('uploadZone');
    const fileInput = document.getElementById('file');
    const fileNameEl = document.getElementById('fileName');

    fileInput.addEventListener('change', function() {
        if (this.files.length) {
            fileNameEl.textContent = this.files[0].name;
            fileNameEl.style.display = 'block';
            zone.querySelector('i').className = 'bi bi-file-earmark-pdf-fill';
            zone.querySelector('i').style.color = '#dc3545';
        }
    });

    zone.addEventListener('dragover', function(e) {
        e.preventDefault();
        zone.classList.add('dragover');
    });
    zone.addEventListener('dragleave', function() {
        zone.classList.remove('dragover');
    });
    zone.addEventListener('drop', function(e) {
        e.preventDefault();
        zone.classList.remove('dragover');
        if (e.dataTransfer.files.length) {
            fileInput.files = e.dataTransfer.files;
            fileInput.dispatchEvent(new Event('change'));
        }
    });
})();
</script>
@endsection
