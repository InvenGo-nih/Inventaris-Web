@extends('layouts.app')

@section('title')
    NOTIFIKASI
@endsection

@section('content')
    <div class="card">
        <div class="card-header bg-danger text-white">
            <h5 class="mb-0 text-white">
                <i class="bx bx-error-circle me-2"></i>
                Daftar Peminjaman Terlambat
            </h5>
        </div>
        <div class="card-body">
            @if($overdueBorrows->isEmpty())
                <div class="alert alert-success mt-2">
                    <i class="bx bx-check-circle me-2"></i>
                    Tidak ada peminjaman yang terlambat saat ini.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>NO</th>
                                <th>NAMA PEMINJAM</th>
                                <th>BARANG</th>
                                <th>JUMLAH</th>
                                <th>TANGGAL PINJAM</th>
                                <th>BATAS PENGEMBALIAN</th>
                                <th>KETERLAMBATAN</th>
                                <th>AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($overdueBorrows as $index => $borrow)
                                @php
                                    $maxReturnDate = $borrow->max_return_date->copy()->startOfDay();
                                    $today = now()->startOfDay();
                                    $daysLate = (int) $maxReturnDate->diffInDays($today, true);
                                @endphp
                                <tr class="table-danger">
                                    <td>{{ $overdueBorrows->firstItem() + $index }}</td>
                                    <td>{{ $borrow->borrow_by }}</td>
                                    <td>{{ $borrow->inventaris?->name ?? '-' }}</td>
                                    <td>{{ $borrow->quantity }}</td>
                                    <td>{{ $borrow->date_borrow?->format('d/m/Y') }}</td>
                                    <td>{{ $borrow->max_return_date?->format('d/m/Y') }}</td>
                                    <td>
                                        {{ $daysLate }} hari
                                    </td>
                                    <td>
                                        @hasPermission('EDIT_BORROW')
                                            <a href="{{ route('borrow.form', $borrow->id) }}" class="btn btn-sm btn-warning">
                                                <i class="bx bx-edit me-1"></i>Update
                                            </a>
                                        @endhasPermission
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-center mt-3">
                    {{ $overdueBorrows->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
@endsection
