<x-guest-layout>
<div id="auth-left">
    <div class="auth-logo">
        <a href="/">{{ config('app.name') }}</a>
    </div>
    <h1 class="auth-title">Masuk</h1>
    <p class="auth-subtitle mb-5">Kelola keuangan Anda dengan mudah.</p>

    {{-- Error validasi --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $error)
                <p class="mb-0">{{ $error }}</p>
            @endforeach
        </div>
    @endif

    {{-- Session status (misal: setelah reset password) --}}
    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <form action="{{ route('login') }}" method="POST">
        @csrf

        <div class="form-group position-relative has-icon-left mb-4">
            <input type="email" name="email"
                   class="form-control form-control-xl @error('email') is-invalid @enderror"
                   value="{{ old('email') }}" placeholder="Email" required autofocus>
            <div class="form-control-icon">
                <i class="bi bi-envelope"></i>
            </div>
        </div>

        <div class="form-group position-relative has-icon-left mb-4">
            <input type="password" name="password"
                   class="form-control form-control-xl @error('password') is-invalid @enderror"
                   placeholder="Password" required>
            <div class="form-control-icon">
                <i class="bi bi-shield-lock"></i>
            </div>
        </div>

        <div class="form-check form-check-lg d-flex align-items-end mb-4">
            <input class="form-check-input me-2" type="checkbox" name="remember" id="remember"
                   {{ old('remember') ? 'checked' : '' }}>
            <label class="form-check-label text-gray-600" for="remember">Ingat saya</label>
        </div>

        <button class="btn btn-primary btn-block btn-lg shadow-lg mt-2 w-100" type="submit">Masuk</button>
    </form>

    <div class="text-center mt-5 text-lg fs-4">
        @if (Route::has('password.request'))
            <a class="font-bold" href="{{ route('password.request') }}">Lupa password?</a>
        @endif
    </div>

    <div class="text-center mt-3 fs-5">
        Belum punya akun?
        <a href="{{ route('register') }}" class="font-bold">Daftar</a>
    </div>
</div>

<div id="auth-right"></div>
</x-guest-layout>