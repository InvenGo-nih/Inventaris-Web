@extends('layouts.app')

@section('title')
    Dashboard
@endsection

@section('content')

<div class="row">
    <!-- Barang Normal -->
    <div class="col-lg-6 col-md-6 col-12 mb-3" data-aos="fade-right">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Jumlah Inventaris
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="inventaris-count">0</div>
                    </div>
                    <div>
                        <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Barang Rusak -->
    <div class="col-lg-6 col-md-6 col-12 mb-3" data-aos="fade-left">
        <div class="card border-left-danger shadow h-100 py-2">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                            Jumlah Peminjam
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="borrow-count">0</div>
                    </div>
                    <div>
                        <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<hr>
    <div id="chart"></div>


    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        fetch("{{ route('chart_data') }}") // Ambil data dari backend
        .then(response => response.json())
        .then(data => {
            console.log(data);
             // Update jumlah barang Normal & Rusak
             document.getElementById("inventaris-count").innerText = data.data.InventarisCount;
             document.getElementById("borrow-count").innerText = data.data.BorrowCount;
            var options = {
                chart: { type: 'bar', height: 350 },
                series: [{
                    name: 'Jumlah',
                    data: [data.data.NormalCount,
                    data.data.RusakCount, 
                    ] // Ambil nilai dari JSON yang baru
                }],
                xaxis: {
                    categories: ['Barang Normal', 'Barang rusak'] // Label untuk sumbu X
                }
            };

            new ApexCharts(document.querySelector("#chart"), options).render();
        });
    </script>
@endsection