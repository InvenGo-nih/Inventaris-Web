<?php

namespace App\Http\Controllers;

use App\DataTables\RolesDataTable;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRolePermissionsRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionController extends Controller
{
    public function manageRoles(RolesDataTable $dataTable)
    {
        return $dataTable->render('permission.role');
    }

    public function form($id = null)
    {
        $role = $id ? Role::with('permissions')->findOrFail($id) : new Role;
        $permissions = Permission::all()->groupBy('permission_group');

        return view('permission.role-form', compact('role', 'permissions'));
    }

    public function store(StoreRoleRequest $request)
    {
        $role = Role::create([
            'name' => $request->validated('name'),
            'guard_name' => config('auth.defaults.guard', 'web'),
        ]);

        $role->syncPermissions($request->validated('permissions') ?? []);

        return redirect()->route('roles.index')->with('success', 'Jabatan berhasil disimpan');
    }

    public function updateRolePermissions(UpdateRolePermissionsRequest $request, Role $role)
    {
        $validated = $request->validated();

        DB::transaction(function () use ($role, $validated) {
            $role->name = $validated['name'];
            $role->save();

            $role->syncPermissions($validated['permissions'] ?? []);
        });

        return redirect()->route('roles.index')->with('success', 'Hak akses berhasil diperbarui');
    }

    public function destroy(Role $role)
    {
        if (User::where('role_id', $role->id)->exists()) {
            return redirect()->back()->with('error', 'Jabatan tidak dapat dihapus karena masih digunakan pengguna.');
        }

        $role->delete();

        return redirect()->route('roles.index')->with('success', 'Jabatan berhasil dihapus');
    }
}
