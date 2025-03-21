@extends('layouts.app')

@section('title')
    Inventaris
@endsection

@section('content')
    <div class="container p-5 mt-3 mb-5 border text-center shadow bg-white" data-aos="zoom-in">
        <div>
            <p>Jumlah Inventaris :</p>
            <p>{{ $jumlah }}</p>
        </div>
    </div>
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-3">
        <!-- Tombol Create Inventaris -->
        <div class="d-flex mb-2 mb-md-0 ">
            @hasPermission('CREATE_INVENTARIS')
                <a href="{{ route('inventaris.form') }}" class="btn btn-primary" style="margin-right: 10px">Tambah
                    Inventaris</a>
            @endhasPermission
            @hasPermission('PDF_INVENTARIS')
                <a href="{{ route('inventaris.pdf') }}" class="btn btn-danger"><i class="fa-solid fa-download"></i></a>
            @endhasPermission
        </div>
        <!-- Form Search -->
        <form action="{{ route('inventaris.index') }}" method="get" class="d-flex">
            <input type="text" class="form-control me-2" placeholder="Cari" name="search">
            <button class="btn btn-success" type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
        </form>
    </div>
    @forelse ($data as $item)
        <a href="{{ route('inventaris.show', $item->id) }}" style="text-decoration: none;">
            <div class="card shadow-sm mb-3">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <!-- Gambar -->
                        <div class="me-3">
                            <img src="https://vtgompvryxqxirylucui.supabase.co/storage/v1/object/public/invengo/upload/{{ $item->image }}"
                                alt="{{ $item->name }}" class="rounded" width="60" height="60">
                        </div>
                        <!-- Detail Laptop -->
                        <div>
                            <h6 class="mb-0 fw-bold">{{ $item->name }}</h6>
                            <p class="mb-1 text-muted small">{{ $item->specification }}</p>
                            <span class="badge rounded-pill {{ $item->condition == 'Normal' ? 'bg-success' : 'bg-danger' }}">
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
    @empty
        <p class="text-center">Tidak ada data</p>
    @endforelse
    <div class="d-flex justify-content-center">
        {{ $data->links('pagination::bootstrap-5') }}
    </div>

@endsection
