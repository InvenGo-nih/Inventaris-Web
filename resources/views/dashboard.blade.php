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
                            <i class="fas fa-fw fa-solid fa-cubes fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Barang Rusak -->
        <div class="col-lg-6 col-md-6 col-12 mb-3" data-aos="fade-left">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Jumlah Peminjam
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="borrow-count">0</div>
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
            <div id="chart"></div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        fetch("{{ route('chart_data') }}")
            .then(response => response.json())
            .then(data => {
                // console.log(data);

                // Update jumlah barang Normal & Rusak
                document.getElementById("inventaris-count").innerText = data.data.InventarisCount;
                document.getElementById("borrow-count").innerText = data.data.BorrowCount;

                var options = {
                    chart: {
                        type: 'bar',
                        height: 350
                    },
                    series: [{
                        name: 'Jumlah',
                        data: [data.data.NormalCount, data.data.RusakCount]
                    }],
                    plotOptions: {
                        bar: {
                            distributed: true, // Pastikan setiap bar mendapat warna sesuai urutannya
                        }
                    },
                    colors: ['#4e73df', '#e74a3b'], // Warna pertama untuk normal, kedua untuk rusak
                    xaxis: {
                        categories: ['Barang Normal', 'Barang Rusak']
                    }
                };

                new ApexCharts(document.querySelector("#chart"), options).render();
            })
            .catch(error => console.error("Error fetching data:", error));
    </script>
@endsection
