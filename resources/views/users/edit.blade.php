@extends('tablar::page')

@section('title', 'Edit User - RFID Tags App')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="text-dark fw-bold mb-1">Edit User</h3>
            <p class="text-secondary mb-0">Update user details, role, and permissions</p>
        </div>
        <a href="{{ route('users.index') }}" class="btn btn-secondary px-4">
            &larr; Back to Users
        </a>
    </div>

    <!-- Edit Form Card -->
    <div class="card shadow-sm border-0 rounded-3 col-md-8 mx-auto">
        <div class="card-body p-4">
            <form id="editUserForm" action="{{ route('users.update', $user->id) }}" method="POST" novalidate>
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label text-secondary">Full Name <span class="text-danger">*</span></label>
                    <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}">
                    <div class="invalid-feedback">Please enter a valid name.</div>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label text-secondary">Email Address <span class="text-danger">*</span></label>
                    <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}">
                    <div class="invalid-feedback">Please enter a valid email address.</div>
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label text-secondary">Role <span class="text-danger">*</span></label>
                    <select id="role" name="role" class="form-select @error('role') is-invalid @enderror">
                        <option value="">Select Role</option>
                        @foreach($roles as $role)
                        <option value="{{ $role->name }}" {{ (old('role', $user->role) == $role->name) ? 'selected' : '' }}>
                            {{ $role->name }}
                        </option>
                        @endforeach
                    </select>
                    <div class="invalid-feedback">Please select a role.</div>
                    @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label text-secondary">Phone Number <span class="text-danger">*</span></label>
                    <input type="text" id="phone" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $user->phone) }}" maxlength="10">
                    <div class="invalid-feedback">Please enter a valid 10-digit phone number.</div>
                    @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-secondary">New Password <small class="text-muted">(Leave blank to keep current)</small></label>
                        <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror">
                        <div class="invalid-feedback">Password must be at least 8 characters long.</div>
                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-secondary">Confirm New Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control">
                        <div class="invalid-feedback">Passwords do not match.</div>
                    </div>
                </div>

                <div class="mb-4 form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="status" id="statusToggle" value="1" {{ old('status', $user->status) == 1 ? 'checked' : '' }}>
                    <label class="form-check-label text-secondary" for="statusToggle">Active User Status</label>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('users.index') }}" class="btn btn-light px-4">Cancel</a>
                    <button type="submit" class="btn btn-warning text-white px-4 fw-bold">Update User</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('js')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const form = document.getElementById('editUserForm');

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

        // 5. Password Live Validation (Optional on edit, but checked if typed)
        password.addEventListener('input', function() {
            if (password.value.length > 0 && password.value.length < 8) {
                password.classList.add('is-invalid');
            } else {
                password.classList.remove('is-invalid');
            }

            // Also re-check confirmation match live if typing
            if (confirmPassword.value && confirmPassword.value !== password.value) {
                confirmPassword.classList.add('is-invalid');
            } else {
                confirmPassword.classList.remove('is-invalid');
            }
        });

        // 6. Confirm Password Match Live Validation
        confirmPassword.addEventListener('input', function() {
            if (confirmPassword.value !== password.value) {
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

            if (password.value.trim() !== '') {
                if (password.value.length < 8) {
                    password.classList.add('is-invalid');
                    isValid = false;
                }
                if (confirmPassword.value !== password.value) {
                    confirmPassword.classList.add('is-invalid');
                    isValid = false;
                }
            }

            if (!isValid) {
                event.preventDefault();
            }
        });
    });
</script>
@endpush
@endsection