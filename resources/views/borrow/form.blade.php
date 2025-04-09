@extends('layouts.app')

@section('title')
    {{ request()->route('id') ? 'Edit' : 'Tambah' }} Peminjaman
@endsection

@section('content')
    @php
        $url = '';
        if (request()->route('id')) {
            $url = route('borrow.update', ['id' => $data->id]);
        } else {
            $url = route('borrow.store');
        }
    @endphp

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

    <form method="POST" action="{{ $url }}" enctype="multipart/form-data">
        @csrf
        @if (request()->route('id'))
            @method('PUT')
        @endif

        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="borrow_by" class="form-label">Nama Peminjam</label>
                    <input type="text" name="borrow_by" class="form-control" value="{{$data->borrow_by }}" placeholder="Nama peminjam">
                </div>
                <div class="mb-3">
                    <label for="inventaris_id" class="form-label">Barang</label>
                    <select name="inventaris_id" class="form-control" id="inventaris_id" onchange="updateAvailableQuantity()">
                        <option value="" selected disabled>Pilih Barang</option>
                        @foreach ($inventaris as $item)
                            <option value="{{ $item->id }}" 
                                data-quantity="{{ $item->quantity }}"
                                {{ isset($data) && $data->inventaris_id == $item->id ? 'selected' : '' }}>
                                {{ $item->name }} | {{ $item->type }} | {{ $item->location }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="quantity" class="form-label">Jumlah Pinjam</label>
                    <input type="number" name="quantity" class="form-control" id="quantity" 
                        value="{{ $data->quantity ?? 1 }}" min="1" 
                        placeholder="Masukkan Jumlah Pinjam"
                        {{ isset($data) && $data->status == 'Dikembalikan' ? 'readonly' : '' }}>
                    <small class="text-muted">Stok tersedia: <span id="available_quantity">0</span></small>
                </div>
                <div class="mb-3">
                    <label for="date_borrow" class="form-label">Tanggal Pinjam</label>
                    <input type="date" name="date_borrow" class="form-control" value="{{ $data->date_borrow ?? '' }}">
                </div>
            </div>

            <div class="col-md-6">
                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" class="form-control" id="status" onchange="toggleDateBack()">
                        <option value="" selected disabled>Pilih Status</option>
                        <option value="Dipinjam" {{ isset($data) && $data->status == 'Dipinjam' ? 'selected' : '' }}>Dipinjam</option>
                        <option value="Dikembalikan" {{ isset($data) && $data->status == 'Dikembalikan' ? 'selected' : '' }}>Dikembalikan</option>
                    </select>
                </div>
                <div class="mb-3" id="dateBackContainer" style="display: {{ isset($data) && $data->status == 'Dikembalikan' ? 'block' : 'none' }};">
                    <label for="date_back" class="form-label">Tanggal Kembali</label>
                    <input type="date" name="date_back" class="form-control" value="{{ $data->date_back ?? '' }}">
                </div>
                <div class="mb-3">
                    <label for="img_borrow" class="form-label">Gambar</label>
                    <input type="file" name="img_borrow" class="form-control" {{ request()->route('id') ? '' : 'required' }}>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-center gap-3 mt-3">
            <button type="button" onclick="history.back()" class="btn btn-secondary px-4">Kembali</button>
            <button type="submit" class="btn btn-primary px-4">
                {{ request()->route('id') ? 'Perbarui' : 'Tambah' }}
            </button>
        </div>
    </form>

    <script>
        function updateAvailableQuantity() {
            const select = document.getElementById('inventaris_id');
            const option = select.options[select.selectedIndex];
            const quantity = option.getAttribute('data-quantity');
            document.getElementById('available_quantity').textContent = quantity;
        }

        function toggleDateBack() {
            const status = document.getElementById('status').value;
            const dateBackContainer = document.getElementById('dateBackContainer');
            const quantityInput = document.getElementById('quantity');
            
            if (status === 'Dikembalikan') {
                dateBackContainer.style.display = 'block';
                // Jika dalam mode edit, set quantity ke nilai awal
                if (document.querySelector('input[name="_method"]')) {
                    const originalQuantity = "{{ $data->quantity ?? 1 }}";
                    quantityInput.value = originalQuantity;
                    quantityInput.readOnly = true;
                }
            } else {
                dateBackContainer.style.display = 'none';
                quantityInput.readOnly = false;
            }
        }

        // Panggil fungsi saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            updateAvailableQuantity();
            toggleDateBack();
        });
    </script>
@endsection
