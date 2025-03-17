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
                    <label for="borrow_by" class="form-label">Nama Peminjam</label>
                    <input type="text" name="borrow_by" class="form-control" value="{{ $data->borrow_by ?? '' }}" placeholder="Masukkan Nama Peminjam">
                    {{-- <select name="user_id" id="user_id" class="form-control">
                        <option value="">Pilih Peminjam</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" {{ $data->user_id == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select> --}}
                </div>

                <div class="mb-3">
                    <label for="inventaris_id" class="form-label">Barang</label>
                    <select name="inventaris_id" id="inventaris_id" class="form-control">
                        <option value="">Pilih Barang</option>
                        @foreach ($inventaris as $item)
                            <option value="{{ $item->id }}" {{ $data->inventaris_id == $item->id ? 'selected' : '' }}>
                                {{ $item->name }} - {{ $item->specification }} - {{ $item->location }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label for="date_borrow" class="form-label">Tanggal Pinjam</label>
                    <input type="date" name="date_borrow" id="date_borrow" class="form-control" 
                        value="{{ old('date_borrow', $data->date_borrow) }}">
                </div>
            </div>

            <div class="col-md-6">

                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-control" onchange="toggleDateBack()">
                        <option value="" selected>Pilih Status</option>
                        <option value="Dikembalikan" {{ $data->status == 'Dikembalikan' ? 'selected' : '' }}>Dikembalikan</option>
                        <option value="Dipinjam" {{ $data->status == 'Dipinjam' ? 'selected' : '' }}>Dipinjam</option>
                    </select>
                </div>

                <div class="mb-3" id="dateBackContainer" style="{{ $data->status == 'Dipinjam' || $data->status == '' ? 'display:none;' : '' }}">
                    <label for="date_back" class="form-label">Tanggal Kembali</label>
                    <input type="date" name="date_back" id="date_back" class="form-control" 
                        value="{{ old('date_back', $data->date_back) }}" {{ $data->status == 'Dipinjam' ? 'value=null' : '' }}>
                </div>

                <div class="mb-3">
                    <label for="img_borrow" class="form-label">Gambar</label>
                    <input type="file" name="img_borrow" class="form-control"> {{-- Hapus agar tidak wajib saat edit --}}
                    
                   
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-center gap-3 mt-3">
            <button type="button" onclick="history.back()" class="btn btn-secondary px-4">Batal</button>
            <button type="submit" class="btn btn-primary px-4">{{ $data->id ? 'Perbarui' : 'Simpan' }}</button>
        </div>
    </form>
</div>

<script>
    function toggleDateBack() {
        const statusSelect = document.getElementById('status');
        const dateBackContainer = document.getElementById('dateBackContainer');
        if (statusSelect.value === 'Dipinjam' || statusSelect.value === '') {
            dateBackContainer.style.display = 'none';
            document.getElementById('date_back').value = null; // Set value to null
        } else {
            dateBackContainer.style.display = 'block';
        }
    }
</script>
@endsection
