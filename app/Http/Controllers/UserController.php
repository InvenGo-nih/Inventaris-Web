<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('role')->get();
        return view('permission.users', compact('users'));
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
        ]);

        if ($validate->fails()) {
            return redirect()->back()->withErrors($validate)->withInput();
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

        return redirect()->route('users.index')->with('success', 'Data pengguna diperbarui');
    }

    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'role_id' => 'required',
            'password' => 'required|min:8'
        ]);

        if ($validate->fails()) {
            return redirect()->back()->withErrors($validate)->withInput();
        }

        $data = new User();
        $data->name = $request->name;
        $data->email = $request->email;
        $data->role_id = $request->role_id;
        $data->password = bcrypt($request->password);
        $data->save();

        

        return redirect()->route('users.index')->with('success', 'Data berhasil disimpan');
    }

    public function destroy($id)
    {
        // Mencari pengguna berdasarkan ID
        $user = User::find($id);

        // Memastikan pengguna ditemukan
        if (!$user) {
            return redirect()->back()->with('error', 'siswa tidak ditemukan');
        }

        // Menghapus pengguna
        $user->delete();

        // Mengembalikan respons sukses
        return redirect()->route('users.index')->with('success', 'Data berhasil dihapus');
    }
}
