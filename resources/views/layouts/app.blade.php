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

    <script src="{{ asset('assets/static/js/components/dark.js') }}"></script>
    <script src="{{ asset('assets/extensions/perfect-scrollbar/perfect-scrollbar.min.js') }}"></script>
    <script src="{{ asset('assets/compiled/js/app.js') }}"></script>

    <!-- Need: Apexcharts -->
    <script src="{{ asset('assets/extensions/apexcharts/apexcharts.min.js') }}"></script>
    <script src="{{ asset('assets/static/js/pages/dashboard.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    

    @stack('scripts')
    @yield('script')

</body>

</html>