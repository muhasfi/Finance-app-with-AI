<x-guest-layout>
<div id="auth-left">
    <div class="auth-logo">
        <a href="/">{{ config('app.name') }}</a>
    </div>
    <h1 class="auth-title">Daftar</h1>
    <p class="auth-subtitle mb-5">Buat akun baru untuk mulai mengelola keuangan Anda.</p>

    {{-- Error validasi --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $error)
                <p class="mb-0">{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form action="{{ route('register') }}" method="POST">
        @csrf

        {{-- Nama --}}
        <div class="form-group position-relative has-icon-left mb-4">
            <input type="text" name="name"
                   class="form-control form-control-xl @error('name') is-invalid @enderror"
                   value="{{ old('name') }}" placeholder="Nama Lengkap" required autofocus autocomplete="name">
            <div class="form-control-icon">
                <i class="bi bi-person"></i>
            </div>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Email --}}
        <div class="form-group position-relative has-icon-left mb-4">
            <input type="email" name="email"
                   class="form-control form-control-xl @error('email') is-invalid @enderror"
                   value="{{ old('email') }}" placeholder="Email" required autocomplete="username">
            <div class="form-control-icon">
                <i class="bi bi-envelope"></i>
            </div>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Password --}}
        <div class="form-group position-relative has-icon-left mb-4">
            <input type="password" name="password"
                   class="form-control form-control-xl @error('password') is-invalid @enderror"
                   placeholder="Password" required autocomplete="new-password">
            <div class="form-control-icon">
                <i class="bi bi-shield-lock"></i>
            </div>
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Konfirmasi Password --}}
        <div class="form-group position-relative has-icon-left mb-4">
            <input type="password" name="password_confirmation"
                   class="form-control form-control-xl"
                   placeholder="Konfirmasi Password" required autocomplete="new-password">
            <div class="form-control-icon">
                <i class="bi bi-shield-check"></i>
            </div>
        </div>

        <button class="btn btn-primary btn-block btn-lg shadow-lg mt-2 w-100" type="submit">Daftar</button>
    </form>

    <div class="text-center mt-3 fs-5">
        Sudah punya akun?
        <a href="{{ route('login') }}" class="font-bold">Masuk</a>
    </div>
</div>

<div id="auth-right"></div>
</x-guest-layout>