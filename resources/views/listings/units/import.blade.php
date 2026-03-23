@extends('layouts.app')

@section('title', 'Import Listings')

@section('styles')
<style>
    .upload-zone {
        border: 2px dashed var(--border);
        border-radius: var(--radius);
        padding: 2.5rem;
        text-align: center;
        background: var(--cream);
        cursor: pointer;
        transition: border-color .2s, background .2s;
    }
    .upload-zone:hover, .upload-zone.drag-over {
        border-color: var(--primary);
        background: var(--primary-muted);
    }
    .upload-zone .icon { font-size: 2.5rem; color: var(--text-light); }
    .col-rule { font-size: .82rem; color: #667; }
    .col-rule code { background: #f1f3f5; padding: 1px 5px; border-radius: 4px; font-size: .8rem; }
    .badge-req { background: #fee2e2; color: #b91c1c; font-size: .72rem; padding: 2px 6px; border-radius: 4px; }
    .badge-opt { background: #e0f2fe; color: #0369a1; font-size: .72rem; padding: 2px 6px; border-radius: 4px; }
</style>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold mb-0">Import Listings from Excel</h3>
    <div class="d-flex gap-2">
        <a href="{{ route('units.template') }}" class="btn btn-sm btn-outline-success">
            <i class="bi bi-file-earmark-arrow-down me-1"></i> Download Template
        </a>
        <a href="{{ route('units.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

@if(session('import_errors') && count(session('import_errors')))
<div class="alert alert-warning">
    <strong><i class="bi bi-exclamation-triangle me-1"></i> Some rows were skipped:</strong>
    <ul class="mb-0 mt-1">
        @foreach(session('import_errors') as $err)
            <li>{{ $err }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="row g-4">
    {{-- Upload form --}}
    <div class="col-lg-5">
        <div class="card">
            <div class="card-body">
                <h6 class="fw-semibold mb-3">Upload File</h6>
                <form method="POST" action="{{ route('units.import') }}" enctype="multipart/form-data" id="importForm">
                    @csrf
                    <div class="upload-zone mb-3" id="dropZone" onclick="document.getElementById('fileInput').click()">
                        <div class="icon mb-2"><i class="bi bi-file-earmark-spreadsheet"></i></div>
                        <div id="fileName" class="text-muted" style="font-size:.9rem">
                            Click to select or drag & drop file here
                        </div>
                        <div class="text-muted mt-1" style="font-size:.78rem">.xlsx / .xls / .csv — max 10MB</div>
                        <input type="file" name="file" id="fileInput" accept=".xlsx,.xls,.csv" class="d-none" required>
                    </div>
                    @error('file')
                        <div class="text-danger small mb-2">{{ $message }}</div>
                    @enderror
                    <button type="submit" class="btn btn-dark w-100">
                        <i class="bi bi-upload me-1"></i> Import
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Column guide --}}
    <div class="col-lg-7">
        <div class="card">
            <div class="card-body">
                <h6 class="fw-semibold mb-1">Column Guide</h6>
                <p class="text-muted mb-3" style="font-size:.82rem">
                    Row 1 ต้องเป็น header ตามนี้เท่านั้น (case-insensitive) — ดาวน์โหลด template เพื่อใช้เป็นต้นแบบ
                </p>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0" style="font-size:.82rem">
                        <thead class="table-light">
                            <tr>
                                <th>Column</th>
                                <th>ประเภท</th>
                                <th>ตัวอย่าง / หมายเหตุ</th>
                            </tr>
                        </thead>
                        <tbody class="col-rule">
                            <tr><td><code>location</code></td><td><span class="badge-req">required</span></td><td>ชื่อ Location ที่มีในระบบ (ตรง project_name)</td></tr>
                            <tr><td><code>project_name</code></td><td><span class="badge-req">required</span></td><td>ชื่อ Project ที่มีในระบบ</td></tr>
                            <tr><td><code>room_number</code></td><td><span class="badge-req">required</span></td><td>เลขห้อง เช่น <code>501</code></td></tr>
                            <tr><td><code>floor</code></td><td><span class="badge-opt">optional</span></td><td>ตัวเลขชั้น เช่น <code>5</code></td></tr>
                            <tr><td><code>unit_code</code></td><td><span class="badge-opt">optional</span></td><td>รหัสยูนิต เช่น <code>A501</code> — ซ้ำไม่ได้ / ถ้าว่างระบบ generate ให้อัตโนมัติ เช่น ตึก Sky, ชั้น 5, ห้อง 01 → <code>S501</code></td></tr>
                            <tr><td><code>bedrooms</code></td><td><span class="badge-opt">optional</span></td><td><code>1</code> / <code>2</code> / <code>Studio</code></td></tr>
                            <tr><td><code>area</code></td><td><span class="badge-opt">optional</span></td><td>ตัวเลข เช่น <code>45.50</code></td></tr>
                            <tr><td><code>price_per_room</code></td><td><span class="badge-opt">optional</span></td><td>ราคาต่อยูนิต เช่น <code>3500000</code></td></tr>
                            <tr><td><code>price_per_sqm</code></td><td><span class="badge-opt">optional</span></td><td>ราคาต่อ ตร.ม.</td></tr>
                            <tr><td><code>unit_type</code></td><td><span class="badge-opt">optional</span></td><td>เช่น <code>Condo</code> / <code>Studio</code></td></tr>
                            <tr><td><code>status</code></td><td><span class="badge-opt">optional</span></td><td><code>available</code> (default) / <code>reserved</code> / <code>contract</code> / <code>installment</code> / <code>transferred</code></td></tr>
                            <tr><td><code>reservation_deposit</code></td><td><span class="badge-opt">auto</span></td><td>ว่างไว้ → คำนวณจาก price × 0.25%</td></tr>
                            <tr><td><code>contract_payment</code></td><td><span class="badge-opt">auto</span></td><td>ว่างไว้ → คำนวณจาก price × 2.75%</td></tr>
                            <tr><td><code>installment_15_terms</code></td><td><span class="badge-opt">auto</span></td><td>ว่างไว้ → price × 9.5%</td></tr>
                            <tr><td><code>installment_12_terms</code></td><td><span class="badge-opt">auto</span></td><td>ว่างไว้ → คำนวณจาก installment_15</td></tr>
                            <tr><td><code>special_installment_3_terms</code></td><td><span class="badge-opt">auto</span></td><td>ว่างไว้ → คำนวณจาก installment_15</td></tr>
                            <tr><td><code>transfer_amount</code></td><td><span class="badge-opt">auto</span></td><td>ว่างไว้ → price × 87.5%</td></tr>
                            <tr><td><code>transfer_fee</code></td><td><span class="badge-opt">auto</span></td><td>ว่างไว้ → (price × 2%) ÷ 2</td></tr>
                            <tr><td><code>annual_common_fee</code></td><td><span class="badge-opt">auto</span></td><td>ว่างไว้ → area × 55 × 12</td></tr>
                            <tr><td><code>sinking_fund</code></td><td><span class="badge-opt">auto</span></td><td>ว่างไว้ → area × 650</td></tr>
                            <tr><td><code>utility_fee</code></td><td><span class="badge-req">manual</span></td><td>ต้องใส่เอง — ค่ามิเตอร์ไฟ/น้ำ</td></tr>
                            <tr><td><code>total_misc_fee</code></td><td><span class="badge-opt">auto</span></td><td>ว่างไว้ → transfer_fee + common + sinking + utility</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
const dropZone  = document.getElementById('dropZone');
const fileInput = document.getElementById('fileInput');
const fileName  = document.getElementById('fileName');

fileInput.addEventListener('change', () => {
    fileName.textContent = fileInput.files[0]?.name ?? 'No file selected';
});

dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.classList.add('drag-over'); });
dropZone.addEventListener('dragleave', () => dropZone.classList.remove('drag-over'));
dropZone.addEventListener('drop', e => {
    e.preventDefault();
    dropZone.classList.remove('drag-over');
    const file = e.dataTransfer.files[0];
    if (file) {
        const dt = new DataTransfer();
        dt.items.add(file);
        fileInput.files = dt.files;
        fileName.textContent = file.name;
    }
});
</script>
@endsection
