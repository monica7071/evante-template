@extends('layouts.app')

@section('title', $isEdit ? 'Edit Employee' : 'Add Employee')

@section('content')
    <div class="mb-3">
        <a href="{{ route('employee.list.index') }}" class="text-decoration-none text-muted">
            <i class="bi bi-arrow-left me-1"></i> Back to Employees
        </a>
    </div>

    <div class="card border-0 shadow-sm" style="border-radius:16px;">
        <div class="card-body p-0">
            <form action="{{ $isEdit ? route('employee.list.update', $employee) : route('employee.list.store') }}"
                  method="POST" enctype="multipart/form-data">
                @csrf
                @if($isEdit) @method('PUT') @endif

                {{-- Tabs --}}
                <ul class="nav nav-tabs px-4 pt-3" id="empTabs" role="tablist">
                    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tabPersonal">Personal</a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabContact">Contact</a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabWork">Work</a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabBank">Bank</a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabAccount">Account</a></li>
                </ul>

                <div class="tab-content p-4">
                    {{-- Tab 1: Personal --}}
                    <div class="tab-pane fade show active" id="tabPersonal">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Avatar</label>
                                <div class="d-flex align-items-center gap-3">
                                    @if($employee->avatar)
                                        <img src="{{ asset('storage/' . $employee->avatar) }}" class="rounded-circle" style="width:60px;height:60px;object-fit:cover;">
                                    @endif
                                    <input type="file" name="avatar" class="form-control" accept="image/*" style="max-width:300px;">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Prefix</label>
                                <select name="prefix" class="form-select">
                                    <option value="">-</option>
                                    @foreach(['Mr.','Ms.','Mrs.','นาย','นาง','นางสาว'] as $p)
                                        <option value="{{ $p }}" {{ old('prefix', $employee->prefix) == $p ? 'selected' : '' }}>{{ $p }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label fw-semibold">First Name <span class="text-danger">*</span></label>
                                <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror" value="{{ old('first_name', $employee->first_name) }}" required>
                                @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-5">
                                <label class="form-label fw-semibold">Last Name <span class="text-danger">*</span></label>
                                <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror" value="{{ old('last_name', $employee->last_name) }}" required>
                                @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-5">
                                <label class="form-label fw-semibold">First Name (TH)</label>
                                <input type="text" name="first_name_th" class="form-control" value="{{ old('first_name_th', $employee->first_name_th) }}">
                            </div>
                            <div class="col-md-5">
                                <label class="form-label fw-semibold">Last Name (TH)</label>
                                <input type="text" name="last_name_th" class="form-control" value="{{ old('last_name_th', $employee->last_name_th) }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Nickname</label>
                                <input type="text" name="nickname" class="form-control" value="{{ old('nickname', $employee->nickname) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Gender</label>
                                <select name="gender" class="form-select">
                                    <option value="">-</option>
                                    @foreach(['male'=>'Male','female'=>'Female','other'=>'Other'] as $k => $v)
                                        <option value="{{ $k }}" {{ old('gender', $employee->gender) == $k ? 'selected' : '' }}>{{ $v }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Date of Birth</label>
                                <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth', $employee->date_of_birth?->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">National ID</label>
                                <input type="text" name="national_id" class="form-control" value="{{ old('national_id', $employee->national_id) }}">
                            </div>
                        </div>
                    </div>

                    {{-- Tab 2: Contact --}}
                    <div class="tab-pane fade" id="tabContact">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Phone</label>
                                <input type="text" name="phone" class="form-control" value="{{ old('phone', $employee->phone) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Email</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email', $employee->email) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">LINE ID</label>
                                <input type="text" name="line_id" class="form-control" value="{{ old('line_id', $employee->line_id) }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Address</label>
                                <textarea name="address" rows="3" class="form-control">{{ old('address', $employee->address) }}</textarea>
                            </div>
                        </div>
                    </div>

                    {{-- Tab 3: Work --}}
                    <div class="tab-pane fade" id="tabWork">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Employee Code</label>
                                <input type="text" class="form-control" value="{{ $employee->employee_code ?? 'Auto-generated' }}" disabled>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Position</label>
                                <select name="position_id" class="form-select">
                                    <option value="">-- Select --</option>
                                    @foreach($positions as $pos)
                                        <option value="{{ $pos->id }}" {{ old('position_id', $employee->position_id) == $pos->id ? 'selected' : '' }}>{{ $pos->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Team</label>
                                <select name="team_id" class="form-select">
                                    <option value="">-- Select --</option>
                                    @foreach($teams as $team)
                                        <option value="{{ $team->id }}" {{ old('team_id', $employee->team_id) == $team->id ? 'selected' : '' }}>{{ $team->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Hire Date</label>
                                <input type="date" name="hire_date" class="form-control" value="{{ old('hire_date', $employee->hire_date?->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">End Date</label>
                                <input type="date" name="end_date" class="form-control" value="{{ old('end_date', $employee->end_date?->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Employment Type <span class="text-danger">*</span></label>
                                <select name="employment_type" class="form-select" required>
                                    @foreach(['full_time'=>'Full Time','part_time'=>'Part Time','contract'=>'Contract','intern'=>'Intern'] as $k => $v)
                                        <option value="{{ $k }}" {{ old('employment_type', $employee->employment_type ?? 'full_time') == $k ? 'selected' : '' }}>{{ $v }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-select" required>
                                    @foreach(['active'=>'Active','probation'=>'Probation','inactive'=>'Inactive','resigned'=>'Resigned'] as $k => $v)
                                        <option value="{{ $k }}" {{ old('status', $employee->status ?? 'active') == $k ? 'selected' : '' }}>{{ $v }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Salary</label>
                                <input type="number" step="0.01" min="0" name="salary" class="form-control" value="{{ old('salary', $employee->salary) }}">
                            </div>
                        </div>
                    </div>

                    {{-- Tab 4: Bank --}}
                    <div class="tab-pane fade" id="tabBank">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Bank Name</label>
                                <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name', $employee->bank_name) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Branch</label>
                                <input type="text" name="bank_branch" class="form-control" value="{{ old('bank_branch', $employee->bank_branch) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Account Number</label>
                                <input type="text" name="account_number" class="form-control" value="{{ old('account_number', $employee->account_number) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Account Name</label>
                                <input type="text" name="account_name" class="form-control" value="{{ old('account_name', $employee->account_name) }}">
                            </div>
                        </div>
                    </div>

                    {{-- Tab 5: Account --}}
                    <div class="tab-pane fade" id="tabAccount">
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="create_account" value="1" id="createAccountToggle"
                                       {{ $employee->user_id ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="createAccountToggle">Create Login Account</label>
                            </div>
                        </div>

                        <div id="accountFields" class="{{ $employee->user_id ? '' : 'd-none' }}">
                            @if($isEdit && $employee->user)
                                <div class="alert alert-info py-2 small">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Account status: <strong>{{ $employee->user->is_active ? 'Active' : 'Deactivated' }}</strong>
                                    &middot; Role: <strong>{{ ucfirst($employee->user->role) }}</strong>
                                </div>
                            @endif

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                                    <input type="email" name="account_email" id="accountEmail" class="form-control"
                                           value="{{ old('account_email', $employee->user->email ?? $employee->email ?? '') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
                                    <select name="account_role" class="form-select">
                                        @foreach(['admin'=>'Admin','agent'=>'Agent','leader'=>'Leader'] as $k => $v)
                                            <option value="{{ $k }}" {{ old('account_role', $employee->user->role ?? 'agent') == $k ? 'selected' : '' }}>{{ $v }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Password {{ $isEdit && $employee->user ? '(leave blank to keep)' : '' }}</label>
                                    <input type="password" name="account_password" class="form-control" minlength="6"
                                           {{ !$isEdit || !$employee->user ? 'required' : '' }}>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-4 pb-4 d-flex justify-content-between">
                    <a href="{{ route('employee.list.index') }}" class="btn btn-light px-4">Cancel</a>
                    <button type="submit" class="btn btn-dark px-4">
                        <i class="bi bi-check-lg me-1"></i> {{ $isEdit ? 'Update' : 'Create' }} Employee
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
<script>
document.getElementById('createAccountToggle').addEventListener('change', function() {
    document.getElementById('accountFields').classList.toggle('d-none', !this.checked);
});
</script>
@endsection
