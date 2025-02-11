@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Kelola Role & Permission</h2>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createRoleModal">Tambah
            Jabatan</button>
        <table class="table">
            <thead>
                <tr>
                    <th>Role</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>

                @foreach ($roles as $role)
                    <tr>
                        <td>{{ ucfirst($role->name) }}</td>
                        <td>
                            <a href="{{ route('roles.form', $role->id) }}" class="btn btn-warning">Edit</a>
                            <form action="{{ route('roles.destroy', $role->id) }}" method="post" onsubmit="return confirm('Apakah Anda yakin ingin menghapus jabatan ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Modal --}}
    <div class="modal fade" id="createRoleModal" tabindex="-1" aria-labelledby="createRoleModal" aria-hidden="true">
        <form action="{{ route('roles.store') }}" method="post">
            @csrf
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="createRoleModal">Tambah Jabatan</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <label for="name" class="form-label">Nama</label>
                        <input type="text" name="name" class="form-control" placeholder="Masukkan Nama Jabatan">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Tambah</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
