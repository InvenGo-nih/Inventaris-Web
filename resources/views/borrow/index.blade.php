@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Daftar Peminjaman</h2>

    <!-- Flash Message -->
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('borrow.form') }}" class="btn btn-primary mb-3">Tambah Peminjaman</a>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Peminjam</th>
                <th>Nama Barang</th>
                <th>Tanggal Pinjam</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $key => $borrow)
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $borrow->user->name }}</td>
                <td>{{ $borrow->inventaris->name }}</td>
                <td>{{ $borrow->date_borrow }}</td>
                <td>{{ $borrow->status }}</td>
                <td>
                    <a href="{{ route('borrow.form', $borrow->id) }}" class="btn btn-warning btn-sm">Edit</a>
                    <form action="{{ route('borrow.delete', $borrow->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus?')">Hapus</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
