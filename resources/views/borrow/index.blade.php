@extends('layouts.app')

@section('title', 'Peminjaman')

@section('content')

<div class="container">
    <!-- Flash Message -->
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-3">
       
        <a href="{{ route('borrow.form') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah
        </a>
    </div>

    <!-- Membuat tabel bisa discroll di layar kecil -->
    <div class="table-responsive">
        <table class="table table-bordered text-center">
            <thead class="table-primary">
                <tr>
                    <th>Inventaris</th>
                    <th>Peminjam</th>
                    <th>Tanggal Pinjam</th>
                    <th>Tanggal Kembali</th>
                    <th>Status</th>
                    <th>Foto Peminjam</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $borrow)
                <tr>
                    <td class="text-wrap">{{ $borrow->inventaris->name }}</td>
                    <td class="text-wrap">{{ $borrow->user->name }}</td>
                    <td>{{ $borrow->date_borrow }}</td>
                    <td>{{ $borrow->date_back }}</td>
                    <td>
                        @if ($borrow->status == 'Dikembalikan')
                            <span class="badge bg-success">Dikembalikan</span>
                        @else
                            <span class="badge bg-danger">Dipinjam</span>
                        @endif
                    </td>
                    <td><img class="img-fluid" src="{{ asset('storage/' . $borrow->img_borrow) }}" width="100" alt=""></td>
                    <td>
                        <a href="{{ route('borrow.form', $borrow->id) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('borrow.delete', $borrow->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
