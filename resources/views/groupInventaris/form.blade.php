@extends('layouts.app')

@section('title')
    {{ request()->route('id') ? 'Edit' : 'Tambah' }} Inventaris
@endsection

@section('content')
    @php
        $url = '';
        if (request()->route('id')) {
            $url = route('inventaris.updateGroup', ['id' => $data->id]);
        } else {
            $url = route('inventaris.storeGroup');
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
            </div>
        </div>

        <div class="d-flex justify-content-center gap-3 mt-3">
            <button type="button" onclick="history.back()" class="btn btn-secondary px-4">Kembali</button>
            <button type="submit" class="btn btn-primary px-4">
                {{ request()->route('id') ? 'Perbarui' : 'Tambah' }}
            </button>
        </div>
    </form>
@endsection
