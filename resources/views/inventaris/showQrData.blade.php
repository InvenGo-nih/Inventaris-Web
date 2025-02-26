<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $data->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container my-5">
        <div class="card">
            <img src="{{ asset('storage/' . $data->image) }}" class="card-img-top" alt="{{ $data->name }}">
            <div class="card-body">
                <p class="mb-2 border-bottom"><strong>Nama:</strong> <span class="float-end">{{ $data->name }}</span></p>
                <p class="mb-2 border-bottom"><strong>Spesifikasi:</strong> <span class="float-end">{{ $data->specification }}</span></p>
                <p class="mb-2 border-bottom"><strong>Kondisi:</strong> <span class="float-end">{{ $data->condition }}</span></p>
                <p class="mb-2 border-bottom"><strong>Lokasi:</strong> <span class="float-end">{{ $data->location }}</span></p>
                <p class="mb-2 border-bottom"><strong>Deskripsi Rusak:</strong> <span class="float-end">{{ $data->broken_description ? $data->broken_description : '-' }}</span></p>
                <p class="mb-2 border-bottom"><strong>Status:</strong> <span class="float-end">{{ $data->status }}</span></p>
                <p class="mb-2 border-bottom"><strong>No.Serial:</strong> <span class="float-end">{{ $data->serial_number }}</span></p>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>