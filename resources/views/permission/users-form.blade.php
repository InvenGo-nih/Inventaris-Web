@extends('layouts.app')

@section('title')
    {{ request()->route('id') ? 'Edit' : 'Tambah' }} Pengguna
@endsection

@section('content')

@php
    $url = '';
    if (request()->route('id')) {
        $url = route('users.update', ['id' => $data->id]);
    } else {
        $url = route('users.store');
    }
@endphp

<form method="POST" action="{{ $url }}">
    @csrf
    @if (request()->route('id'))
        @method('PUT')
    @endif

    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label for="name" class="form-label">Nama</label>
                <input type="text" name="name" class="form-control" value="{{ $data->name ?? '' }}" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="{{ $data->email ?? '' }}" required>
            </div>
        </div>

        <div class="col-md-6">
            <div class="mb-3">
                <label for="role_id" class="form-label">Jabatan</label>
                 <select name="role_id" class="form-control">
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" name="password" class="form-control">
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-center gap-3 mt-3">
        <button type="button" onclick="history.back()" class="btn btn-secondary px-4">Kembali</button>
        <button type="submit" class="btn btn-primary px-4">
            {{ request()->route('id') ? 'Perbarui' : 'Tambah'  }}
        </button>
    </div>
</form>

@endsection
