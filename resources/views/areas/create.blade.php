@extends('tablar::page')

@section('title', 'RFID TAGES | Add Area')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title text-uppercase">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-plus me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M12 5l0 14"/>
                        <path d="M5 12l14 0"/>
                    </svg>
                    Add Area
                </h2>
                <div class="text-secondary mt-1">
                    Create a new tracking area zone.
                </div>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <a href="{{ route('areas.index') }}" class="btn btn-outline-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-arrow-left me-1" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M5 12l14 0"/>
                        <path d="M5 12l6 6"/>
                        <path d="M5 12l6 -6"/>
                    </svg>
                    Back to All Areas
                </a>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible" role="alert">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
            </div>
        @endif

        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <form id="createAreaForm" action="{{ route('areas.store') }}" method="POST" novalidate>
                    @csrf

                    <div class="mb-3">
                        <label class="form-label required" for="name">Area Name</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" placeholder="e.g. Mayapuri, Warehouse A, Zone 1">
                        <div class="invalid-feedback" id="nameError">
                            @error('name') {{ $message }} @else Please enter a valid Area Name. @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="description">Description (Optional)</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" placeholder="Enter brief details about this area...">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-footer mt-4">
                        <button type="submit" class="btn btn-primary px-4">
                            Save Area
                        </button>
                        <a href="{{ route('areas.index') }}" class="btn btn-light ms-2">Cancel</a>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

@push('js')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const form = document.getElementById('createAreaForm');
        const name = document.getElementById('name');

        // Helper to get or create invalid feedback element
        function getOrCreateError(inputElement, defaultMsg) {
            let feedback = inputElement.parentNode.querySelector('.invalid-feedback');
            if (!feedback) {
                feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                feedback.innerText = defaultMsg;
                inputElement.parentNode.appendChild(feedback);
            }
            return feedback;
        }

        const nameError = getOrCreateError(name, 'Please enter a valid area name.');
        const existingAreas = @json($existingAreas ?? []);

        // Area Name Live Validation & Restriction
        name.addEventListener('input', function() {
            const raw = name.value;
            const clean = raw.replace(/[^a-zA-Z\s]/g, '');

            if (raw !== clean) {
                nameError.innerText = 'Numbers and special characters are not allowed in names!';
                name.classList.add('is-invalid');
            } else if (!clean.trim()) {
                nameError.innerText = 'Area name cannot be empty.';
                name.classList.add('is-invalid');
            } else if (existingAreas.includes(clean.trim().toLowerCase())) {
                nameError.innerText = 'Area name already exists!';
                name.classList.add('is-invalid');
            } else {
                name.classList.remove('is-invalid');
            }
            name.value = clean;
        });

        // Form Submission Validation
        form.addEventListener('submit', function(event) {
            let isValid = true;
            const clean = name.value.trim();

            if (!clean) {
                nameError.innerText = 'Area name cannot be empty.';
                name.classList.add('is-invalid');
                isValid = false;
            } else if (existingAreas.includes(clean.toLowerCase())) {
                nameError.innerText = 'Area name already exists!';
                name.classList.add('is-invalid');
                isValid = false;
            }

            if (!isValid || name.classList.contains('is-invalid')) {
                event.preventDefault();
                name.focus();
            }
        });
    });
</script>
@endpush
@endsection

