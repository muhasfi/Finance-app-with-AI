@extends('layouts.app')
@section('title', 'Laporan & Export')

@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6">
                <h3>Laporan & Export</h3>
                <p class="text-subtitle text-muted">Export transaksi ke file CSV</p>
            </div>
        </div>
    </div>

    @include('components.alert')

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-download me-2"></i>Export CSV</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('reports.export') }}">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Dari Tanggal <span class="text-danger">*</span></label>
                                <input type="date" name="from" class="form-control @error('from') is-invalid @enderror"
                                       value="{{ old('from', now()->startOfMonth()->format('Y-m-d')) }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Sampai Tanggal <span class="text-danger">*</span></label>
                                <input type="date" name="to" class="form-control @error('to') is-invalid @enderror"
                                       value="{{ old('to', now()->format('Y-m-d')) }}" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tipe Transaksi</label>
                                <select name="type" class="form-select">
                                    <option value="">Semua tipe</option>
                                    <option value="income"   {{ old('type') === 'income'   ? 'selected' : '' }}>Pemasukan</option>
                                    <option value="expense"  {{ old('type') === 'expense'  ? 'selected' : '' }}>Pengeluaran</option>
                                    <option value="transfer" {{ old('type') === 'transfer' ? 'selected' : '' }}>Transfer</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Rekening</label>
                                <select name="account_id" class="form-select">
                                    <option value="">Semua rekening</option>
                                    @foreach ($accounts as $acc)
                                        <option value="{{ $acc->id }}" {{ old('account_id') == $acc->id ? 'selected' : '' }}>
                                            {{ $acc->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Kategori</label>
                            <select name="category_id" class="form-select">
                                <option value="">Semua kategori</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-file-earmark-spreadsheet me-1"></i> Download CSV
                        </button>

                    </form>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Shortcut Periode</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('reports.export', ['from' => now()->startOfMonth()->format('Y-m-d'), 'to' => now()->format('Y-m-d')]) }}"
                           class="btn btn-outline-secondary text-start">
                            <i class="bi bi-calendar-month me-2"></i>
                            Bulan ini ({{ now()->translatedFormat('F Y') }})
                        </a>
                        <a href="{{ route('reports.export', ['from' => now()->subMonth()->startOfMonth()->format('Y-m-d'), 'to' => now()->subMonth()->endOfMonth()->format('Y-m-d')]) }}"
                           class="btn btn-outline-secondary text-start">
                            <i class="bi bi-calendar-minus me-2"></i>
                            Bulan lalu ({{ now()->subMonth()->translatedFormat('F Y') }})
                        </a>
                        <a href="{{ route('reports.export', ['from' => now()->startOfYear()->format('Y-m-d'), 'to' => now()->format('Y-m-d')]) }}"
                           class="btn btn-outline-secondary text-start">
                            <i class="bi bi-calendar-range me-2"></i>
                            Tahun ini ({{ now()->year }})
                        </a>
                        <a href="{{ route('reports.export', ['from' => now()->subMonths(3)->startOfMonth()->format('Y-m-d'), 'to' => now()->format('Y-m-d')]) }}"
                           class="btn btn-outline-secondary text-start">
                            <i class="bi bi-calendar3 me-2"></i>
                            3 bulan terakhir
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
