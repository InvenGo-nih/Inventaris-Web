@extends('layouts.app')
@section('content')
    {{-- <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="dropdown-item">
            Logout
        </button>
    </form> --}}

    <p>Jumlah Inventaris :</p>
    <p>{{ $jumlah }}</p>
    <form action="{{ route('home') }}" method="get" class="container">
        <div class="input-group mb-3">
            <input type="text" class="form-control" placeholder="Search" name="search">
            <button class="btn btn-outline-secondary" type="submit">Search</button>
        </div>
    </form>
    <a href="{{ route('inventaris.form') }}" class="btn btn-primary">Create Inventaris</a>

    @forelse ($data as $item)
        <h1>{{ $item->name }}</h1>
        <p>{{ $item->specification }}</p>
        <p>{{ $item->condition }}</p>
        {!! QrCode::size(200)->generate($item->qr_link) !!}
        <img src="{{ asset('storage/'.$item->image) }}" alt="{{ $item->name }}" width="200" height="200">
        <a href="{{ route('inventaris.form', ['id' => $item->id]) }}" class="btn btn-primary">Edit Inventaris</a>
        {{-- <form method="POST" action="{{ route('inventaris.destroy', ['id' => $item->id]) }}" class="d-inline"> --}}
    @empty
        <p>Tidak ada data</p>
    @endforelse

    {{-- Pagination --}}
    {{ $data->links('vendor.pagination.bootstrap-5') }}

@endsection