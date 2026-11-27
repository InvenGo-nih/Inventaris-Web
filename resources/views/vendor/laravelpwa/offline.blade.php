<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Offline - {{ config('app.name', 'InvenGo') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="d-flex align-items-center justify-content-center vh-100 bg-light">
    <div class="text-center p-4">
        <h1 class="h3 mb-3">Anda sedang offline</h1>
        <p class="text-muted mb-4">Periksa koneksi internet Anda, lalu muat ulang halaman ini.</p>
        <a href="/" class="btn btn-primary">Muat Ulang</a>
    </div>
</body>

</html>
