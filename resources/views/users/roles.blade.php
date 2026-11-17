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
                            <option value="{{ $role->id }}" {{ old('role') == $role->id ? 'selected' : '' }}>
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
                    <div class="col-12 mb-4">
                        <label class="form-label text-secondary d-block">Status</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="statusToggle" name="status" value="1" checked style="width: 3rem; height: 1.5rem;">
                            <label class="form-check-label ms-2 mt-1" for="statusToggle">Active</label>
                        </div>
                    </div>
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

        form.addEventListener('submit', function(event) {
            let isValid = true;

            // 1. Name Validation
            const name = document.getElementById('name');
            if (!name.value.trim()) {
                name.classList.add('is-invalid');
                isValid = false;
            } else {
                name.classList.remove('is-invalid');
            }

            // 2. Role Validation
            const role = document.getElementById('role');
            if (!role.value) {
                role.classList.add('is-invalid');
                isValid = false;
            } else {
                role.classList.remove('is-invalid');
            }

            // 3. Email Validation
            const email = document.getElementById('email');
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email.value.trim())) {
                email.classList.add('is-invalid');
                isValid = false;
            } else {
                email.classList.remove('is-invalid');
            }

            // 4. Phone Validation (10 digits)
            const phone = document.getElementById('phone');
            const phoneRegex = /^\d{10}$/;
            if (!phoneRegex.test(phone.value.trim())) {
                phone.classList.add('is-invalid');
                isValid = false;
            } else {
                phone.classList.remove('is-invalid');
            }

            // 5. Password Validation (min 8 chars)
            const password = document.getElementById('password');
            if (password.value.length < 8) {
                password.classList.add('is-invalid');
                isValid = false;
            } else {
                password.classList.remove('is-invalid');
            }

            // 6. Confirm Password Match
            const confirmPassword = document.getElementById('password_confirmation');
            if (confirmPassword.value !== password.value || confirmPassword.value === '') {
                confirmPassword.classList.add('is-invalid');
                isValid = false;
            } else {
                confirmPassword.classList.remove('is-invalid');
            }

            // Prevent submission if validation fails
            if (!isValid) {
                event.preventDefault();
            }
        });
    });
</script>
@endpush
@endsection