<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;
use App\Models\Permission;

class UserManagementController extends Controller
{
    /**
     * Display a listing of the users (User List).
     */
    public function index()
    {
        $users = User::latest()->paginate(10);
        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user (Add New User).
     */
    public function create()
    {
        $roles = Role::all();
        return view('users.create', compact('roles'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'role'     => 'required|exists:roles,name',
            'phone'    => 'required|string|max:10',
            'password' => 'required|string|min:8|confirmed', // 'confirmed' automatically checks matching password_confirmation
            'status'   => 'nullable|boolean',
        ]);

        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'role'     => $request->role,
            'phone'    => $request->phone,
            'password' => Hash::make($request->password),
            'status'   => $request->has('status') ? $request->status : 1,
        ]);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    /**
     * Display the roles and permissions management page.
     */
    public function rolesPermissions()
    {
        // Fetch roles with their associated permissions and count users per role
        $roles = Role::with('permissions')->withCount('users')->get();
        $permissions = Permission::all(); // To list checkboxes in your modal if needed

        return view('users.roles', compact('roles', 'permissions'));
    }

    public function storeRole(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255|unique:roles,name',
            'permissions' => 'nullable|array',
        ]);

        // Create the role dynamically
        $role = Role::create([
            'name' => $request->name,
        ]);

        // Attach permissions if selected
        if ($request->has('permissions')) {
            $role->permissions()->attach($request->permissions);
        }

        return redirect()->route('roles.index')->with('success', 'Role created successfully.');
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        // Fetch roles if you want a dynamic dropdown of roles on the edit page
        $roles = Role::all();

        return view('users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users,email,' . $user->id, // Ignores current user's email
            'role'     => 'required|string|max:50',
            'phone'    => 'required|string|max:20',
            'password' => 'nullable|string|min:8|confirmed', // Optional password update
            'status'   => 'nullable|boolean',
        ]);

        $user->name  = $request->name;
        $user->email = $request->email;
        $user->role  = $request->role;
        $user->phone = $request->phone;
        $user->status = $request->has('status') ? 1 : 0;

        // Update password only if a new one is provided
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }

    /**
     * Show the form for editing the specified role.
     */
    public function editRole(Role $role)
    {
        $role->load('permissions'); // Load currently assigned permissions
        $permissions = Permission::all(); // Fetch all available permissions for checkboxes

        return view('users.edit-role', compact('role', 'permissions'));
    }

    /**
     * Update the specified role in storage.
     */
    public function updateRole(Request $request, Role $role)
    {
        $request->validate([
            'name'        => 'required|string|max:255|unique:roles,name,' . $role->id,
            'permissions' => 'nullable|array',
        ]);

        // Update role name
        $role->update([
            'name' => $request->name,
        ]);

        // Sync permissions (updates pivot table automatically)
        if ($request->has('permissions')) {
            $role->permissions()->sync($request->permissions);
        } else {
            $role->permissions()->sync([]); // Remove all if none checked
        }

        return redirect()->route('roles.index')->with('success', 'Role updated successfully.');
    }

    /**
     * Remove the specified role from storage.
     */
    public function destroyRole(Role $role)
    {
        // Detach permissions before deleting to maintain relational integrity
        $role->permissions()->detach();
        $role->delete();

        return redirect()->route('roles.index')->with('success', 'Role deleted successfully.');
    }

    public function storePermission(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name',
        ]);

        \App\Models\Permission::create([
            'name' => $request->name,
        ]);

        return redirect()->back()->with('success', 'Permission created successfully.');
    }
}
