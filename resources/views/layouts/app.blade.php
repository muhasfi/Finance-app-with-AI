@include('layouts.partials.header')

<body>
    <script src="{{ asset('assets/static/js/initTheme.js') }}"></script>
    @include('layouts.partials.sidebar')

    <div id="app">
        <div id="main">

            {{-- Navbar --}}
            @include('layouts.partials.navbar')

            {{-- Flash messages --}}
            <div class="px-3">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
            </div>

            {{-- Content --}}
            <div class="page-content">
                @yield('content')
            </div>

            {{-- Footer --}}
            @include('layouts.partials.footer')

        </div>
    </div>

    {{-- Toast Container --}}
    <div id="toastContainer"
         style="position:fixed;bottom:1.5rem;right:1.5rem;z-index:9999;display:flex;flex-direction:column;align-items:flex-end">
    </div>

    <script src="{{ asset('assets/static/js/components/dark.js') }}"></script>
    <script src="{{ asset('assets/extensions/perfect-scrollbar/perfect-scrollbar.min.js') }}"></script>
    <script src="{{ asset('assets/compiled/js/app.js') }}"></script>

    <!-- Need: Apexcharts -->
    <script src="{{ asset('assets/extensions/apexcharts/apexcharts.min.js') }}"></script>
    <script src="{{ asset('assets/static/js/pages/dashboard.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    {{-- <script src="{{ asset('assets/extensions/simple-datatables/umd/simple-datatables.js') }}"></script>
    <script src="{{ asset('assets/static/js/pages/simple-datatables.js') }}"></script> --}}

    {{-- Pusher + Laravel Echo --}}
    @if (config('broadcasting.connections.pusher.key'))
    <script src="https://cdn.jsdelivr.net/npm/pusher-js@8.3.0/dist/web/pusher.min.js"></script>
    <script>
    const _pusherKey     = '{{ config("broadcasting.connections.pusher.key") }}';
    const _pusherCluster = '{{ config("broadcasting.connections.pusher.options.cluster", "ap1") }}';
    const _userId        = '{{ auth()->id() }}';
    const _csrfToken     = document.querySelector('meta[name="csrf-token"]').content;

    // Inisialisasi Pusher
    const pusher = new Pusher(_pusherKey, {
        cluster: _pusherCluster,
        authEndpoint: '/broadcasting/auth',
        auth: {
            headers: { 'X-CSRF-TOKEN': _csrfToken }
        }
    });

    // Debug koneksi Pusher (bisa dihapus setelah production)
    pusher.connection.bind('connected', () => console.log('✅ Pusher connected, userId:', _userId));
    pusher.connection.bind('error', (err) => console.error('❌ Pusher error:', err));

    // Subscribe ke private channel user
    const channel = pusher.subscribe('private-user.' + _userId);

    channel.bind('pusher:subscription_succeeded', () => {
        console.log('✅ Subscribed to private-user.' + _userId);
    });

    channel.bind('pusher:subscription_error', (err) => {
        console.error('❌ Subscription error:', err);
    });

    // Dengarkan event budget alert
    channel.bind('budget.alert', function(data) {
        console.log('📢 budget.alert diterima:', data);
        showToast(data.message, data.type === 'exceeded' ? 'danger' : 'warning');
        refreshNotifBadge();

        // FIX: Cek class 'show' pada .dropdown-menu bukan wrapper div
        const notifMenu = document.querySelector('#notifDropdown .dropdown-menu');
        if (notifMenu && notifMenu.classList.contains('show')) {
            loadNotifications();
        }
    });

    // Dengarkan event tagihan jatuh tempo
    channel.bind('bill.due', function(data) {
        console.log('📢 bill.due diterima:', data);
        showToast(data.message, 'info');
        refreshNotifBadge();

        // FIX: Cek class 'show' pada .dropdown-menu bukan wrapper div
        const notifMenu = document.querySelector('#notifDropdown .dropdown-menu');
        if (notifMenu && notifMenu.classList.contains('show')) {
            loadNotifications();
        }
    });

    // ── Toast notification ──────────────────────────────────────────────────
    function showToast(message, type = 'info') {
        const colors = {
            danger : { bg: '#dc3545', icon: 'bi-exclamation-octagon' },
            warning: { bg: '#f59e0b', icon: 'bi-exclamation-triangle' },
            info   : { bg: '#3b82f6', icon: 'bi-bell'                },
            success: { bg: '#22c55e', icon: 'bi-check-circle'        },
        };
        const c  = colors[type] || colors.info;
        const id = 'toast_' + Date.now();
        const html = `
            <div id="${id}" class="toast show align-items-center text-white border-0 mb-2"
                style="background:${c.bg};border-radius:10px;max-width:360px"
                role="alert">
                <div class="d-flex">
                    <div class="toast-body d-flex align-items-center gap-2">
                        <i class="bi ${c.icon} flex-shrink-0"></i>
                        <span style="font-size:13px">${message}</span>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto"
                            onclick="document.getElementById('${id}').remove()"></button>
                </div>
            </div>`;

        const container = document.getElementById('toastContainer');
        if (container) {
            container.insertAdjacentHTML('beforeend', html);
        }
        // Auto-dismiss setelah 6 detik
        setTimeout(() => document.getElementById(id)?.remove(), 6000);
    }

    // ── Notification dropdown ───────────────────────────────────────────────
    async function loadNotifications() {
        try {
            const res  = await fetch('/notifications', {
                headers: { 'X-CSRF-TOKEN': _csrfToken, 'Accept': 'application/json' }
            });
            const data = await res.json();

            const list    = document.getElementById('notifList');
            const badge   = document.getElementById('notifBadge');
            const markBtn = document.getElementById('markAllBtn');

            // Guard jika element tidak ada
            if (!list || !badge || !markBtn) return;

            // Update badge
            if (data.unread_count > 0) {
                badge.textContent = data.unread_count > 9 ? '9+' : data.unread_count;
                badge.style.display = 'block';
                markBtn.style.display = 'block';
            } else {
                badge.style.display = 'none';
                markBtn.style.display = 'none';
            }

            if (data.notifications.length === 0) {
                list.innerHTML = `
                    <div class="text-center text-muted py-4 small">
                        <i class="bi bi-bell-slash d-block fs-3 mb-1"></i>
                        Tidak ada notifikasi
                    </div>`;
                return;
            }

            const colorMap = {
                danger   : '#dc3545',
                warning  : '#f59e0b',
                info     : '#3b82f6',
                success  : '#22c55e',
                secondary: '#6b7280'
            };

            list.innerHTML = data.notifications.map(n => `
                <div class="dropdown-item px-3 py-2 border-bottom ${n.read ? '' : 'bg-light'}"
                    style="cursor:pointer;white-space:normal"
                    onclick="handleNotifClick('${n.id}', '${n.url}')">
                    <div class="d-flex gap-2 align-items-start">
                        <i class="bi ${n.icon} flex-shrink-0 mt-1"
                           style="color:${colorMap[n.color] || '#6b7280'}"></i>
                        <div class="flex-fill">
                            <p class="mb-0 small ${n.read ? 'text-muted' : 'fw-semibold'}">${n.message}</p>
                            <small class="text-muted">${n.time}</small>
                        </div>
                        ${!n.read ? '<span class="rounded-circle bg-primary flex-shrink-0" style="width:7px;height:7px;margin-top:6px"></span>' : ''}
                    </div>
                </div>`).join('');

        } catch (err) {
            console.error('❌ loadNotifications error:', err);
        }
    }

    async function handleNotifClick(id, url) {
        try {
            await fetch(`/notifications/${id}/read`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': _csrfToken }
            });
            if (url && url !== '#') window.location.href = url;
            else loadNotifications();
        } catch (err) {
            console.error('❌ handleNotifClick error:', err);
        }
    }

    async function markAllRead() {
        try {
            await fetch('/notifications/read-all', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': _csrfToken }
            });
            loadNotifications();
        } catch (err) {
            console.error('❌ markAllRead error:', err);
        }
    }

    // FIX: Tambah null check & try-catch agar tidak error di tab idle
    async function refreshNotifBadge() {
        try {
            const res  = await fetch('/notifications', {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': _csrfToken }
            });
            const data = await res.json();
            const badge = document.getElementById('notifBadge');

            console.log('🔍 badge element:', badge);       // ← cek apakah null
            console.log('🔍 unread_count:', data.unread_count); // ← cek datanya

            if (!badge) return;

            if (data.unread_count > 0) {
                badge.textContent = data.unread_count > 9 ? '9+' : data.unread_count;
                badge.style.display = 'block';
            } else {
                badge.style.display = 'none';
            }
        } catch (err) {
            console.error('❌ refreshNotifBadge error:', err);
        }
    }

    // FIX: Tunggu DOM siap sebelum load badge
    document.addEventListener('DOMContentLoaded', () => {
        refreshNotifBadge();
    });
    </script>
    @endif

    @stack('scripts')
    @yield('script')

</body>

</html>