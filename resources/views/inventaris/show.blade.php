@extends('layouts.app')

@section('title')
   {{ $data->name }}
@endsection

@section('content')
    <div class="text-center">
        

        <div class="d-flex justify-content-center">
            <img class="img-fluid mb-3 rounded" src="https://vtgompvryxqxirylucui.supabase.co/storage/v1/object/public/invengo/upload/{{ $data->image }}" alt="{{ $data->name }}" class="rounded mb-3" width="350" height="230">
        </div>

        <div class="card mx-auto" style="max-width: 90%;">
            <div class="card-body text-start">
                <p class="mb-2 border-bottom"><strong>Nama:</strong> <span class="float-end">{{ $data->name }}</span></p>
                <p class="mb-2 border-bottom"><strong>Lokasi:</strong> <span class="float-end">{{ $data->location }}</span></p>
                <p class="mb-2 border-bottom"><strong>Spesifikasi:</strong> <span class="float-end">{{ $data->specification }}</span></p>
                <p class="mb-2 border-bottom"><strong>Kondisi:</strong> <span class="float-end">{{ $data->condition }}</span></p>
                <p class="mb-2 border-bottom"><strong>Jumlah:</strong> <span class="float-end">{{ $data->quantity }}</span></p>
                <p class="mb-2 border-bottom"><strong>Deskripsi Rusak:</strong> <span class="float-end">{{ $data->broken_description ? $data->broken_description : '-' }}</span></p>
                <p class="mb-2 border-bottom"><strong>Status:</strong> <span class="float-end">{{ $data->status }}</span></p>
                <p class="mb-2 border-bottom"><strong>No.Serial:</strong> <span class="float-end">{{ $data->serial_number }}</span></p>
            </div>
        </div>

        <div class="d-flex justify-content-center gap-2 my-3">
            <button onclick="window.history.back()" class="btn btn-secondary white-space text-nowrap"><i class="fa-solid fa-arrow-left"></i> Kembali</button>
            @hasPermission('EDIT_INVENTARIS')
            <a href="{{ route('inventaris.form', ['id' => $data->id]) }}" class="btn btn-warning white-space text-nowrap"><i class="fa-solid fa-pen-to-square"></i> Edit</a>
            @endhasPermission
            @hasPermission('DELETE_INVENTARIS')
            <form action="{{ route('inventaris.delete', $data->id) }}" method="post" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger white-space text-nowrap"><i class="fa-solid fa-trash"></i> Hapus</button>
            </form>
            @endhasPermission
        </div>
    </div>
@endsection
