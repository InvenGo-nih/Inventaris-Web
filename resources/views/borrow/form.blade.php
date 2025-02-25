@extends('layouts.app')

@section('title')
    {{ $data->id ? 'Edit Peminjaman' : 'Tambah Peminjaman' }}
@endsection

@section('content')
<div class="container">

    <!-- Flash Message -->
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ $data->id ? route('borrow.update', $data->id) : route('borrow.store') }}" 
        method="POST" enctype="multipart/form-data"> {{-- Tambahkan enctype agar bisa upload gambar --}}
        
        @csrf
        @if ($data->id)
            @method('PUT')
        @endif

        <div class="row">
            <div class="col-md-6">

                <div class="mb-3">
                    <label for="user_id" class="form-label">Nama Peminjam</label>
                    <select name="user_id" id="user_id" class="form-control" required>
                        <option value="">Pilih Peminjam</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" {{ $data->user_id == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label for="inventaris_id" class="form-label">Barang</label>
                    <select name="inventaris_id" id="inventaris_id" class="form-control" required>
                        <option value="">Pilih Barang</option>
                        @foreach ($inventaris as $item)
                            <option value="{{ $item->id }}" {{ $data->inventaris_id == $item->id ? 'selected' : '' }}>
                                {{ $item->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label for="date_borrow" class="form-label">Tanggal Pinjam</label>
                    <input type="date" name="date_borrow" id="date_borrow" class="form-control" 
                        value="{{ old('date_borrow', $data->date_borrow) }}" required>
                </div>
            </div>

            <div class="col-md-6">

                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-control">
                        <option value="Dikembalikan" {{ $data->status == 'Dikembalikan' ? 'selected' : '' }}>Dikembalikan</option>
                        <option value="Dipinjam" {{ $data->status == 'Dipinjam' ? 'selected' : '' }}>Dipinjam</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="date_back" class="form-label">Tanggal Kembali</label>
                    <input type="date" name="date_back" id="date_back" class="form-control" 
                        value="{{ old('date_back', $data->date_back) }}" required>
                </div>

                <div class="mb-3">
                    <label for="img_borrow" class="form-label">Gambar</label>
                    <input type="file" name="img_borrow" class="form-control"> {{-- Hapus required agar tidak wajib saat edit --}}
                    
                   
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-center gap-3 mt-3">
            <button type="submit" class="btn btn-primary px-4">{{ $data->id ? 'Update' : 'Simpan' }}</button>
            <a href="{{ route('borrow.index') }}" class="btn btn-primary px-4">Batal</a>
        </div>
    </form>
</div>
@endsection
