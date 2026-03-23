@extends('layouts.app')

@section('title', 'My Profile')

@section('styles')
<style>
    .profile-layout {
        display: grid;
        grid-template-columns: 30% 1fr;
        gap: 1.5rem;
        align-items: flex-start;
        width: 100%;
    }
    .profile-column {
        width: 100%;
    }
    .profile-left,
    .profile-right {
        max-width: 100%;
    }
    .profile-header {
        background: linear-gradient(135deg, #212529, #343a40);
        border-radius: 16px;
        padding: 2rem;
        color: #fff;
        margin-bottom: 1.5rem;
    }
    .profile-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        border: 3px solid rgba(255,255,255,0.2);
        object-fit: cover;
    }
    .profile-avatar-placeholder {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        border: 3px solid rgba(255,255,255,0.2);
        background: rgba(255,255,255,0.15);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        font-weight: 700;
    }
    .profile-card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    }
    .profile-card .card-header {
        background: #fff;
        border-bottom: 1px solid #f2f4f7;
        border-radius: 16px 16px 0 0 !important;
        padding: 1.25rem 1.5rem;
    }
    .role-badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 600;
        background: rgba(255,255,255,0.15);
    }
    #signatureCanvas {
        border: 1.5px solid #e4e7ec;
        border-radius: 10px;
        cursor: crosshair;
        touch-action: none;
        background: #fff;
        display: block;
        width: 100%;
    }
    .signature-preview img {
        border: 1.5px solid #e4e7ec;
        border-radius: 10px;
        max-height: 120px;
        background: #fff;
    }
    .employee-info-card {
        margin-top: 1.5rem;
    }
    .employee-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 0.75rem;
    }
    .employee-info-chip {
        border: 1px solid #eef1f5;
        border-radius: 12px;
        padding: 0.85rem 1rem;
        background: #f8fafc;
    }
    .employee-info-chip .chip-label {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #98a2b3;
        margin-bottom: 0.25rem;
        font-weight: 600;
    }
    .employee-info-chip .chip-value {
        font-size: 0.9rem;
        font-weight: 600;
        color: #101828;
    }
    @media (max-width: 991.98px) {
        .profile-layout {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('content')
    {{-- Profile Header --}}
    <div class="profile-header">
        <div class="d-flex align-items-center gap-3">
            @if($user->avatar)
                <img src="{{ asset('storage/' . $user->avatar) }}" alt="" class="profile-avatar">
            @else
                <div class="profile-avatar-placeholder">{{ $user->initials }}</div>
            @endif
            <div>
                <h4 class="fw-bold mb-1">{{ $user->name }}</h4>
                <div class="small opacity-75 mb-1">{{ $user->email }}</div>
                <span class="role-badge">{{ ucfirst($user->role) }}</span>
            </div>
            <div class="ms-auto text-end d-none d-md-block">
                <div class="small opacity-50">Member since</div>
                <div class="small">{{ $user->created_at->format('d M Y') }}</div>
                @if($user->last_login_at)
                    <div class="small opacity-50 mt-1">Last login</div>
                    <div class="small">{{ $user->last_login_at->diffForHumans() }}</div>
                @endif
            </div>
        </div>
    </div>

    <div class="profile-layout">
        <div class="profile-column profile-left">
            {{-- Account Info --}}
            <div class="card profile-card">
                <div class="card-header">
                    <h6 class="fw-bold mb-0"><i class="bi bi-info-circle me-2"></i>Account Details</h6>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Role</span>
                        <span class="badge bg-dark">{{ ucfirst($user->role) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Status</span>
                        <span class="badge {{ $user->is_active ? 'bg-success' : 'bg-danger' }}">{{ $user->is_active ? 'Active' : 'Deactivated' }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Member since</span>
                        <span class="small fw-semibold">{{ $user->created_at->format('d M Y') }}</span>
                    </div>
                    @if($user->last_login_at)
                        <div class="d-flex justify-content-between">
                            <span class="text-muted small">Last login</span>
                            <span class="small fw-semibold">{{ $user->last_login_at->format('d M Y, H:i') }}</span>
                        </div>
                    @endif
                    <hr class="my-3">
                    <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Role and account status can only be changed by an administrator.</small>
                </div>
            </div>
        </div>

        <div class="profile-column profile-right">
            {{-- Profile Info --}}
            <div class="card profile-card">
                <div class="card-header">
                    <h6 class="fw-bold mb-0"><i class="bi bi-person me-2"></i>Profile Information</h6>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Avatar</label>
                                <input type="file" name="avatar" class="form-control @error('avatar') is-invalid @enderror" accept="image/*">
                                @error('avatar') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <div class="form-text">Max 2MB. JPG, PNG accepted.</div>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Phone</label>
                                <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $user->phone) }}">
                                @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-dark px-4">
                                <i class="bi bi-check-lg me-1"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Employee Info (read-only) --}}
            @if($employee)
                <div class="card profile-card employee-info-card">
                    <div class="card-header text-center">
                        <h6 class="fw-bold mb-0"><i class="bi bi-briefcase me-2"></i>Employee Information</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="employee-info-grid">
                            <div class="employee-info-chip">
                                <div class="chip-label">Employee Code</div>
                                <div class="chip-value">{{ $employee->employee_code }}</div>
                            </div>
                            <div class="employee-info-chip">
                                <div class="chip-label">Position</div>
                                <div class="chip-value">{{ $employee->position->name ?? '-' }}</div>
                            </div>
                            <div class="employee-info-chip">
                                <div class="chip-label">Team</div>
                                <div class="chip-value">{{ $employee->team->name ?? '-' }}</div>
                            </div>
                            <div class="employee-info-chip">
                                <div class="chip-label">Employment Type</div>
                                <div class="chip-value">{{ ucfirst(str_replace('_', ' ', $employee->employment_type)) }}</div>
                            </div>
                            <div class="employee-info-chip">
                                <div class="chip-label">Hire Date</div>
                                <div class="chip-value">{{ $employee->hire_date?->format('d M Y') ?? '-' }}</div>
                            </div>
                            <div class="employee-info-chip">
                                <div class="chip-label">Status</div>
                                @php $sc = ['active'=>'success','probation'=>'warning','inactive'=>'secondary','resigned'=>'danger']; @endphp
                                <span class="badge bg-{{ $sc[$employee->status] ?? 'dark' }}">{{ ucfirst($employee->status) }}</span>
                            </div>
                        </div>
                        <div class="mt-3 text-center">
                            <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Employee information can only be updated by an administrator.</small>
                        </div>
                    </div>
                </div>
            @endif
            {{-- Change Password --}}
            <div class="card profile-card mt-4">
                <div class="card-header">
                    <h6 class="fw-bold mb-0"><i class="bi bi-shield-lock me-2"></i>Change Password</h6>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('profile.password') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Current Password <span class="text-danger">*</span></label>
                            <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" required>
                            @error('current_password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">New Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required minlength="6">
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <div class="form-text">Minimum 6 characters.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Confirm New Password <span class="text-danger">*</span></label>
                            <input type="password" name="password_confirmation" class="form-control" required minlength="6">
                        </div>

                        <button type="submit" class="btn btn-outline-dark w-100">
                            <i class="bi bi-key me-1"></i> Update Password
                        </button>
                    </form>
                </div>
            </div>
            {{-- Signature Pad --}}
            <div class="card profile-card mt-4">
        	    <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0"><i class="bi bi-pen me-2"></i>Signature</h6>
                    @if($user->signature)
                        <span class="badge bg-success-subtle text-success">Saved</span>
                    @endif
                </div>
                <div class="card-body p-4">
                    @if($user->signature)
                        <div class="mb-3 signature-preview">
                            <div class="text-muted small mb-2">Current signature</div>
                            <img src="{{ asset('storage/' . $user->signature) }}" alt="Signature">
                        </div>
                        <div class="text-muted small mb-3">Draw a new signature below to replace the current one.</div>
                    @else
                        <div class="text-muted small mb-3">Draw your signature below using mouse or finger.</div>
                    @endif

                    <canvas id="signatureCanvas" height="180"></canvas>

                    <div class="d-flex gap-2 mt-3">
                        <form id="signatureForm" action="{{ route('profile.signature') }}" method="POST">
                            @csrf
                            <input type="hidden" name="signature" id="signatureData">
                            <button type="submit" class="btn btn-dark" id="btnSaveSignature">
                                <i class="bi bi-check-lg me-1"></i> Save Signature
                            </button>
                        </form>
                        <button type="button" class="btn btn-outline-secondary" id="btnClearSignature">
                            <i class="bi bi-eraser me-1"></i> Clear
                        </button>
                    </div>
                </div>
            </div>
    </div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const canvas = document.getElementById('signatureCanvas');
    const form = document.getElementById('signatureForm');
    const signatureData = document.getElementById('signatureData');
    const btnClear = document.getElementById('btnClearSignature');

    if (!canvas) return;

    function resizeCanvas() {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        const displayWidth = canvas.offsetWidth;
        const displayHeight = 180;
        canvas.width = displayWidth * ratio;
        canvas.height = displayHeight * ratio;
        canvas.style.height = displayHeight + 'px';
        canvas.getContext('2d').scale(ratio, ratio);
        signaturePad.clear();
    }

    const signaturePad = new SignaturePad(canvas, {
        backgroundColor: 'rgb(255, 255, 255)',
        penColor: 'rgb(0, 0, 0)',
        minWidth: 1,
        maxWidth: 2.5,
    });

    resizeCanvas();
    window.addEventListener('resize', resizeCanvas);

    btnClear.addEventListener('click', function () {
        signaturePad.clear();
    });

    form.addEventListener('submit', function (e) {
        if (signaturePad.isEmpty()) {
            e.preventDefault();
            alert('Please draw your signature before saving.');
            return;
        }
        signatureData.value = signaturePad.toDataURL('image/png');
    });
});
</script>
@endsection
