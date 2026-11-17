@extends('tablar::page')

@section('title', 'Users Listing - RFID Tags App')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="text-dark fw-bold mb-1">Users List</h3>
            <p class="text-secondary mb-0">Manage your system users, roles, and status</p>
        </div>
        <a href="{{ route('users.create') }}" class="btn btn-warning text-white fw-bold px-4">
            + Add New User
        </a>
    </div>

    <!-- Success Message Alert -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Users Table Card -->
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-secondary text-uppercase fs-7">
                        <tr>
                            <th class="py-3 ps-4">S.No</th>
                            <th class="py-3">Name / Email</th>
                            <th class="py-3">Role</th>
                            <th class="py-3">Phone</th>
                            <th class="py-3">Status</th>
                            <th class="py-3 text-end pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $index => $user)
                        <tr>
                            <td class="ps-4 fw-bold">{{ $users->firstItem() + $index ?? $index + 1 }}</td>
                            <td>
                                <div class="fw-bold">{{ $user->name }}</div>
                                <div class="text-secondary small">{{ $user->email }}</div>
                            </td>
                            <td>
                                <span class="badge bg-secondary text-white px-2 py-1 text-capitalize">{{ $user->role ?? 'User' }}</span>
                            </td>
                            <td>{{ $user->phone ?? 'N/A' }}</td>
                            <td>
                                @if(isset($user->status) && $user->status == 1)
                                <span class="badge bg-success px-3 py-1 fw-bold">Active</span>
                                @else
                                <span class="badge bg-danger px-3 py-1 fw-bold">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-outline-primary me-1">Edit</a>
                                <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-secondary">
                                No users found. Click <strong>+ Add New User</strong> to create one.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination Links -->
        @if(isset($users) && method_exists($users, 'links'))
        <div class="card-footer bg-white py-3">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>
@endsection