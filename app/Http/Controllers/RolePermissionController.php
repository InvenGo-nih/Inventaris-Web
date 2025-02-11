<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionController extends Controller
{
    // Halaman Kelola Role & Permission
    public function manageRoles()
    {
        $roles = Role::with('permissions')->get();
        $permissions = Permission::all()->groupBy('permission_group');
        return view('permission.role', compact('roles', 'permissions'));
    }

    public function form(Request $request, $id = null) 
    {
        // $role = $id ? Role::with('permissions')->findOrFail($request->id) : new Role();
        $role = Role::with('permissions')->findOrFail($id);
        $permissions = Permission::all()->groupBy('permission_group');
        return view('permission.role-form', compact('role', 'permissions'));
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
        ]);

        $data = new Role();
        $data->name = $request->name;
        $data->guard_name = "web";
        $data->save();

        return redirect()->route('roles.index')->with('success', 'Role berhasil disimpan');
    }

    // Update Permission dari Role
    public function updateRolePermissions(Request $request, Role $role)
    {
        // Validasi input name
        $request->validate([
            'name' => 'required',
        ]);
        
        // Perbarui nama role
        $role->name = $request->name;
        $role->save(); // Tambahkan tanda kurung agar perubahan disimpan

        $role->syncPermissions($request->permissions);

        return redirect()->route('roles.index')->with('success', 'Permission role diperbarui');
    }

    public function destroy(Role $role)
    {
        $role->delete();
        return redirect()->route('roles.index')->with('success', 'Role berhasil dihapus');
    }
}
