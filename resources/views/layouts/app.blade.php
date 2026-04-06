<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — {{ config('app.name') }}</title>

    <link rel="stylesheet" crossorigin href="{{ asset('assets/compiled/css/app.css') }}">
    <link rel="stylesheet" crossorigin href="{{ asset('assets/compiled/css/app-dark.css') }}">
    <link rel="stylesheet" crossorigin href="{{ asset('assets/compiled/css/iconly.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/extensions/simple-datatables/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/compiled/css/table-datatable.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="{{ asset('assets/extensions/@icon/dripicons/dripicons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/compiled/css/ui-icons-dripicons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/extensions/dripicons/webfont.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/extensions/dripicons/dripicons.css') }}">

    @stack('styles')
    @yield('css')
</head>
<body>
    <script src="{{ asset('assets/static/js/initTheme.js') }}"></script>

    {{-- Sidebar --}}
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    @stack('scripts')
    @yield('script')
</body>
</html>