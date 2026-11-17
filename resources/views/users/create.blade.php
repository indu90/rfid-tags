@extends('tablar::page')

@section('title', 'Create User - RFID Tags App')

@section('content')
<div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="card shadow-sm w-100" style="max-width: 800px; background-color: #ffffff; border-radius: 8px;">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                <h4 class="mb-0 text-dark fw-bold">Add User</h4>
                <a href="{{ route('users.index') }}" class="btn-close" aria-label="Close"></a>
            </div>

            <form id="createUserForm" action="{{ route('users.store') }}" method="POST" novalidate>
                @csrf
                <div class="row">
                    <!-- User / Name -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-secondary">User <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" class="form-control" placeholder="Enter name" value="{{ old('name') }}">
                        <div class="invalid-feedback" id="nameError">Please enter a valid name.</div>
                    </div>

                    <!-- Role -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-secondary">Role <span class="text-danger">*</span></label>
                        <select id="role" name="role" class="form-select @error('role') is-invalid @enderror">
                            <option value="">Select Role</option>
                            @foreach($roles as $role)
                            <option value="{{ $role->name }}" {{ old('role') == $role->name ? 'selected' : '' }}>
                                {{ $role->name }}
                            </option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="roleError">Please select a role.</div>
                        @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <!-- Email -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-secondary">Email <span class="text-danger">*</span></label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="Enter email address" value="{{ old('email') }}">
                        <div class="invalid-feedback" id="emailError">Please enter a valid email address.</div>
                    </div>

                    <!-- Phone -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-secondary">Phone <span class="text-danger">*</span></label>
                        <input type="text" id="phone" name="phone" class="form-control" placeholder="Enter 10-digit phone number" value="{{ old('phone') }}" maxlength="10">
                        <div class="invalid-feedback" id="phoneError">Please enter a valid 10-digit phone number.</div>
                    </div>

                    <!-- Password -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-secondary">Password <span class="text-danger">*</span></label>
                        <input type="password" id="password" name="password" class="form-control" placeholder="••••••••">
                        <div class="invalid-feedback" id="passwordError">Password must be at least 8 characters long.</div>
                    </div>

                    <!-- Confirm Password -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-secondary">Confirm Password <span class="text-danger">*</span></label>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" placeholder="Confirm password">
                        <div class="invalid-feedback" id="confirmPasswordError">Passwords do not match.</div>
                    </div>

                    <!-- Status Toggle -->
                    <!--<div class="col-12 mb-4">
                        <label class="form-label text-secondary d-block">Status</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="statusToggle" name="status" value="1" checked style="width: 3rem; height: 1.5rem;">
                            <label class="form-check-label ms-2 mt-1" for="statusToggle">Active</label>
                        </div>
                    </div>-->
                </div>

                <!-- Action Buttons -->
                <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                    <a href="{{ route('users.index') }}" class="btn btn-secondary px-4">Cancel</a>
                    <button type="submit" class="btn btn-warning text-white px-4">Add User</button>
                </div>
            </form>
        </div>
    </div>
</div>
@push('js')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const form = document.getElementById('createUserForm');

        // Elements
        const name = document.getElementById('name');
        const email = document.getElementById('email');
        const role = document.getElementById('role');
        const phone = document.getElementById('phone');
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('password_confirmation');

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

        const nameError = getOrCreateError(name, 'Please enter a valid name.');
        const emailError = getOrCreateError(email, 'Please enter a valid email address.');
        const roleError = getOrCreateError(role, 'Please select a role.');
        const phoneError = getOrCreateError(phone, 'Please enter a valid 10-digit phone number.');
        const passwordError = getOrCreateError(password, 'Password must be at least 8 characters long.');
        const confirmError = getOrCreateError(confirmPassword, 'Passwords do not match.');

        // 1. Name Live Validation & Restriction (Letters & spaces only)
        name.addEventListener('input', function() {
            const raw = name.value;
            const clean = raw.replace(/[^a-zA-Z\s]/g, '');

            if (raw !== clean) {
                nameError.innerText = 'Numbers and special characters are not allowed in names!';
                name.classList.add('is-invalid');
            } else if (!clean.trim()) {
                nameError.innerText = 'Name cannot be empty.';
                name.classList.add('is-invalid');
            } else {
                name.classList.remove('is-invalid');
            }
            name.value = clean;
        });

        // 2. Email Live Validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        email.addEventListener('input', function() {
            if (!emailRegex.test(email.value.trim())) {
                emailError.innerText = 'Please enter a valid email format (e.g., user@wavelinx.com).';
                email.classList.add('is-invalid');
            } else {
                email.classList.remove('is-invalid');
            }
        });

        // 3. Role Live Validation
        role.addEventListener('change', function() {
            if (!role.value) {
                role.classList.add('is-invalid');
            } else {
                role.classList.remove('is-invalid');
            }
        });

        // 4. Phone Live Validation & Restriction (Digits only, max 10)
        phone.addEventListener('input', function() {
            const raw = phone.value;
            const clean = raw.replace(/\D/g, '');

            if (raw !== clean) {
                phoneError.innerText = 'Alphabets and special characters are not allowed!';
                phone.classList.add('is-invalid');
            } else if (clean.length !== 10 && clean.length > 0) {
                phoneError.innerText = 'Phone number must be exactly 10 digits.';
                phone.classList.add('is-invalid');
            } else {
                phone.classList.remove('is-invalid');
            }
            phone.value = clean;
        });

        // 5. Password Live Validation (Required on Create)
        password.addEventListener('input', function() {
            if (password.value.length < 8) {
                passwordError.innerText = 'Password must be at least 8 characters long.';
                password.classList.add('is-invalid');
            } else {
                password.classList.remove('is-invalid');
            }

            if (confirmPassword.value && confirmPassword.value !== password.value) {
                confirmPassword.classList.add('is-invalid');
            } else {
                confirmPassword.classList.remove('is-invalid');
            }
        });

        // 6. Confirm Password Match Live Validation
        confirmPassword.addEventListener('input', function() {
            if (confirmPassword.value !== password.value || confirmPassword.value === '') {
                confirmPassword.classList.add('is-invalid');
            } else {
                confirmPassword.classList.remove('is-invalid');
            }
        });

        // Final Form Submission Check
        form.addEventListener('submit', function(event) {
            let isValid = true;

            if (!name.value.trim()) {
                name.classList.add('is-invalid');
                isValid = false;
            }
            if (!emailRegex.test(email.value.trim())) {
                email.classList.add('is-invalid');
                isValid = false;
            }
            if (!role.value) {
                role.classList.add('is-invalid');
                isValid = false;
            }
            if (phone.value.length !== 10) {
                phone.classList.add('is-invalid');
                isValid = false;
            }
            if (password.value.length < 8) {
                password.classList.add('is-invalid');
                isValid = false;
            }
            if (confirmPassword.value !== password.value || confirmPassword.value === '') {
                confirmPassword.classList.add('is-invalid');
                isValid = false;
            }

            if (!isValid) {
                event.preventDefault();
            }
        });
    });
</script>
@endpush
@endsection