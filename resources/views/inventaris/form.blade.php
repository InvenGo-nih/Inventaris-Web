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
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" name="name" class="form-control" value="{{ $data->name ?? '' }}">
                    </div>
                    <div class="form-group">
                        <label for="specification">Spesifikasi</label>
                        <input type="text" name="specification" class="form-control" value="{{ $data->specification ?? '' }}">
                    </div>
                    <div class="form-group">
                        <label for="condition">Kondisi</label>
                        <input type="text" name="condition" class="form-control" value="{{ $data->condition ?? '' }}">
                    </div>
                    <div class="form-group">
                        <label for="status">Status</label>
                        <input type="text" name="status" class="form-control" value="{{ $data->status ?? '' }}">
                    </div>
                    <div class="form-group">
                        <label for="image">Gambar</label>
                        <input type="file" name="image" class="form-control" value="{{ $data->image ?? '' }}">
                    </div>
                    <button type="submit" class="btn btn-primary w-100 mt-3">{{ request()->route('id') ? 'Perbarui' : 'Tambah'  }}</button>
                </div>
            </div>
        </div>
    </div>
</form>

@endsection