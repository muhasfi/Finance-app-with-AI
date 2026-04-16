<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar — {{ config('app.name', 'FinanceApp') }}</title>

    {{-- Mazer / Bootstrap --}}
    <link rel="stylesheet" href="{{ asset('assets/compiled/css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/compiled/css/app-dark.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/compiled/css/iconly.css') }}">

    {{-- Bootstrap Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        /* ── Root tokens (light) ────────────────────────────── */
        :root {
            --auth-bg:          #f0f4ff;
            --card-bg:          #ffffff;
            --card-border:      #dde3f0;
            --input-bg:         #f5f7ff;
            --input-border:     #d0d8ee;
            --input-focus-bg:   #ffffff;
            --text-primary:     #1a1d23;
            --text-secondary:   #5a6480;
            --text-muted:       #adb5bd;
            --brand:            #3a6cf4;
            --brand-hover:      #2755d8;
            --brand-soft:       #eaf0ff;
            --shadow-card:      0 4px 32px rgba(58, 108, 244, .10), 0 1px 4px rgba(0,0,0,.04);
            --shadow-btn:       0 4px 14px rgba(58, 108, 244, .38);
            --radius-card:      18px;
            --radius-input:     10px;
            --transition:       .2s ease;
        }

        /* ── Root tokens (dark) ─────────────────────────────── */
        [data-bs-theme="dark"] {
            --auth-bg:          #111827;
            --card-bg:          #1c2233;
            --card-border:      #2a3350;
            --input-bg:         #161d30;
            --input-border:     #2e3a55;
            --input-focus-bg:   #1e2740;
            --text-primary:     #e8ecf5;
            --text-secondary:   #8a97b8;
            --text-muted:       #4a5470;
            --brand:            #5a86f8;
            --brand-hover:      #3a6cf4;
            --brand-soft:       rgba(90,134,248,.13);
            --shadow-card:      0 4px 36px rgba(0,0,0,.4);
            --shadow-btn:       0 4px 16px rgba(90,134,248,.4);
        }

        /* ── Base ───────────────────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            min-height: 100vh;
            background-color: var(--auth-bg);
            background-image: radial-gradient(ellipse at 70% 20%, rgba(58,108,244,.08) 0%, transparent 60%),
                              radial-gradient(ellipse at 20% 80%, rgba(58,108,244,.05) 0%, transparent 50%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Nunito', 'Segoe UI', sans-serif;
            color: var(--text-primary);
            padding: 1.5rem 1rem;
            transition: background-color var(--transition), color var(--transition);
        }

        /* ── Theme toggle ───────────────────────────────────── */
        .theme-toggle {
            position: fixed;
            top: 1.25rem;
            right: 1.25rem;
            z-index: 999;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 1.5px solid var(--card-border);
            background: var(--card-bg);
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 1.1rem;
            transition: all var(--transition);
            box-shadow: 0 2px 8px rgba(0,0,0,.08);
        }
        .theme-toggle:hover {
            background: var(--brand-soft);
            color: var(--brand);
            border-color: var(--brand);
            transform: rotate(20deg);
        }

        /* ── Card ───────────────────────────────────────────── */
        .auth-card {
            background: var(--card-bg);
            border: 1.5px solid var(--card-border);
            border-radius: var(--radius-card);
            box-shadow: var(--shadow-card);
            width: 100%;
            max-width: 460px;
            padding: 2.5rem 2.25rem;
            transition: background var(--transition), border-color var(--transition);
        }

        /* ── Brand logo area ────────────────────────────────── */
        .auth-brand {
            text-align: center;
            margin-bottom: 2rem;
        }
        .auth-brand .brand-icon {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            background: linear-gradient(135deg, var(--brand) 0%, #6a3de8 100%);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.6rem;
            margin-bottom: .85rem;
            box-shadow: var(--shadow-btn);
        }
        .auth-brand h1 {
            font-size: 1.4rem;
            font-weight: 800;
            color: var(--text-primary);
            letter-spacing: -.4px;
        }
        .auth-brand h1 span { color: var(--brand); }
        .auth-brand p {
            font-size: .875rem;
            color: var(--text-secondary);
            margin-top: .3rem;
        }

        /* ── Form group ─────────────────────────────────────── */
        .form-group {
            margin-bottom: 1.15rem;
        }
        .form-label {
            display: block;
            font-size: .82rem;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: .5px;
            margin-bottom: .45rem;
        }

        /* ── Input wrapper (icon inside) ────────────────────── */
        .input-wrapper {
            position: relative;
        }
        .input-icon {
            position: absolute;
            left: .9rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 1rem;
            pointer-events: none;
            transition: color var(--transition);
        }
        .form-control {
            width: 100%;
            padding: .75rem 2.75rem .75rem 2.6rem;
            background: var(--input-bg);
            border: 1.5px solid var(--input-border);
            border-radius: var(--radius-input);
            color: var(--text-primary);
            font-size: .92rem;
            outline: none;
            transition: all var(--transition);
        }
        .form-control::placeholder { color: var(--text-muted); }
        .form-control:focus {
            background: var(--input-focus-bg);
            border-color: var(--brand);
            box-shadow: 0 0 0 3px var(--brand-soft);
        }
        .input-wrapper:focus-within .input-icon { color: var(--brand); }

        /* ── Field error ────────────────────────────────────── */
        .field-error {
            margin-top: .4rem;
            font-size: .8rem;
            color: #ef4444;
        }
        [data-bs-theme="dark"] .field-error { color: #f87171; }

        /* ── Toggle password eye ────────────────────────────── */
        .toggle-password {
            position: absolute;
            right: .9rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            cursor: pointer;
            font-size: 1rem;
            transition: color var(--transition);
            background: none;
            border: none;
            padding: 0;
        }
        .toggle-password:hover { color: var(--brand); }

        /* ── Password strength indicator ────────────────────── */
        .password-strength {
            margin-top: .5rem;
            display: flex;
            gap: .3rem;
            align-items: center;
        }
        .strength-bar {
            height: 4px;
            flex: 1;
            border-radius: 4px;
            background: var(--input-border);
            transition: background .3s;
        }
        .strength-label {
            font-size: .75rem;
            color: var(--text-muted);
            min-width: 60px;
            text-align: right;
        }

        /* ── Submit button ──────────────────────────────────── */
        .btn-register {
            width: 100%;
            padding: .8rem;
            background: linear-gradient(135deg, var(--brand) 0%, #6a3de8 100%);
            color: #fff;
            border: none;
            border-radius: var(--radius-input);
            font-size: .95rem;
            font-weight: 700;
            letter-spacing: .2px;
            cursor: pointer;
            transition: all var(--transition);
            box-shadow: var(--shadow-btn);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            margin-top: 1.4rem;
        }
        .btn-register:hover {
            opacity: .92;
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(58,108,244,.45);
        }
        .btn-register:active { transform: translateY(0); }

        /* ── Divider ────────────────────────────────────────── */
        .auth-divider {
            display: flex;
            align-items: center;
            gap: .75rem;
            margin: 1.5rem 0 1.25rem;
            color: var(--text-muted);
            font-size: .78rem;
        }
        .auth-divider::before,
        .auth-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--card-border);
        }

        /* ── Login link ─────────────────────────────────────── */
        .auth-footer {
            text-align: center;
            font-size: .875rem;
            color: var(--text-secondary);
        }
        .auth-footer a {
            color: var(--brand);
            font-weight: 700;
            text-decoration: none;
        }
        .auth-footer a:hover { text-decoration: underline; }

        /* ── Terms note ─────────────────────────────────────── */
        .terms-note {
            text-align: center;
            font-size: .78rem;
            color: var(--text-muted);
            margin-top: 1rem;
            line-height: 1.5;
        }
        .terms-note a {
            color: var(--brand);
            text-decoration: none;
        }
        .terms-note a:hover { text-decoration: underline; }

        /* ── Mobile ─────────────────────────────────────────── */
        @media (max-width: 480px) {
            .auth-card {
                padding: 2rem 1.35rem;
                border-radius: 14px;
            }
            .auth-brand h1 { font-size: 1.2rem; }
            .btn-register { font-size: .9rem; }
        }
    </style>
</head>

<body>

    {{-- ── Theme toggle button ── --}}
    <button class="theme-toggle" id="themeToggle" title="Toggle dark/light mode">
        <i class="bi bi-moon-stars-fill" id="themeIcon"></i>
    </button>

    {{-- ── Auth card ── --}}
    <div class="auth-card">

        {{-- Brand --}}
        <div class="auth-brand">
            <div class="brand-icon">
                <i class="bi bi-bar-chart-line-fill"></i>
            </div>
            <h1>Finance<span>App</span></h1>
            <p>Buat akun baru dan mulai kelola keuangan Anda</p>
        </div>

        {{-- Form --}}
        <form method="POST" action="{{ route('register') }}">
            @csrf

            {{-- Name --}}
            <div class="form-group">
                <label class="form-label" for="name">Nama Lengkap</label>
                <div class="input-wrapper">
                    <input
                        id="name"
                        type="text"
                        name="name"
                        class="form-control"
                        value="{{ old('name') }}"
                        placeholder="John Doe"
                        required
                        autofocus
                        autocomplete="name"
                    >
                    <i class="bi bi-person input-icon"></i>
                </div>
                @error('name')
                    <div class="field-error">
                        <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                    </div>
                @enderror
            </div>

            {{-- Email --}}
            <div class="form-group">
                <label class="form-label" for="email">Email</label>
                <div class="input-wrapper">
                    <input
                        id="email"
                        type="email"
                        name="email"
                        class="form-control"
                        value="{{ old('email') }}"
                        placeholder="nama@email.com"
                        required
                        autocomplete="username"
                    >
                    <i class="bi bi-envelope input-icon"></i>
                </div>
                @error('email')
                    <div class="field-error">
                        <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                    </div>
                @enderror
            </div>

            {{-- Password --}}
            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <div class="input-wrapper">
                    <input
                        id="password"
                        type="password"
                        name="password"
                        class="form-control"
                        placeholder="Min. 8 karakter"
                        required
                        autocomplete="new-password"
                        oninput="checkStrength(this.value)"
                    >
                    <i class="bi bi-lock input-icon"></i>
                    <button type="button" class="toggle-password" id="togglePwd" tabindex="-1">
                        <i class="bi bi-eye" id="eyeIcon"></i>
                    </button>
                </div>
                {{-- Password strength bars --}}
                <div class="password-strength" id="strengthBars">
                    <div class="strength-bar" id="bar1"></div>
                    <div class="strength-bar" id="bar2"></div>
                    <div class="strength-bar" id="bar3"></div>
                    <div class="strength-bar" id="bar4"></div>
                    <span class="strength-label" id="strengthLabel"></span>
                </div>
                @error('password')
                    <div class="field-error">
                        <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                    </div>
                @enderror
            </div>

            {{-- Confirm Password --}}
            <div class="form-group">
                <label class="form-label" for="password_confirmation">Konfirmasi Password</label>
                <div class="input-wrapper">
                    <input
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        class="form-control"
                        placeholder="Ulangi password"
                        required
                        autocomplete="new-password"
                    >
                    <i class="bi bi-shield-lock input-icon"></i>
                    <button type="button" class="toggle-password" id="togglePwdConfirm" tabindex="-1">
                        <i class="bi bi-eye" id="eyeIconConfirm"></i>
                    </button>
                </div>
                @error('password_confirmation')
                    <div class="field-error">
                        <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                    </div>
                @enderror
            </div>

            {{-- Submit --}}
            <button type="submit" class="btn-register">
                <i class="bi bi-person-plus-fill"></i>
                Buat Akun
            </button>
        </form>

        {{-- Terms note --}}
        <p class="terms-note">
            Dengan mendaftar, Anda menyetujui <a href="#">Syarat &amp; Ketentuan</a>
            dan <a href="#">Kebijakan Privasi</a> kami.
        </p>

        {{-- Login link --}}
        <div class="auth-divider">atau</div>
        <div class="auth-footer">
            Sudah punya akun?
            <a href="{{ route('login') }}">Masuk sekarang</a>
        </div>

    </div>

    {{-- ── Scripts ── --}}
    <script src="{{ asset('assets/compiled/js/app.js') }}"></script>

    <script>
        // ── Dark / Light mode ──────────────────────────────────
        const html        = document.documentElement;
        const btn         = document.getElementById('themeToggle');
        const icon        = document.getElementById('themeIcon');
        const STORAGE_KEY = 'mazer_theme';

        function applyTheme(theme) {
            html.setAttribute('data-bs-theme', theme);
            icon.className = theme === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-stars-fill';
            localStorage.setItem(STORAGE_KEY, theme);
        }

        const saved = localStorage.getItem(STORAGE_KEY) || 'light';
        applyTheme(saved);

        btn.addEventListener('click', () => {
            const current = html.getAttribute('data-bs-theme');
            applyTheme(current === 'dark' ? 'light' : 'dark');
        });

        // ── Toggle show/hide password ──────────────────────────
        const togglePwd = document.getElementById('togglePwd');
        const pwdInput  = document.getElementById('password');
        const eyeIcon   = document.getElementById('eyeIcon');

        togglePwd.addEventListener('click', () => {
            const isText = pwdInput.type === 'text';
            pwdInput.type     = isText ? 'password' : 'text';
            eyeIcon.className = isText ? 'bi bi-eye' : 'bi bi-eye-slash';
        });

        const togglePwdConfirm = document.getElementById('togglePwdConfirm');
        const pwdConfirmInput  = document.getElementById('password_confirmation');
        const eyeIconConfirm   = document.getElementById('eyeIconConfirm');

        togglePwdConfirm.addEventListener('click', () => {
            const isText = pwdConfirmInput.type === 'text';
            pwdConfirmInput.type     = isText ? 'password' : 'text';
            eyeIconConfirm.className = isText ? 'bi bi-eye' : 'bi bi-eye-slash';
        });

        // ── Password strength checker ──────────────────────────
        function checkStrength(val) {
            let score = 0;
            if (val.length >= 8)              score++;
            if (/[A-Z]/.test(val))            score++;
            if (/[0-9]/.test(val))            score++;
            if (/[^A-Za-z0-9]/.test(val))     score++;

            const colors  = ['', '#ef4444', '#f97316', '#eab308', '#22c55e'];
            const labels  = ['', 'Lemah', 'Cukup', 'Kuat', 'Sangat Kuat'];
            const bars    = [
                document.getElementById('bar1'),
                document.getElementById('bar2'),
                document.getElementById('bar3'),
                document.getElementById('bar4'),
            ];
            const labelEl = document.getElementById('strengthLabel');

            bars.forEach((bar, i) => {
                bar.style.background = i < score ? colors[score] : '';
            });

            labelEl.textContent  = val.length ? labels[score] : '';
            labelEl.style.color  = colors[score] || 'var(--text-muted)';
        }
    </script>

</body>
</html>