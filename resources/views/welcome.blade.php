@extends('layouts.app')

@section('content')
<div class="container">

    <div class="container p-5 my-5 border text-center shadow">
        <div >
            <p>Jumlah Inventaris :</p>
            <p>{{ $jumlah }}</p>
        </div>
    </div>
    <div class="container">

         <div class="d-flex justify-content-between align-items-center mb-3">
             
             <!-- Tombol Create Inventaris -->
             <a href="{{ route('inventaris.form') }}" class="btn btn-primary">Create Inventaris</a>
             <!-- Form Search -->
             <form action="{{ route('home') }}" method="get" class="d-flex">
                 <input type="text" class="form-control me-2" placeholder="Search" name="search">
                 <button class="btn btn-outline-secondary" type="submit">Search</button>
             </form>
    </div>
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
          <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">DataTables</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr class="text-center">
                                            <th>No</th>
                                            <th>Nama</th>
                                            <th>Spesifikasi</th>
                                            <th>Kondisi</th>
                                            <th>QR Code</th>
                                            <th>Gambar</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    {{-- <tfoot>
                                        <tr >
                                            <th>No</th>
                                            <th>Nama</th>
                                            <th>Spesifikasi</th>
                                            <th>Kondisi</th>
                                            <th>QR Code</th>
                                            <th>Gambar</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </tfoot> --}}
                                    <tbody class="text-center">
                                          @forelse ($data as $index => $item)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $item->name }}</td>
                                            <td>{{ $item->specification }}</td>
                                            <td>{{ $item->condition }}</td>
                                            <td>{!! QrCode::size(100)->generate($item->qr_link) !!}</td>
                                            <td><img src="{{ asset('storage/'.$item->image) }}" alt="{{ $item->name }}" width="100" height="100"></td>
                                            <td><button class="btn btn-success "><a class="text-light" style="text-decoration: none" href="{{ route('inventaris.form', ['id' => $item->id]) }}">Edit</a></button> <span> </span><button class="btn btn-danger">Hapus</button></td>
                                        </tr>
                                       @empty
                                    <tr>
                                        <td colspan="7" class="text-center">Tidak ada data</td>
                                    </tr>
                                @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="d-flex justify-content-center">
                            {{ $data->links('vendor.pagination.bootstrap-5') }}
                        </div>
                    </div>
        {{-- Pagination --}}
    </div>
    
</div>

@endsection
