@extends('layouts.app')

@section('title', 'Purchase Contract')

@section('content')
    <h2 class="mb-4">Purchase Contract</h2>

    <form action="{{ route('contracts.purchase.store') }}" method="POST">
        @csrf
        <input type="hidden" name="type" value="purchase">

        <div class="row g-3">
            <div class="col-md-6">
                <label for="buyer_name" class="form-label">Buyer Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('buyer_name') is-invalid @enderror" id="buyer_name" name="buyer_name" value="{{ old('buyer_name') }}">
                @error('buyer_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label for="id_number" class="form-label">ID Number <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('id_number') is-invalid @enderror" id="id_number" name="id_number" value="{{ old('id_number') }}">
                @error('id_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label for="phone" class="form-label">Phone <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}">
                @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}">
                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4">
                <label for="unit_number" class="form-label">Unit Number <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('unit_number') is-invalid @enderror" id="unit_number" name="unit_number" value="{{ old('unit_number') }}">
                @error('unit_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4">
                <label for="price" class="form-label">Price <span class="text-danger">*</span></label>
                <input type="number" step="0.01" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price') }}">
                @error('price') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4">
                <label for="deposit" class="form-label">Deposit</label>
                <input type="number" step="0.01" class="form-control @error('deposit') is-invalid @enderror" id="deposit" name="deposit" value="{{ old('deposit') }}">
                @error('deposit') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4">
                <label for="contract_date" class="form-label">Contract Date <span class="text-danger">*</span></label>
                <input type="date" class="form-control @error('contract_date') is-invalid @enderror" id="contract_date" name="contract_date" value="{{ old('contract_date') }}">
                @error('contract_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-success">Save Purchase Contract</button>
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary ms-2">Cancel</a>
        </div>
    </form>

    <div class="mt-5">
        <h4 class="mb-3">Submitted Purchase Contracts</h4>
        @if($contracts->isEmpty())
            <div class="alert alert-light border">No purchase contracts yet.</div>
        @else
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Buyer</th>
                                    <th>Unit</th>
                                    <th>Contract Date</th>
                                    <th>Created</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($contracts as $contract)
                                <tr>
                                    <td class="fw-semibold">{{ $contract->buyer_name }}</td>
                                    <td>{{ $contract->unit_number }}</td>
                                    <td>{{ optional($contract->contract_date)->format('Y-m-d') }}</td>
                                    <td>{{ $contract->created_at?->format('Y-m-d H:i') }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('contracts.preview', $contract) }}" class="btn btn-sm btn-outline-primary">View PDF</a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
