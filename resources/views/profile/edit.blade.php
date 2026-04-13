@extends('layouts.app')
@section('title', 'Profil Saya')

@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6">
                <h3>Profil Saya</h3>
                <p class="text-subtitle text-muted">Kelola informasi akun Anda</p>
            </div>
        </div>
    </div>

    <div class="row">

        {{-- Update profil --}}
        <div class="col-12 col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Informasi Akun</h5>
                </div>
                <div class="card-body">

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if ($errors->updateProfile->any())
                        <div class="alert alert-danger">
                            @foreach ($errors->updateProfile->all() as $error)
                                <p class="mb-0">{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('profile.update') }}">
                        @csrf @method('PATCH')

                        <div class="mb-3">
                            <label class="form-label">Nama <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name', 'updateProfile') is-invalid @enderror"
                                   value="{{ old('name', $user->name) }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control @error('email', 'updateProfile') is-invalid @enderror"
                                   value="{{ old('email', $user->email) }}" required>
                            @if ($user->email_verified_at === null)
                                <div class="form-text text-warning">
                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                    Email belum diverifikasi.
                                    <form method="POST" action="{{ route('verification.send') }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-link p-0 text-warning fw-semibold" 
                                                style="vertical-align: baseline; text-decoration: underline;">
                                            Kirim ulang verifikasi
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Mata Uang Default</label>
                                <select name="currency" class="form-select @error('currency', 'updateProfile') is-invalid @enderror">
                                    <option value="IDR" {{ old('currency', $user->currency) === 'IDR' ? 'selected' : '' }}>IDR — Rupiah</option>
                                    <option value="USD" {{ old('currency', $user->currency) === 'USD' ? 'selected' : '' }}>USD — Dollar</option>
                                    <option value="SGD" {{ old('currency', $user->currency) === 'SGD' ? 'selected' : '' }}>SGD — Singapore Dollar</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Zona Waktu</label>
                                <select name="timezone" class="form-select @error('timezone', 'updateProfile') is-invalid @enderror">
                                    <option value="Asia/Jakarta"    {{ old('timezone', $user->timezone) === 'Asia/Jakarta'    ? 'selected' : '' }}>WIB — Asia/Jakarta</option>
                                    <option value="Asia/Makassar"   {{ old('timezone', $user->timezone) === 'Asia/Makassar'   ? 'selected' : '' }}>WITA — Asia/Makassar</option>
                                    <option value="Asia/Jayapura"   {{ old('timezone', $user->timezone) === 'Asia/Jayapura'   ? 'selected' : '' }}>WIT — Asia/Jayapura</option>
                                    <option value="Asia/Singapore"  {{ old('timezone', $user->timezone) === 'Asia/Singapore'  ? 'selected' : '' }}>SGT — Asia/Singapore</option>
                                </select>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i> Simpan Perubahan
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Ganti password --}}
        <div class="col-12 col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Ganti Password</h5>
                </div>
                <div class="card-body">

                    @if ($errors->updatePassword->any())
                        <div class="alert alert-danger">
                            @foreach ($errors->updatePassword->all() as $error)
                                <p class="mb-0">{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.update') }}">
                        @csrf @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Password Saat Ini <span class="text-danger">*</span></label>
                            <input type="password" name="current_password"
                                   class="form-control @error('current_password', 'updatePassword') is-invalid @enderror"
                                   required autocomplete="current-password">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password Baru <span class="text-danger">*</span></label>
                            <input type="password" name="password"
                                   class="form-control @error('password', 'updatePassword') is-invalid @enderror"
                                   required autocomplete="new-password">
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                            <input type="password" name="password_confirmation"
                                   class="form-control" required autocomplete="new-password">
                        </div>

                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-shield-lock me-1"></i> Ganti Password
                        </button>
                    </form>
                </div>
            </div>

            {{-- Hapus akun --}}
            <div class="card border-danger mt-3">
                <div class="card-header bg-danger bg-opacity-10">
                    <h5 class="mb-0 text-danger">Hapus Akun</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        Setelah akun dihapus, semua data keuangan Anda akan ikut terhapus permanen. Tindakan ini tidak bisa dibatalkan.
                    </p>

                    <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                        <i class="bi bi-trash me-1"></i> Hapus Akun Saya
                    </button>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- Modal konfirmasi hapus akun --}}
<div class="modal fade" id="deleteAccountModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">Hapus Akun</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('profile.destroy') }}">
                @csrf @method('DELETE')
                <div class="modal-body">
                    <p>Masukkan password Anda untuk mengkonfirmasi penghapusan akun.</p>
                    <div class="form-group">
                        <input type="password" name="password" class="form-control @error('password', 'userDeletion') is-invalid @enderror"
                               placeholder="Password" required>
                        @error('password', 'userDeletion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Ya, Hapus Akun Saya</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
