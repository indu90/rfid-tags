@extends('tablar::page')

@section('title', 'RFID TAGES | Add Location')

@section('content')
<div class="page-body mt-3">
    <div class="container-xl">

        <!-- Welcome / Flash Banner -->
        <div class="alert alert-success border-0 mb-3 py-2 px-3" style="background-color: #d4edda; color: #155724; font-size: 14px;">
            {{ session('success') ?? 'Welcome Admin User To Site Manager' }}
        </div>

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible mb-3" role="alert">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
            </div>
        @endif

        <div class="d-flex justify-content-center">
            <div class="card shadow-sm border-0 w-100" style="max-width: 800px;">
            <!-- Card Header -->
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-grid-dots me-2 text-dark" width="24" height="24" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" fill="none">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <circle cx="5" cy="5" r="1" fill="currentColor"/>
                        <circle cx="12" cy="5" r="1" fill="currentColor"/>
                        <circle cx="19" cy="5" r="1" fill="currentColor"/>
                        <circle cx="5" cy="12" r="1" fill="currentColor"/>
                        <circle cx="12" cy="12" r="1" fill="currentColor"/>
                        <circle cx="19" cy="12" r="1" fill="currentColor"/>
                        <circle cx="5" cy="19" r="1" fill="currentColor"/>
                        <circle cx="12" cy="19" r="1" fill="currentColor"/>
                        <circle cx="19" cy="19" r="1" fill="currentColor"/>
                    </svg>
                    <h3 class="card-title fw-bold text-dark mb-0 fs-3">ADD LOCATION</h3>
                </div>
                <div>
                    <a href="{{ route('locations.index') }}" class="btn btn-outline-secondary btn-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-arrow-left me-1" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <path d="M5 12l14 0"/>
                            <path d="M5 12l6 6"/>
                            <path d="M5 12l6 -6"/>
                        </svg>
                        Back to All Locations
                    </a>
                </div>
            </div>

            <div class="card-body p-4">
                <form id="addLocationForm" action="{{ route('locations.store') }}" method="POST" novalidate>
                    @csrf

                    <!-- 1. Select Area -->
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark" for="area_id">
                            Select Area <span class="text-danger">*</span>
                        </label>
                        <select name="area_id" id="area_id" class="form-select @error('area_id') is-invalid @enderror">
                            <option value="">Select Area</option>
                            @foreach($areas as $area)
                                <option value="{{ $area->id }}" {{ old('area_id') == $area->id ? 'selected' : '' }}>
                                    {{ $area->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="area_id_error">
                            @error('area_id') {{ $message }} @enderror
                        </div>
                    </div>

                    <!-- 2. Location Name -->
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark" for="location_name">
                            Location Name <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               name="location_name"
                               id="location_name"
                               class="form-control @error('location_name') is-invalid @enderror"
                               placeholder="Location Name *"
                               value="{{ old('location_name') }}">
                        <div class="invalid-feedback" id="location_name_error">
                            @error('location_name') {{ $message }} @enderror
                        </div>
                    </div>

                    <!-- 3. Location Type -->
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark" for="location_type">
                            Location Type <span class="text-danger">*</span>
                        </label>
                        <select name="location_type" id="location_type" class="form-select @error('location_type') is-invalid @enderror">
                            <option value="">Select Location Type</option>
                            @foreach($locationTypes as $type)
                                <option value="{{ $type }}" {{ old('location_type') == $type ? 'selected' : '' }}>
                                    {{ $type }}
                                </option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="location_type_error">
                            @error('location_type') {{ $message }} @enderror
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="mt-4">
                        <button type="submit" id="btnSubmit" class="btn btn-success px-4 me-2">
                            Add
                        </button>
                        <button type="reset" id="btnReset" class="btn btn-info px-4 text-white">
                            Reset
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    </div>
</div>

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('addLocationForm');
    const areaSelect = document.getElementById('area_id');
    const locationNameInput = document.getElementById('location_name');
    const locationTypeSelect = document.getElementById('location_type');

    function showError(input, errorElementId, message) {
        input.classList.add('is-invalid');
        const errorElem = document.getElementById(errorElementId);
        if (errorElem) {
            errorElem.innerText = message;
            errorElem.style.display = 'block';
        }
    }

    function clearError(input, errorElementId) {
        input.classList.remove('is-invalid');
        const errorElem = document.getElementById(errorElementId);
        if (errorElem) {
            errorElem.innerText = '';
            errorElem.style.display = 'none';
        }
    }

    // Real-time clearance on change / input
    areaSelect.addEventListener('change', function () {
        if (this.value.trim() !== '') {
            clearError(this, 'area_id_error');
        } else {
            showError(this, 'area_id_error', 'Please select an Area.');
        }
    });

    // Location Name Live Validation & Restriction
    locationNameInput.addEventListener('input', function () {
        const raw = this.value;
        const clean = raw.replace(/[^a-zA-Z\s]/g, '');

        if (raw !== clean) {
            showError(this, 'location_name_error', 'Numbers and special characters are not allowed in names!');
        } else if (!clean.trim()) {
            showError(this, 'location_name_error', 'Location name cannot be empty.');
        } else {
            clearError(this, 'location_name_error');
        }
        this.value = clean;
    });

    locationTypeSelect.addEventListener('change', function () {
        if (this.value.trim() !== '') {
            clearError(this, 'location_type_error');
        } else {
            showError(this, 'location_type_error', 'Please select a Location Type.');
        }
    });

    // Reset button clears all errors
    const btnReset = document.getElementById('btnReset');
    if (btnReset) {
        btnReset.addEventListener('click', function () {
            clearError(areaSelect, 'area_id_error');
            clearError(locationNameInput, 'location_name_error');
            clearError(locationTypeSelect, 'location_type_error');
        });
    }

    // Form submit validation via JavaScript
    form.addEventListener('submit', function (e) {
        let isValid = true;
        let firstInvalid = null;

        // 1. Validate Area
        if (!areaSelect.value || areaSelect.value.trim() === '') {
            showError(areaSelect, 'area_id_error', 'Please select an Area.');
            isValid = false;
            if (!firstInvalid) firstInvalid = areaSelect;
        } else {
            clearError(areaSelect, 'area_id_error');
        }

        // 2. Validate Location Name
        if (!locationNameInput.value || locationNameInput.value.trim() === '') {
            showError(locationNameInput, 'location_name_error', 'Please enter Location Name.');
            isValid = false;
            if (!firstInvalid) firstInvalid = locationNameInput;
        } else if (locationNameInput.classList.contains('is-invalid')) {
            isValid = false;
            if (!firstInvalid) firstInvalid = locationNameInput;
        }

        // 3. Validate Location Type
        if (!locationTypeSelect.value || locationTypeSelect.value.trim() === '') {
            showError(locationTypeSelect, 'location_type_error', 'Please select a Location Type.');
            isValid = false;
            if (!firstInvalid) firstInvalid = locationTypeSelect;
        } else {
            clearError(locationTypeSelect, 'location_type_error');
        }

        if (!isValid) {
            e.preventDefault(); // Stop form submission
            if (firstInvalid) firstInvalid.focus();
        }
    });
});
</script>
@endpush
@endsection
