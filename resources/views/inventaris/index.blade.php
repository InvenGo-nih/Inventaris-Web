@extends('layouts.app')

@section('title')
    {{-- Daftar {{ $title }} --}}
    Daftar Barang
@endsection

@section('content')
    <div class="row">
        <!-- Barang Normal -->
        <div class="col-lg-4 col-md-4 col-8 mb-3 " data-aos="fade-right">
            <div class="card shadow h-100 py-2">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Jumlah Barang
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="inventaris-count">{{ $jumlah }}</div>
                        </div>
                        <div>
                            <i class="fas fa-fw fa-solid fa-cubes fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Barang Rusak -->
        <div class="col-lg-4 col-md-4 col-8 mb-3" data-aos="fade-left">
            <div class="card shadow h-100 py-2">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Jumlah Barang Normal
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="borrow-count">{{ $jumlahNormal }}</div>
                        </div>
                        <div>
                            <i class="fas fa-fw fa-solid fa-hand-holding fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Barang Rusak -->
        <div class="col-lg-4 col-md-4 col-8 mb-3" data-aos="fade-left">
            <div class="card shadow h-100 py-2">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Jumlah Barang Rusak
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="borrow-count">{{ $jumlahRusak }}</div>
                        </div>
                        <div>
                            <i class="fas fa-fw fa-solid fa-hand-holding fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-3">
        <!-- Tombol Create Inventaris -->
        <div class="d-flex mb-2 mb-md-0 mt-2 gap-2">
            <a href="{{ route('inventaris.index') }}" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Kembali</a>
            @php
                $id = request()->route('id');
            @endphp
            @hasPermission('CREATE_INVENTARIS')
                <a href="{{ route('inventaris.form', ['group_inventaris_id' => $id]) }}" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Tambah
                    Barang</a>
            @endhasPermission
            @hasPermission('PDF_INVENTARIS')
                <a href="{{ route('inventaris.pdf', ['group_inventaris_id' => $id]) }}" class="btn btn-danger"><i class="fa-solid fa-download"></i></a>
            @endhasPermission
        </div>
        <!-- Form Search -->
        <form action="{{ route('inventaris.index') }}" method="get" class="d-flex gap-2">
            <input type="text" class="form-control" placeholder="Cari" name="search">
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
                            <img src="https://ztbemybejhgwbftpryqs.supabase.co/storage/v1/object/public/invengo2/upload/{{ $item->image }}"
                                alt="{{ $item->name }}" class="rounded" width="60" height="60">
                        </div>
                        <!-- Detail Laptop -->
                        <div>
                            <h6 class="mb-1 fw-bold">{{ $item->name }} | {{ $item->serial_number }}</h6>
                            <p class="mb-1 text-muted small">{{ $item->location }}</p>
                            <span class="badge rounded-pill {{ $item->condition == 'Normal' ? 'bg-label-success' : 'bg-label-danger' }}">
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
