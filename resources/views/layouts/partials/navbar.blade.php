<header class="mb-3">
    <nav class="navbar navbar-expand-lg navbar-light navbar-top">
        <div class="container-fluid">
            <a href="#" class="burger-btn d-block d-xl-none">
                <i class="bi bi-justify fs-3"></i>
            </a>

            <div class="navbar-nav ms-auto align-items-center">

                {{-- Notifikasi --}}
                <div class="nav-item dropdown me-3">
                    <a href="#" class="nav-link" data-bs-toggle="dropdown">
                        <i class="bi bi-bell fs-5"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end">
                        <h6 class="dropdown-header">Notifikasi</h6>
                        <p class="text-center text-muted small py-2 mb-0">Tidak ada notifikasi baru</p>
                    </div>
                </div>

                {{-- User dropdown --}}
                <div class="nav-item dropdown">
                    <a href="#" class="nav-link d-flex align-items-center gap-2" data-bs-toggle="dropdown">
                        <div class="avatar avatar-sm">
                            @if (auth()->user()->avatar)
                                <img src="{{ Storage::url(auth()->user()->avatar) }}" alt="avatar" class="rounded-circle">
                            @else
                                <span class="avatar-content bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"
                                    style="width:32px;height:32px;font-size:13px;font-weight:500">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </span>
                            @endif
                        </div>
                        <span class="d-none d-md-block fw-semibold fs-6">{{ auth()->user()->name }}</span>
                        @if (auth()->user()->isAdmin())
                            <span class="badge bg-danger ms-1">Admin</span>
                        @endif
                    </a>

                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li>
                            <div class="dropdown-header px-3 py-2">
                                <p class="mb-0 fw-semibold">{{ auth()->user()->name }}</p>
                                <small class="text-muted">{{ auth()->user()->email }}</small>
                            </div>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('profile.edit') }}">
                                <i class="bi bi-person"></i> Profil
                            </a>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item d-flex align-items-center gap-2 text-danger">
                                    <i class="bi bi-box-arrow-right"></i> Keluar
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>

            </div>
        </div>
    </nav>
</header>