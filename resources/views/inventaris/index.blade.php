@extends('layouts.app')

@section('title')
    Inventaris
@endsection

@section('content')
<div class="container">

    <div class="container p-5 my-5 border text-center shadow">
        <div >
            <p>Jumlah Inventaris :</p>
            <p>{{ $jumlah }}</p>
        </div>
    </div>

         <div class="d-flex justify-content-between align-items-center mb-3">
             
             <!-- Tombol Create Inventaris -->
             <div class="d-flex">
                 <a href="{{ route('inventaris.form') }}" class="btn btn-primary" style="margin-right: 10px">Tambah Inventaris</a>
    
    <a href="{{ route('inventaris.pdf') }}" class="btn btn-danger"><i class="fa-solid fa-download"></i></a>
</div>
             <!-- Form Search -->
             <form action="{{ route('inventaris.index') }}" method="get" class="d-flex">
                 <input type="text" class="form-control me-2" placeholder="Search" name="search">
                 <button class="btn btn-outline-secondary" type="submit">Search</button>
             </form>
    </div>
    
    {{-- old --}}
    {{-- <div class="table-responsive m-5 " >
        <table class="table table-dark table-striped">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>Spesifikasi</th>
                    <th>Kondisi</th>
                    <th>QR Code</th>
                    <th>Gambar</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->specification }}</td>
                        <td>{{ $item->condition }}</td>
                        <td>{!! QrCode::size(100)->generate($item->qr_link) !!}</td>
                        <td>
                            <img src="{{ asset('storage/'.$item->image) }}" alt="{{ $item->name }}" width="100" height="100">
                        </td>
                        <td>
                            <a href="{{ route('inventaris.form', ['id' => $item->id]) }}" class="btn btn-primary btn-sm">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">Tidak ada data</td>
                    </tr>
                @endforelse
            </tbody>
        </table> --}}

        {{-- new --}}
    @foreach ($data as $item)
    <a href="{{ route('inventaris.show', $item->id) }}">
        <div class="card shadow-sm mb-3">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <!-- Gambar -->
                    <div class="me-3">
                        <img src="{{ asset('storage/'.$item->image) }}" alt="{{ $item->name }}" class="rounded" width="60" height="60">
                    </div>
                    <!-- Detail Laptop -->
                    <div>
                        <h6 class="mb-0 fw-bold">{{ $item->name }}</h6>
                        <p class="mb-1 text-muted small">{{ $item->specification }}</p>
                        <span class="badge {{ $item->condition == 'Berada di Lab' ? 'bg-success' : 'bg-warning text-dark' }}">
                            {{ $item->condition }}
                        </span>
                    </div>
                </div>
                <!-- QR Code -->
                <div>
                    {!! QrCode::size(50)->generate($item->qr_link) !!}
                </div>
            </div>
        </div>
        </a>
    @endforeach
    
</div>

@endsection
