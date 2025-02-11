@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Kelola User & Role</h2>
    <a href="{{ route('users.form') }}" class="btn btn-primary">Tambah Pengguna</a>
    <table class="table">
        <thead>
            <tr>
                <th>Nama</th>
                <th>Email</th>
                <th>Role</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ ucfirst($user->role->name) }}</td>
                    <td>
                        <a href="{{ route('users.form', $user->id) }}" class="btn btn-warning">Edit</a>
                        <form action="{{ route('users.destroy', $user->id) }}" method="post" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengguna ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                        {{-- <form action="{{ route('admin.users.updateRole', $user->id) }}" method="POST">
                            @csrf
                            <select name="role" class="form-control" required>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->name }}" {{ $user->hasRole($role->name) ? 'selected' : '' }}>
                                        {{ ucfirst($role->name) }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-primary btn-sm mt-2">Update Role</button>
                        </form> --}}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
