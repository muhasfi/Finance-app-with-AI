<div id="sidebar">
    <div class="sidebar-wrapper active">
        <div class="sidebar-header position-relative">
            <div class="d-flex justify-content-between align-items-center">
                <div class="logo">
                    <a href="{{ route('dashboard') }}">
                        <h4 class="mb-0 text-primary">{{ config('app.name') }}</h4>
                    </a>
                </div>
                <div class="theme-toggle d-flex align-items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z"/></svg>
                    <div class="form-check form-switch fs-6 mb-0">
                        <input class="form-check-input me-0" type="checkbox" id="toggle-dark" style="cursor:pointer">
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"/></svg>
                </div>
                <div class="sidebar-toggler x">
                    <a href="#" class="sidebar-hide d-xl-none d-block"><i class="bi bi-x bi-middle"></i></a>
                </div>
            </div>
        </div>

        <div class="sidebar-menu">
            <ul class="menu">
                <li class="sidebar-title">Menu Utama</li>

                <li class="sidebar-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <a href="{{ route('dashboard') }}" class="sidebar-link">
                        <i class="bi bi-grid-fill"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <li class="sidebar-item {{ request()->routeIs('transactions.*') ? 'active' : '' }}">
                    <a href="{{ route('transactions.index') }}" class="sidebar-link">
                        <i class="bi bi-arrow-left-right"></i>
                        <span>Transaksi</span>
                    </a>
                </li>

                <li class="sidebar-item {{ request()->routeIs('accounts.*') ? 'active' : '' }}">
                    <a href="{{ route('accounts.index') }}" class="sidebar-link">
                        <i class="bi bi-wallet2"></i>
                        <span>Rekening</span>
                    </a>
                </li>

                <li class="sidebar-item {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                    <a href="{{ route('categories.index') }}" class="sidebar-link">
                        <i class="bi bi-tags"></i>
                        <span>Kategori</span>
                    </a>
                </li>

                {{-- AI Features — hanya tampil jika API key sudah diset --}}
                @if (config('services.gemini.api_key'))
                <li class="sidebar-title">AI Keuangan</li>

                <li class="sidebar-item {{ request()->routeIs('ai.chat') ? 'active' : '' }}">
                    <a href="{{ route('ai.chat') }}" class="sidebar-link">
                        <i class="bi bi-chat-dots"></i>
                        <span>Tanya Fina</span>
                    </a>
                </li>

                <li class="sidebar-item {{ request()->routeIs('ai.insights') ? 'active' : '' }}">
                    <a href="{{ route('ai.insights') }}" class="sidebar-link">
                        <i class="bi bi-stars"></i>
                        <span>AI Insight</span>
                    </a>
                </li>
                @endif

                {{-- Admin only --}}
                @if (auth()->user()->isAdmin())
                <li class="sidebar-title">Admin</li>

                <li class="sidebar-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <a href="{{ route('admin.dashboard') }}" class="sidebar-link">
                        <i class="bi bi-speedometer2"></i>
                        <span>Admin Dashboard</span>
                    </a>
                </li>

                <li class="sidebar-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.users.index') }}" class="sidebar-link">
                        <i class="bi bi-people"></i>
                        <span>Manajemen User</span>
                    </a>
                </li>
                @endif

            </ul>
        </div>
    </div>
</div>