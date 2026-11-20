@extends('tablar::page')

@section('title', 'Register Asset')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">Register Asset</h2>
            </div>
            <div class="col-auto ms-auto">
                <a href="{{ url('asset') }}" class="btn btn-outline-secondary">
                    <i class="ti ti-packages me-1"></i> Back to Asset List
                </a>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">New Asset Details</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ url('asset') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label required">Serial No / RFID EPC</label>
                            <input type="text" name="serial_no" value="{{ old('serial_no') }}" class="form-control font-monospace @error('serial_no') is-invalid @enderror" required>
                            @error('serial_no')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required">Asset Code</label>
                            <input type="text" name="asset_code" value="{{ old('asset_code') }}" class="form-control @error('asset_code') is-invalid @enderror" required>
                            @error('asset_code')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required">Asset Name</label>
                            <input type="text" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" required>
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Location</label>
                            <select name="location_id" class="form-select @error('location_id') is-invalid @enderror">
                                <option value="">None</option>
                                @foreach(($locations ?? collect()) as $location)
                                <option value="{{ $location->id }}" {{ (string) old('location_id') === (string) $location->id ? 'selected' : '' }}>
                                    {{ $location->location_name ?? ('Location #' . $location->id) }}
                                </option>
                                @endforeach
                            </select>
                            @error('location_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label required">Initial Status</label>
                            <select name="status" class="form-select">
                                <option value="">All Statuses</option>
                                <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>Available</option>
                                <option value="in_use" {{ request('status') === 'in_use' ? 'selected' : '' }}>In Use</option>
                                <option value="maintenance" {{ request('status') === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                <option value="retired" {{ request('status') === 'retired' ? 'selected' : '' }}>Retired</option>
                            </select>
                            @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-4 d-flex justify-content-end gap-2">
                        <a href="{{ url('asset') }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy me-1"></i> Save Asset
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection