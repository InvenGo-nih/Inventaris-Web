@extends('layouts.app')

@section('title')
    Lokasi Inventaris
@endsection

@section('content')
    <div class="container">
        <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#exampleModal">Tambah
            Lokasi</button>
        <div class="table-responsive">
            <table class="table table-bordered text-center">
                <thead class="table-primary">
                    <tr>
                        <th>No</th>
                        <th>Lokasi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>

                    @foreach ($data as $item)
                        <tr>
                            <td>{{ $loop->index + 1 }}</td>
                            <td>{{ ucfirst($item->location) }}</td>
                            <td>
                                <div class="d-flex justify-content-center gap-2">
                                    <button type="button" data-bs-toggle="modal"
                                        data-bs-target="#exampleModal{{ $item->id }}" class="btn btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('inventaris.location.delete', $item->id) }}" method="post"
                                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus jabatan ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
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
                                        <input type="text" name="location" value="{{ $item->location }}" class="form-control">
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-danger"
                                            data-bs-dismiss="modal">Tutup</button>
                                        <button type="submit" class="btn btn-primary">Perbarui</button>
                                    </div>
                                </div>
                            </form>
                            </div>
                        </div>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pindahkan modal di sini agar tidak terulang untuk setiap item -->
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
                            <input type="text" name="location"
                                class="form-control">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Tutup</button>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>
@endsection
