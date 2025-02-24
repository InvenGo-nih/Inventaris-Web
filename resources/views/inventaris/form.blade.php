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
                <input type="text" name="name" class="form-control" value="{{ $data->name ?? '' }}">
            </div>
            <div class="mb-3">
                <label for="condition" class="form-label">Kondisi Inventaris</label>
               <select name="condition" class="form-control">
                            <option value="Rusak" {{ isset($data) && $data->condition == 'Rusak' ? 'selected' : '' }}>Rusak</option>
                            <option value="Normal" {{ isset($data) && $data->condition == 'Normal' ? 'selected' : '' }}>Normal</option>
                        </select>
            </div>
            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <input type="text" name="status" class="form-control" value="{{ $data->status ?? '' }}">
            </div>
        </div>

        <div class="col-md-6">
            <div class="mb-3">
                <label for="specification" class="form-label">Spesifikasi</label>
                <textarea name="specification" class="form-control" rows="3">{{ $data->specification ?? '' }}</textarea>
            </div>
            
            <div class="mb-3">
    <label for="location" class="form-label">Lokasi</label>
    <input type="text" name="location" class="form-control" value="{{ $data->location ?? '' }}">
</div>
            <div class="mb-3">
                <label for="image" class="form-label">Gambar</label>
                <input type="file" name="image" class="form-control" value="{{ $data->image ?? '' }}">
            </div>

        </div>
    </div>

    <div class="d-flex justify-content-center gap-3 mt-3">
        <a href="{{ route('inventaris.index') }}" class="btn btn-primary px-4">Kembali</a>
        <button type="submit" class="btn btn-primary px-4">
            {{ request()->route('id') ? 'Perbarui' : 'Tambah' }}
        </button>
    </div>
</form>

@endsection