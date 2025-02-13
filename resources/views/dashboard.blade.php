@extends('layouts.app')

@section('title')
    Dashboard
@endsection

@section('content')
    <div id="chart"></div>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        fetch("{{ route('chart_data') }}") // Ambil data dari backend
        .then(response => response.json())
        .then(data => {
            console.log(data);
            var options = {
                chart: { type: 'bar', height: 350 },
                series: [{
                    name: 'Jumlah',
                    data: [data.data.InventarisCount, data.data.BorrowCount] // Ambil nilai dari JSON yang baru
                }],
                xaxis: {
                    categories: ['Jumlah Inventaris', 'Jumlah Peminjaman'] // Label untuk sumbu X
                }
            };

            new ApexCharts(document.querySelector("#chart"), options).render();
        });
    </script>
@endsection