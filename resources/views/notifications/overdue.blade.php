@extends('layouts.app')

@section('title')
    NOTIFIKASI PEMINJAMAN TERLAMBAT
@endsection

@section('content')
    <div class="card">
        <div class="card-header bg-danger text-white">
            <h5 class="mb-0">
                <i class="bx bx-error-circle me-2"></i>
                Daftar Peminjaman Terlambat
            </h5>
        </div>
        <div class="card-body">
            @if($overdueBorrows->isEmpty())
                <div class="alert alert-success">
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
                                    $maxReturnDate = \Carbon\Carbon::parse($borrow->max_return_date)->startOfDay();
                                    $today = \Carbon\Carbon::now()->startOfDay();
                                    $daysLate = (int) $today->floatDiffInDays($maxReturnDate);
                                @endphp
                                <tr class="table-danger">
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $borrow->borrow_by }}</td>
                                    <td>{{ $borrow->inventaris->name }}</td>
                                    <td>{{ $borrow->quantity }}</td>
                                    <td>{{ \Carbon\Carbon::parse($borrow->date_borrow)->format('d/m/Y') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($borrow->max_return_date)->format('d/m/Y') }}</td>
                                    <td>
                                        {{ abs($daysLate) }} hari
                                    </td>
                                    <td>
                                        <a href="{{ route('borrow.form', $borrow->id) }}" class="btn btn-sm btn-warning">
                                            <i class="bx bx-edit me-1"></i>Update
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection 