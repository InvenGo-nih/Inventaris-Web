@extends('layouts.app')

@section('title')
    Beranda
@endsection

@section('content')
    @php
        $chartMax = max($data['NormalCount'], $data['RusakCount'], 1);
        $chartBars = [
            ['label' => 'Barang Normal', 'count' => $data['NormalCount'], 'color' => '#4e73df'],
            ['label' => 'Barang Rusak', 'count' => $data['RusakCount'], 'color' => '#e74a3b'],
        ];
    @endphp

    <div class="row">
        <div class="col-lg-6 col-md-6 col-12 mb-3" data-aos="fade-right">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Jumlah Inventaris
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($data['InventarisCount']) }}</div>
                        </div>
                        <div>
                            <i class="fas fa-fw fa-solid fa-cubes fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 col-md-6 col-12 mb-3" data-aos="fade-left">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Peminjaman Aktif
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($data['BorrowCount']) }}</div>
                        </div>
                        <div>
                            <i class="fas fa-fw fa-solid fa-hand-holding fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div><br>
    <div class="card shadow">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
                <h5 class="card-title mb-0">Kondisi Inventaris - {{ $months[$selectedMonth] }} {{ $selectedYear }}</h5>
                <form method="GET" action="{{ route('dashboard') }}" class="row g-2 align-items-end">
                    <div class="col-auto">
                        <label for="month" class="form-label">Bulan</label>
                        <select id="month" name="month" class="form-select">
                            @foreach ($months as $monthNumber => $monthName)
                                <option value="{{ $monthNumber }}" @selected($selectedMonth === $monthNumber)>
                                    {{ $monthName }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto">
                        <label for="year" class="form-label">Tahun</label>
                        <select id="year" name="year" class="form-select">
                            @foreach ($years as $year)
                                <option value="{{ $year }}" @selected($selectedYear === $year)>{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary">Filter</button>
                    </div>
                </form>
            </div>
            <div class="d-flex justify-content-around border-bottom" style="height: 240px"
                role="img"
                aria-label="Grafik batang jumlah inventaris normal dan rusak yang dibuat pada {{ $months[$selectedMonth] }} {{ $selectedYear }}">
                @foreach ($chartBars as $bar)
                    <div class="d-flex flex-column align-items-center justify-content-end h-100" style="width: 40%">
                        <strong class="mb-2">{{ number_format($bar['count']) }}</strong>
                        <div style="width: 100%; max-width: 120px; height: {{ ($bar['count'] / $chartMax) * 190 }}px; background-color: {{ $bar['color'] }};"
                            aria-hidden="true"></div>
                    </div>
                @endforeach
            </div>
            <div class="d-flex justify-content-around text-center mt-2">
                @foreach ($chartBars as $bar)
                    <strong style="width: 40%">{{ $bar['label'] }}</strong>
                @endforeach
            </div>
        </div>
    </div>
@endsection
