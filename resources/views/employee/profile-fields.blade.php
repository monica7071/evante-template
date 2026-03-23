@extends('layouts.app')

@section('title', 'Profile Information')

@section('styles')
<style>
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }
    .btn-add-field {
        background: var(--primary);
        border: none;
        color: #fff;
        border-radius: var(--radius-sm);
        padding: 0.55rem 1.2rem;
        font-weight: 600;
        font-size: 0.85rem;
        transition: all 0.15s;
    }
    .btn-add-field:hover {
        background: var(--primary-dark);
        color: #fff;
        box-shadow: var(--shadow-sm);
    }

    /* Group section */
    .field-group {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        margin-bottom: 1.25rem;
        overflow: hidden;
    }
    .field-group-header {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--border);
        background: var(--bg);
    }
    .field-group-header i {
        font-size: 1.1rem;
        color: var(--primary);
    }
    .field-group-header h6 {
        margin: 0;
        font-weight: 700;
        font-size: 0.9rem;
        color: var(--text-dark);
    }
    .field-group-header .badge {
        font-size: 0.7rem;
        font-weight: 600;
        background: var(--primary-muted);
        color: var(--primary);
        border-radius: 999px;
        padding: 0.25rem 0.6rem;
    }

    /* Field rows */
    .field-row {
        display: flex;
        align-items: center;
        padding: 0.75rem 1.25rem;
        border-bottom: 1px solid rgba(0,0,0,0.04);
        transition: background 0.12s;
    }
    .field-row:last-child { border-bottom: none; }
    .field-row:hover { background: rgba(42,139,146,0.03); }

    .field-labels {
        flex: 1;
        min-width: 0;
    }
    .field-label-en {
        font-weight: 600;
        font-size: 0.875rem;
        color: var(--text-dark);
    }
    .field-label-th {
        font-size: 0.78rem;
        color: var(--text-light);
        margin-top: 1px;
    }

    .field-type-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.2rem 0.55rem;
        border-radius: 999px;
        background: var(--cream);
        color: var(--text-mid);
        border: 1px solid var(--border);
        min-width: 56px;
        justify-content: center;
    }

    .field-controls {
        display: flex;
        align-items: center;
        gap: 1.25rem;
        margin-left: 1rem;
    }

    .toggle-wrap {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 2px;
    }
    .toggle-label {
        font-size: 0.65rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--text-light);
    }
    .toggle-btn {
        background: none;
        border: none;
        padding: 0;
        cursor: pointer;
        line-height: 1;
        transition: transform 0.15s;
    }
    .toggle-btn:hover { transform: scale(1.15); }
    .toggle-btn .bi { font-size: 1.5rem; }
    .toggle-on { color: var(--primary); }
    .toggle-off { color: #c5cdd3; }

    /* Modal */
    .modal-content {
        border: none;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-lg);
    }
    .modal-header {
        border-bottom: 1px solid var(--border);
        padding: 1.25rem 1.5rem;
    }
    .modal-title {
        font-weight: 700;
        font-size: 1rem;
        color: var(--text-dark);
    }
    .modal-body {
        padding: 1.5rem;
    }
    .modal-footer {
        border-top: 1px solid var(--border);
        padding: 1rem 1.5rem;
    }
    .modal-footer .btn-primary {
        background: var(--primary);
        border: none;
        border-radius: var(--radius-sm);
        font-weight: 600;
    }
    .modal-footer .btn-primary:hover {
        background: var(--primary-dark);
    }
</style>
@endsection

@section('content')
    <div class="page-header">
        <h3 class="fw-bold mb-0">Profile Information</h3>
        <button class="btn-add-field" data-bs-toggle="modal" data-bs-target="#addFieldModal">
            <i class="bi bi-plus-lg me-1"></i> Add Custom Field
        </button>
    </div>

    @php
        $groupLabels = ['personal' => 'Personal', 'contact' => 'Contact', 'document' => 'Document', 'bank' => 'Bank', 'other' => 'Other'];
        $groupIcons = ['personal' => 'bi-person', 'contact' => 'bi-telephone', 'document' => 'bi-file-earmark', 'bank' => 'bi-bank', 'other' => 'bi-grid'];
        $typeIcons = ['text' => 'bi-fonts', 'number' => 'bi-123', 'date' => 'bi-calendar3', 'select' => 'bi-list-ul', 'file' => 'bi-paperclip', 'textarea' => 'bi-text-paragraph'];
    @endphp

    @foreach($groupLabels as $groupKey => $groupLabel)
        @if(isset($groups[$groupKey]))
            <div class="field-group">
                <div class="field-group-header">
                    <i class="bi {{ $groupIcons[$groupKey] ?? 'bi-grid' }}"></i>
                    <h6>{{ $groupLabel }}</h6>
                    <span class="badge">{{ count($groups[$groupKey]) }} fields</span>
                </div>
                @foreach($groups[$groupKey] as $field)
                    <div class="field-row">
                        <div class="field-labels">
                            <div class="field-label-en">{{ $field->field_label }}</div>
                            @if($field->field_label_th)
                                <div class="field-label-th">{{ $field->field_label_th }}</div>
                            @endif
                        </div>

                        <span class="field-type-badge">
                            <i class="bi {{ $typeIcons[$field->field_type] ?? 'bi-dash' }}"></i>
                            {{ ucfirst($field->field_type) }}
                        </span>

                        <div class="field-controls">
                            <div class="toggle-wrap">
                                <span class="toggle-label">Active</span>
                                <form action="{{ route('employee.profile-info.toggle', [$field, 'is_active']) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="toggle-btn">
                                        <i class="bi {{ $field->is_active ? 'bi-toggle-on toggle-on' : 'bi-toggle-off toggle-off' }}"></i>
                                    </button>
                                </form>
                            </div>
                            <div class="toggle-wrap">
                                <span class="toggle-label">Required</span>
                                <form action="{{ route('employee.profile-info.toggle', [$field, 'is_required']) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="toggle-btn">
                                        <i class="bi {{ $field->is_required ? 'bi-toggle-on toggle-on' : 'bi-toggle-off toggle-off' }}"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    @endforeach

    {{-- Add Field Modal --}}
    <div class="modal fade" id="addFieldModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('employee.profile-info.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-plus-circle me-2 text-primary"></i>Add Custom Field</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Field Name <span class="text-danger">*</span></label>
                            <input type="text" name="field_name" class="form-control" placeholder="e.g. custom_field" required>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Label (EN) <span class="text-danger">*</span></label>
                                <input type="text" name="field_label" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Label (TH)</label>
                                <input type="text" name="field_label_th" class="form-control">
                            </div>
                        </div>
                        <div class="row g-3 mt-0">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Type <span class="text-danger">*</span></label>
                                <select name="field_type" class="form-select" required>
                                    <option value="text">Text</option>
                                    <option value="number">Number</option>
                                    <option value="date">Date</option>
                                    <option value="select">Select</option>
                                    <option value="file">File</option>
                                    <option value="textarea">Textarea</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Group <span class="text-danger">*</span></label>
                                <select name="field_group" class="form-select" required>
                                    @foreach($groupLabels as $k => $l)
                                        <option value="{{ $k }}">{{ $l }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mb-3 mt-3">
                            <label class="form-label fw-semibold">Options <small class="text-muted fw-normal">(comma-separated, for select type)</small></label>
                            <input type="text" name="options" class="form-control" placeholder="Option 1, Option 2, Option 3">
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_required" value="1" id="isRequired">
                            <label class="form-check-label fw-semibold" for="isRequired">Required field</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check-lg me-1"></i>Add Field
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
