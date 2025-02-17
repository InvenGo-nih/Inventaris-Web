@extends('layouts.app')

@section('title')
   {{ $data->name }}
@endsection

@section('content')
    <div class="container text-center">
        

        <div class="d-flex justify-content-center">
            <img src="{{ asset('storage/'.$data->image) }}" alt="{{ $data->name }}" class="rounded mb-3" width="250" height="180">
        </div>

        <div class="card mx-auto" style="max-width: 400px;">
            <div class="card-body  text-center">
                <p><strong>Nama:</strong> {{ $data->name }}</p>
                <p><strong>Spesifikasi:</strong> {{ $data->specification }}</p>
                <p><strong>Kondisi:</strong> {{ $data->condition }}</p>
                <p><strong>Status:</strong> {{ $data->status }}</p>
            </div>
        </div>

        <div class="d-flex justify-content-center gap-2 mt-3">
            <a href="{{ route('inventaris.form', ['id' => $data->id]) }}" class="btn btn-primary">Edit Inventaris</a>
            <form action="{{ route('inventaris.delete', $data->id) }}" method="post">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">Hapus Inventaris</button>
            </form>
        </div>
    </div>
@endsection
