@extends('layouts.app')

@section('title')
    Periksa {{ $data->name }}
@endsection

@section('content')

    <form method="POST" action="{{ route('cek.update', ['id' => $data->id]) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-md-6">
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
                {{-- <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <input type="text" name="status" class="form-control" value="{{ $data->status ?? '' }}"
                        placeholder="Masukkan Status">
                    <select name="status" class="form-control" id="status">
                        <option value="" selected disabled>Pilih Status</option>
                        <option value="Digunakan" {{ isset($data) && $data->status == 'Digunakan' ? 'selected' : '' }}>
                            Digunakan</option>
                        <option value="Tidak Digunakan" {{ isset($data) && $data->status == 'Tidak Digunakan' ? 'selected' : '' }}>
                            Tidak Digunakan</option>
                    </select>
                </div> --}}
                <div class="mb-3">
                    <label for="image" class="form-label">Gambar (opsional)</label>
                    <input type="file" name="image" class="form-control" accept="image/jpeg,image/png,image/webp">
                    <small class="text-muted">Kosongkan jika tidak ingin mengganti gambar.</small>
                </div>
            </div>

            <div class="col-md-6">
                {{-- <div class="mb-3">
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
                </div> --}}
                <div class="mb-3">
                    <label for="specification" class="form-label">Spesifikasi</label>
                    <textarea name="specification" class="form-control" style="height: 8em;" readonly>{{ $data->specification ?? '' }}</textarea>
                </div>

                <div class="mb-3" id="brokenDescriptionContainer"
                    style="display: {{ isset($data) && $data->condition == 'Rusak' ? 'block' : 'none' }};">
                    <label for="broken_description" class="form-label">Rincian Rusak</label>
                    <textarea class="form-control" name="broken_description" id="" style="height: 8.2em;"
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
