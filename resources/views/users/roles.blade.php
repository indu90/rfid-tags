@extends('tablar::page')

@section('title', 'Roles & Permissions')

@section('content')
<!-- Page header -->
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">User Management</div>
                <h2 class="page-title">Roles &amp; Permissions</h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addPermissionModal">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M12 5l0 14" />
                            <path d="M5 12l14 0" />
                        </svg>
                        Add Permission
                    </button>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRoleModal">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M12 5l0 14" />
                            <path d="M5 12l14 0" />
                        </svg>
                        Add New Role
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Page body -->
<div class="page-body">
    <div class="container-xl">
        @if (session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            <div>{{ session('success') }}</div>
            <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
        </div>
        @endif

        @if ($errors->any())
        <div class="alert alert-danger alert-dismissible" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
            <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
        </div>
        @endif

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">System Roles</h3>
            </div>
            <div class="table-responsive">
                <table class="table card-table table-vcenter text-nowrap datatable">
                    <thead>
                        <tr>
                            <th class="w-1">#</th>
                            <th>Role Name</th>
                            <th>Users</th>
                            <th>Assigned Permissions</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($roles as $index => $role)
                        <tr>
                            <td><span class="text-secondary">{{ $index + 1 }}</span></td>
                            <td class="fw-semibold">{{ ucfirst($role->name) }}</td>
                            <td>
                                <span class="badge bg-blue-lt">
                                    {{ $role->users_count ?? $role->users->count() }} {{ Str::plural('User', $role->users_count ?? $role->users->count()) }}
                                </span>
                            </td>
                            <td class="text-wrap" style="max-width: 460px;">
                                @forelse ($role->permissions as $permission)
                                <span class="badge bg-azure-lt me-1 mb-1">{{ $permission->name }}</span>
                                @empty
                                <span class="text-secondary fst-italic">No permissions assigned</span>
                                @endforelse
                            </td>
                            <td class="text-end">
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-primary btn-edit-role"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editRoleModal"
                                    data-id="{{ $role->id }}"
                                    data-name="{{ $role->name }}"
                                    data-update-url="{{ route('roles.update', $role->id) }}"
                                    data-permissions='@json($role->permissions->pluck("id"))'>
                                    Edit
                                </button>

                                <form action="{{ route('roles.destroy', $role->id) }}" method="POST" class="d-inline delete-role-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-secondary py-4">No roles found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add New Role Modal -->
<div class="modal modal-blur fade" id="addRoleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <form action="{{ route('roles.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Add New Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label required">Role Name</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. Supervisor, Operator" required>
                </div>
                <div class="mb-2 d-flex justify-content-between align-items-center">
                    <label class="form-label mb-0">Assign Permissions</label>
                    <label class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="checkbox" id="selectAllAdd">
                        <span class="form-check-label">Select All</span>
                    </label>
                </div>
                <div class="row g-2 border rounded p-3 bg-light">
                    @foreach ($permissions as $permission)
                    <div class="col-md-4 col-sm-6">
                        <label class="form-check">
                            <input class="form-check-input add-perm-checkbox" type="checkbox" name="permissions[]" value="{{ $permission->id }}">
                            <span class="form-check-label">{{ $permission->name }}</span>
                        </label>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary ms-auto">Save Role</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Role Modal -->
<div class="modal modal-blur fade" id="editRoleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <form id="editRoleForm" action="" method="POST" class="modal-content">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title">Edit Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label required">Role Name</label>
                    <input type="text" name="name" id="editRoleName" class="form-control" required>
                </div>
                <div class="mb-2 d-flex justify-content-between align-items-center">
                    <label class="form-label mb-0">Assign Permissions</label>
                    <label class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="checkbox" id="selectAllEdit">
                        <span class="form-check-label">Select All</span>
                    </label>
                </div>
                <div class="row g-2 border rounded p-3 bg-light">
                    @foreach ($permissions as $permission)
                    <div class="col-md-4 col-sm-6">
                        <label class="form-check">
                            <input class="form-check-input edit-perm-checkbox" type="checkbox" name="permissions[]" value="{{ $permission->id }}">
                            <span class="form-check-label">{{ $permission->name }}</span>
                        </label>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary ms-auto">Update Role</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Permission Modal -->
<div class="modal modal-blur fade" id="addPermissionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form action="{{ route('permissions.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Add New Permission</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label required">Permission Name</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. rfid.scan, users.manage" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary ms-auto">Create Permission</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('js')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Select All toggle (Add Modal)
        const selectAllAdd = document.getElementById('selectAllAdd');
        const addCheckboxes = document.querySelectorAll('.add-perm-checkbox');
        if (selectAllAdd) {
            selectAllAdd.addEventListener('change', () => {
                addCheckboxes.forEach(cb => cb.checked = selectAllAdd.checked);
            });
        }

        // Select All toggle (Edit Modal)
        const selectAllEdit = document.getElementById('selectAllEdit');
        const editCheckboxes = document.querySelectorAll('.edit-perm-checkbox');
        if (selectAllEdit) {
            selectAllEdit.addEventListener('change', () => {
                editCheckboxes.forEach(cb => cb.checked = selectAllEdit.checked);
            });
        }

        // Populate Edit Role Modal via event delegation
        document.addEventListener('click', (e) => {
            const editBtn = e.target.closest('.btn-edit-role');
            if (!editBtn) return;

            const form = document.getElementById('editRoleForm');
            const nameInput = document.getElementById('editRoleName');
            const assignedIds = JSON.parse(editBtn.dataset.permissions || '[]').map(Number);

            form.action = editBtn.dataset.updateUrl;
            nameInput.value = editBtn.dataset.name;

            editCheckboxes.forEach(cb => {
                cb.checked = assignedIds.includes(Number(cb.value));
            });

            if (selectAllEdit) {
                selectAllEdit.checked = editCheckboxes.length > 0 && Array.from(editCheckboxes).every(cb => cb.checked);
            }
        });

        // Confirm before deleting a role
        document.addEventListener('submit', (e) => {
            if (e.target.classList.contains('delete-role-form')) {
                if (!confirm('Are you sure you want to delete this role?')) {
                    e.preventDefault();
                }
            }
        });
    });
</script>
@endsection