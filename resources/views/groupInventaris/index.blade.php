@extends('layouts.app')

@section('title')
    Daftar Barang
@endsection

@section('content')
    {{-- <div class="container p-5 mt-3 mb-5 border text-center shadow bg-white" data-aos="zoom-in">
        <div>
            <p>Jumlah Inventaris :</p>
            <p>{{ $jumlah }}</p>
        </div>
    </div> --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-3">
        <!-- Tombol Create Inventaris -->
        <div class="d-flex mb-2 mb-md-0 ">
            @hasPermission('CREATE_INVENTARIS')
                <a href="{{ route('inventaris.formGroup') }}" class="btn btn-primary" style="margin-right: 10px"><i class="fa-solid fa-plus"></i> Tambah
                    Barang Baru</a>
            @endhasPermission
        </div>
        <!-- Form Search -->
        <form action="{{ route('inventaris.index') }}" method="get" class="d-flex">
            <input type="text" class="form-control me-2" placeholder="Cari" name="search">
            <button class="btn btn-success" type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
        </form>
    </div>
    <div class="d-flex justify-content-between flex-wrap gap-3">
        @forelse ($data as $item)
            <div class="card shadow-sm mb-3" style="width: 200px; height: 300px;">
                <div class="card-body d-flex flex-column">
                    <!-- Dropdown Menu -->
                    <div class="dropdown position-absolute top-0 end-0 m-2">
                        <button class="btn btn-link text-dark p-2" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="width: 40px; height: 40px;">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu">
                            @hasPermission('EDIT_INVENTARIS')
                            <li><a class="dropdown-item" href="{{ route('inventaris.formGroup', $item->id) }}"><i class="fas fa-edit me-2"></i>Edit</a></li>
                            @endhasPermission
                            @hasPermission('DELETE_INVENTARIS')
                            <li><a class="dropdown-item text-danger" href="#" onclick="event.preventDefault(); document.getElementById('delete-form-{{ $item->id }}').submit();"><i class="fas fa-trash me-2"></i>Hapus</a></li>
                            <form id="delete-form-{{ $item->id }}" action="{{ route('inventaris.deleteGroup', $item->id) }}" method="POST" class="d-none">
                                @csrf
                                @method('DELETE')
                            </form>
                            @endhasPermission
                        </ul>
                    </div>
                    <!-- Gambar dan Detail -->
                    <a href="{{ route('inventaris.inventaris', $item->id) }}" class="text-decoration-none">
                        <div class="text-center mt-3 mb-3">
                            <img src="https://vtgompvryxqxirylucui.supabase.co/storage/v1/object/public/invengo/upload/{{ $item->image }}"
                                alt="{{ $item->name }}" class="rounded" style="width: 150px; height: 150px; object-fit: cover;">
                        </div>
                        @php
                            $jumlah = \App\Models\Inventaris::where('group_inventaris_id', $item->id)->count();
                            $broken = \App\Models\Inventaris::where('group_inventaris_id', $item->id)->where('condition', 'Rusak')->count();
                            $normal = \App\Models\Inventaris::where('group_inventaris_id', $item->id)->where('condition', 'Normal')->count();
                        @endphp
                        <!-- Detail Laptop -->
                        <div class="mt-3">
                            <h6 class="mb-2 fw-bold text-center text-dark">{{ $item->name }}</h6>
                            <p class="mb-1 text-muted small">{{ $item->type }}</p>
                            <p class="mb-1 text-muted small"><span class="badge bg-label-success">{{ $normal }} Normal</span> <span class="badge bg-label-danger">{{ $broken }} Rusak</span></p>
                        </div>
                    </a>
                </div>
            </div>
        @empty
            <p class="text-center">Tidak ada data</p>
        @endforelse
    @for ($i = 0; $i < 10; $i++)
        <div class="card invisible" style="width: 200px; height: 0;"></div>
    @endfor
    </div>
    <div class="d-flex justify-content-center">
        {{ $data->links('pagination::bootstrap-5') }}
    </div>
@endsection
