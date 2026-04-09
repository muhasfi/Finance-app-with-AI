<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 — Server Error</title>
    <link rel="stylesheet" href="{{ asset('assets/compiled/css/app.css') }}">
</head>
<body>
<div class="d-flex align-items-center justify-content-center" style="min-height:100vh">
    <div class="text-center">
        <h1 style="font-size:6rem;font-weight:700;color:#f59e0b;line-height:1">500</h1>
        <h4 class="mb-2">Terjadi Kesalahan Server</h4>
        <p class="text-muted mb-4">Maaf, ada masalah di server kami. Tim kami sedang menanganinya.</p>
        <a href="{{ route('dashboard') }}" class="btn btn-primary">
            <i class="bi bi-grid me-1"></i> Kembali ke Dashboard
        </a>
    </div>
</div>
</body>
</html>
