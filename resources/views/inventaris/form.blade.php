@extends('layouts.app')

@section('title')
    {{ request()->route('id') ? 'Edit' : 'Tambah' }} Inventaris
@endsection

@section('content')
    @php
        $url = '';
        if (request()->route('id')) {
            $url = route('inventaris.update', ['id' => $data->id]);
        } else {
            $url = route('inventaris.store');
        }
    @endphp

    <form method="POST" action="{{ $url }}" enctype="multipart/form-data">
        @csrf
        @if (request()->route('id'))
            @method('PUT')
        @endif

        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="name" class="form-label">Nama</label>
                    <input type="text" name="name" class="form-control" value="{{ $data->name ?? '' }}"
                        placeholder="Masukkan Nama Inventaris">
                </div>
                <div class="mb-3">
                    <label for="condition" class="form-label">Kondisi Inventaris</label>
                    <select name="condition" class="form-control" id="condition" onchange="toggleBrokenDescription()">
                        <option value="" selected disabled>Kondisi Barang</option>
                        <option value="Rusak" {{ isset($data) && $data->condition == 'Rusak' ? 'selected' : '' }}>Rusak
                        </option>
                        <option value="Normal" {{ isset($data) && $data->condition == 'Normal' ? 'selected' : '' }}>Normal
                        </option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <input type="text" name="status" class="form-control" value="{{ $data->status ?? '' }}"
                        placeholder="Masukkan Status">
                </div>
                <div class="mb-3">
                    <label for="location" class="form-label">Lokasi</label>
                    {{-- <input type="text" name="location" class="form-control" value="{{ $data->location ?? '' }}"> --}}
                    <select name="location" id="location" class="form-control">
                        <option value="" selected disabled>Pilih Lokasi</option>
                        @foreach ($location as $item)
                            <option value="{{ $item->location }}"
                                {{ isset($data) && $data->location == $item->location ? 'selected' : '' }}>
                                {{ $item->location }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="quantity" class="form-label">Jumlah Barang</label>
                    <input type="number" name="quantity" class="form-control" value="{{ $data->quantity ?? 1 }}"
                        min="1" placeholder="Masukkan Jumlah Barang">
                </div>
                <div class="mb-3">
                    <label for="image" class="form-label">Gambar</label>
                    <input type="file" name="image" class="form-control" value="{{ $data->image ?? '' }}">
                </div>
            </div>

            <div class="col-md-6">
                <div class="mb-3">
                    @php
                        $type = [
                            'Teknologi',
                            'Otomotif',
                            'Bahan',
                        ]    
                    @endphp
                    <label for="specification" class="form-label">Type</label>
                    <select name="type" class="form-control" id="type">
                        <option value="" selected disabled>Pilih Type</option>
                        @foreach ($type as $item)
                            <option value="{{ $item }}" {{ isset($data) && $data->type == $item ? 'selected' : '' }}>
                                {{ $item }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="specification" class="form-label">Spesifikasi</label>
                    <textarea name="specification" class="form-control" style="height: 7.8em;" placeholder="Masukkan Spesifikasi">{{ $data->specification ?? '' }}</textarea>
                </div>

                <div class="mb-3" id="brokenDescriptionContainer"
                    style="display: {{ isset($data) && $data->condition == 'Rusak' ? 'block' : 'none' }};">
                    <label for="broken_description" class="form-label">Rincian Rusak</label>
                    <textarea class="form-control" name="broken_description" id="" style="height: 13em;"
                        placeholder="Masukkan Rincian Rusak">{{ $data->broken_description ?? '' }}</textarea>
                </div>
                <div id="hiddenDescription"
                    style="display: {{ isset($data) && $data->condition == 'Rusak' ? 'none' : 'block' }};">
                    <p class="text-muted">Rincian rusak tidak tersedia.</p>
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
        function toggleBrokenDescription() {
            var condition = document.getElementById('condition').value;
            var brokenDescriptionContainer = document.getElementById('brokenDescriptionContainer');
            var hiddenDescription = document.getElementById('hiddenDescription');
            if (condition === 'Rusak') {
                brokenDescriptionContainer.style.display = 'block';
                hiddenDescription.style.display = 'none';
            } else {
                brokenDescriptionContainer.style.display = 'none';
                hiddenDescription.style.display = 'block';
            }
        }
    </script>
@endsection
