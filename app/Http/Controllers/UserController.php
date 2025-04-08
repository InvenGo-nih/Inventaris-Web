<?php

namespace App\Http\Controllers;

use App\DataTables\UsersDataTable;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(UsersDataTable $dataTable)
    {
        $users = User::with('role')->get();

        return $dataTable->render('permission.users', compact('users'));
        // return view('permission.users', compact('users'));
    }

    public function form(Request $request, $id = null)
    {
        $data = $id ? User::findorFail($request->id) : new User();
        $roles = Role::all();
        return view('permission.users-form', compact('data', 'roles'));
    }

    // Update Role User
    public function update(Request $request, $id)
    {
        // Validasi input
        $validate = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'role_id' => 'required',
            'password' => 'nullable|min:8' // Password opsional saat memperbarui
        ], [
            'name.required' => 'Nama harus diisi.',
            'email.required' => 'Email harus diisi.',
            'role_id.required' => 'Jabatan harus dipilih.',
            'password.min' => 'Password minimal 8 karakter.'
        ]);

        if ($validate->fails()) {
            return redirect()->back()->withErrors($validate)->withInput()->with('error', $validate->errors()->all());
        }

        // Mencari pengguna berdasarkan ID
        $user = User::findOrFail($id);

        // Memperbarui data pengguna
        $user->name = $request->name;
        $user->email = $request->email;
        $user->role_id = $request->role_id;

        // Hanya memperbarui password jika diisi
        if ($request->filled('password')) {
            $user->password = bcrypt($request->password);
        }

        $user->save();

        return redirect()->route('users.index')->with('success', 'Data pengguna berhasil diperbarui');
    }

    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'role_id' => 'required',
            'password' => 'required|min:8'
        ], [
            'name.required' => 'Nama harus diisi.',
            'email.required' => 'Email harus diisi.',
            'role_id.required' => 'Jabatan harus dipilih.',
            'password.required' => 'Password harus diisi.',
            'password.min' => 'Password minimal 8 karakter.'
        ]);

        if ($validate->fails()) {
            return redirect()->back()->withErrors($validate)->withInput()->with('error', $validate->errors()->all());
        }

        $data = new User();
        $data->name = $request->name;
        $data->email = $request->email;
        $data->role_id = $request->role_id;
        $data->password = bcrypt($request->password);
        $data->save();

        return redirect()->route('users.index')->with('success', 'Data pengguna berhasil disimpan');
    }

    public function destroy($id)
    {
        // Mencari pengguna berdasarkan ID
        $user = User::find($id);

        // Memastikan pengguna ditemukan
        if (!$user) {
            return redirect()->back()->with('error', 'Pengguna tidak ditemukan');
        }

        // Menghapus pengguna
        $user->delete();

        // Mengembalikan respons sukses
        return redirect()->route('users.index')->with('success', 'Data pengguna berhasil dihapus');
    }
}
