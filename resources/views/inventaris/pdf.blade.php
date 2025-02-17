<!DOCTYPE html>
<html>
<head>
    <title>Data Inventaris</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
            text-align: center;
            padding: 8px;
        }
    </style>
</head>
<body>
    <h2 style="text-align: center;">Laporan Inventaris</h2>
 <table border="1" width="100%">
    <thead>
        <tr>
            <th>No</th>
            <th>Nama</th>
            <th>Spesifikasi</th>
            <th>Kondisi</th>
            <th>QR Code</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $index => $item)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $item->name }}</td>
            <td>{{ $item->specification }}</td>
            <td>{{ $item->condition }}</td>
            <td>
                {!! $item->qr_code !!} <!-- Tampilkan SVG QR Code langsung -->
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

</body>
</html>
