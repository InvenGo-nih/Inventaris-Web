@extends('layouts.app')

@section('title')
    Kelola Jabatan & Hak Akses
@endsection

@section('content')
    <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#createRoleModal">Tambah
        Jabatan</button>
    <div class="table-responsive">
        <table class="table table-bordered text-center">
            <thead class="table-primary">
                <tr>
                    <th>Jabatan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>

                @forelse ($roles as $role)
                    <tr>
                        <td>{{ ucfirst($role->name) }}</td>
                        <td>
                            <div class="d-flex justify-content-center gap-2">
                                <a href="{{ route('roles.form', $role->id) }}" class="btn btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('roles.destroy', $role->id) }}" method="post"
                                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus jabatan ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="text-center">Tidak ada data</td>
                    </tr>
                @endforelse
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
