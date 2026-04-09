<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 — Halaman Tidak Ditemukan</title>
    <link rel="stylesheet" href="{{ asset('assets/compiled/css/app.css') }}">
</head>
<body>
<div class="d-flex align-items-center justify-content-center" style="min-height:100vh">
    <div class="text-center">
        <h1 style="font-size:6rem;font-weight:700;color:#6b7280;line-height:1">404</h1>
        <h4 class="mb-2">Halaman Tidak Ditemukan</h4>
        <p class="text-muted mb-4">Halaman yang Anda cari tidak ada atau telah dipindahkan.</p>
        <a href="{{ url()->previous() }}" class="btn btn-light me-2">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
        <a href="{{ route('dashboard') }}" class="btn btn-primary">
            <i class="bi bi-grid me-1"></i> Dashboard
        </a>
    </div>
</div>
</body>
</html>
