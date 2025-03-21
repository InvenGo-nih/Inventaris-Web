@extends('layouts.app')

@section('title')
    Kelola User & Jabatan
@endsection

@section('content')
    <a href="{{ route('users.form') }}" class="btn btn-primary mb-3">Tambah Pengguna</a>
    <div class="table-responsive">
        <table class="table table-bordered text-center">
            <thead class="table-primary">
                <tr>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Jabatan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td class="text-nowrap">{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ ucfirst($user->role->name) }}</td>
                        <td>
                            <div class="d-flex justify-content-center gap-2">
                                <a href="{{ route('users.form', $user->id) }}" class="btn btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('users.destroy', $user->id) }}" method="post"
                                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengguna ini?');">
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
                        <td colspan="4" class="text-center">Tidak ada data</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
