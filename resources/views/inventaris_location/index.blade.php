@extends('layouts.app')

@section('title')
    Lokasi Inventaris
@endsection

@section('content')
    @hasPermission('CREATE_LOCATION_INVENTARIS')
        <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#exampleModal">Tambah
            Lokasi</button>
    @endhasPermission
    <div class="table-responsive">
        <table class="table table-bordered text-center">
            <thead class="table-primary">
                <tr>
                    <th>No</th>
                    <th>Lokasi</th>
                    @hasPermission(['EDIT_LOCATION_INVENTARIS', 'DELETE_LOCATION_INVENTARIS'])
                        <th>Aksi</th>
                    @endhasPermission
                </tr>
            </thead>
            <tbody>

                @forelse ($data as $item)
                    <tr>
                        <td>{{ $loop->index + 1 }}</td>
                        <td>{{ ucfirst($item->location) }}</td>
                        @hasPermission(['EDIT_LOCATION_INVENTARIS', 'DELETE_LOCATION_INVENTARIS'])
                            <td>
                                <div class="d-flex justify-content-center gap-2">
                                    @hasPermission('EDIT_LOCATION_INVENTARIS')
                                        <button type="button" data-bs-toggle="modal"
                                            data-bs-target="#exampleModal{{ $item->id }}" class="btn btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    @endhasPermission
                                    @hasPermission('DELETE_LOCATION_INVENTARIS')
                                        <form action="{{ route('inventaris.location.delete', $item->id) }}" method="post"
                                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus jabatan ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endhasPermission
                                </div>
                            </td>
                        @endhasPermission
                    </tr>
                    @hasPermission('EDIT_LOCATION_INVENTARIS')
                        <div class="modal fade" id="exampleModal{{ $item->id }}" tabindex="-1"
                            aria-labelledby="exampleModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <form action="{{ route('inventaris.location.update', $item->id) }}" method="post">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h1 class="modal-title fs-5" id="exampleModalLabel">Edit Lokasi</h1>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <label for="location" class="form-label">Lokasi</label>
                                            <input type="text" name="location" value="{{ $item->location }}"
                                                class="form-control">
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Tutup</button>
                                            <button type="submit" class="btn btn-primary">Perbarui</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endhasPermission
                @empty
                    <tr>
                        <td colspan="3" class="text-center">Tidak ada data</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pindahkan modal di sini agar tidak terulang untuk setiap item -->
    @hasPermission('CREATE_LOCATION_INVENTARIS')
        <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <form action="{{ route('inventaris.location.store') }}" method="post">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="exampleModalLabel">Tambah Lokasi</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <label for="location" class="form-label">Lokasi</label>
                            <input type="text" name="location" class="form-control">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Tutup</button>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endhasPermission

@endsection
