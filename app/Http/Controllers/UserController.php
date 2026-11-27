<?php

namespace App\Http\Controllers;

use App\DataTables\UsersDataTable;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(UsersDataTable $dataTable)
    {
        return $dataTable->render('permission.users');
    }

    public function form($id = null)
    {
        $data = $id ? User::findOrFail($id) : new User;
        $roles = Role::all();

        return view('permission.users-form', compact('data', 'roles'));
    }

    public function update(UpdateUserRequest $request, $id)
    {
        $validated = $request->validated();
        $role = Role::findOrFail($validated['role_id']);

        DB::transaction(function () use ($id, $validated, $role) {
            $user = User::findOrFail($id);

            $user->name = $validated['name'];
            $user->email = $validated['email'];
            $user->role_id = $role->id;

            if (! empty($validated['password'])) {
                $user->password = $validated['password'];
            }

            $user->save();
            $user->syncRoles([$role]);
        });

        return redirect()->route('users.index')->with('success', 'Data pengguna berhasil diperbarui');
    }

    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();
        $role = Role::findOrFail($validated['role_id']);

        DB::transaction(function () use ($validated, $role) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'role_id' => $role->id,
                'password' => $validated['password'],
            ]);

            $user->assignRole($role);
        });

        return redirect()->route('users.index')->with('success', 'Data pengguna berhasil disimpan');
    }

    public function destroy($id)
    {
        if ((int) Auth::id() === (int) $id) {
            return redirect()->back()->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        $user = User::find($id);

        if (! $user) {
            return redirect()->back()->with('error', 'Pengguna tidak ditemukan');
        }

        $user->syncRoles([]);
        $user->delete();

        return redirect()->route('users.index')->with('success', 'Data pengguna berhasil dihapus');
    }
}
