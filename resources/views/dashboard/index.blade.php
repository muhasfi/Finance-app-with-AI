@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Dashboard</h3>
                <p class="text-subtitle text-muted">{{ now()->translatedFormat('l, d F Y') }}</p>
            </div>
        </div>
    </div>

    {{-- Kartu ringkasan --}}
    <section class="row">
        <div class="col-12 col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body px-4 py-4-5">
                    <div class="row">
                        <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                            <div class="stats-icon blue mb-2">
                                <i class="iconly-boldWallet fs-3"></i>
                            </div>
                        </div>
                        <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                            <h6 class="text-muted font-semibold">Total Saldo</h6>
                            <h6 class="font-extrabold mb-0">Rp {{ number_format($totalBalance, 0, ',', '.') }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body px-4 py-4-5">
                    <div class="row">
                        <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                            <div class="stats-icon green mb-2">
                                <i class="iconly-boldArrow---Up-2 fs-3"></i>
                            </div>
                        </div>
                        <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                            <h6 class="text-muted font-semibold">Pemasukan Bulan Ini</h6>
                            <h6 class="font-extrabold mb-0 text-success">Rp {{ number_format($summary['income'], 0, ',', '.') }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body px-4 py-4-5">
                    <div class="row">
                        <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                            <div class="stats-icon red mb-2">
                                <i class="iconly-boldArrow---Down-2 fs-3"></i>
                            </div>
                        </div>
                        <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                            <h6 class="text-muted font-semibold">Pengeluaran Bulan Ini</h6>
                            <h6 class="font-extrabold mb-0 text-danger">Rp {{ number_format($summary['expense'], 0, ',', '.') }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body px-4 py-4-5">
                    <div class="row">
                        <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                            <div class="stats-icon {{ $summary['balance'] >= 0 ? 'green' : 'red' }} mb-2">
                                <i class="iconly-boldChart fs-3"></i>
                            </div>
                        </div>
                        <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                            <h6 class="text-muted font-semibold">Selisih Bulan Ini</h6>
                            <h6 class="font-extrabold mb-0 {{ $summary['balance'] >= 0 ? 'text-success' : 'text-danger' }}">
                                Rp {{ number_format($summary['balance'], 0, ',', '.') }}
                            </h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Chart --}}
    <section class="row">
        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4>Tren 6 Bulan Terakhir</h4>
                </div>
                <div class="card-body">
                    <canvas id="trendChart" height="280"></canvas>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h4>Pengeluaran per Kategori</h4>
                </div>
                <div class="card-body">
                    @if (count($categoryData) > 0)
                        <canvas id="categoryChart" height="220"></canvas>
                        <div class="mt-3">
                            @foreach (array_slice($categoryData, 0, 5) as $cat)
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge rounded-circle p-1" style="background:{{ $cat['color'] }}">&nbsp;</span>
                                        <span class="text-muted small">{{ $cat['label'] }}</span>
                                    </div>
                                    <span class="fw-semibold small">Rp {{ number_format($cat['amount'], 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-pie-chart fs-1 d-block mb-2"></i>
                            Belum ada pengeluaran bulan ini
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- Rekening & Transaksi terbaru --}}
    <section class="row">
        {{-- Rekening aktif --}}
        <div class="col-12 col-lg-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Rekening</h4>
                    <a href="{{ route('accounts.index') }}" class="btn btn-sm btn-light">Lihat semua</a>
                </div>
                <div class="card-body">
                    @forelse ($accounts as $account)
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar avatar-sm rounded"
                                     style="background:{{ $account->color }}20;width:36px;height:36px;display:flex;align-items:center;justify-content:center">
                                    <i class="{{ $account->icon }} fs-5" style="color:{{ $account->color }}"></i>
                                </div>
                                <div>
                                    <p class="mb-0 fw-semibold small">{{ $account->name }}</p>
                                    <small class="text-muted">{{ $account->type->label() }}</small>
                                </div>
                            </div>
                            <span class="fw-bold small {{ $account->balance >= 0 ? 'text-success' : 'text-danger' }}">
                                Rp {{ number_format($account->balance, 0, ',', '.') }}
                            </span>
                        </div>
                    @empty
                        <p class="text-muted text-center py-2">Belum ada rekening. <a href="{{ route('accounts.create') }}">Tambah sekarang</a></p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Transaksi terbaru --}}
        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Transaksi Terbaru</h4>
                    <a href="{{ route('transactions.index') }}" class="btn btn-sm btn-light">Lihat semua</a>
                </div>
                <div class="card-body">
                    <table class="table table-hover table-borderless">
                        <tbody>
                            @forelse ($recentTransactions as $tx)
                                <tr>
                                    <td width="40">
                                        <div class="avatar avatar-sm rounded"
                                             style="background:{{ $tx->category?->color ?? '#6b7280' }}20;width:36px;height:36px;display:flex;align-items:center;justify-content:center">
                                            <i class="{{ $tx->category?->icon ?? 'bi-tag' }} fs-5"
                                               style="color:{{ $tx->category?->color ?? '#6b7280' }}"></i>
                                        </div>
                                    </td>
                                    <td>
                                        <p class="mb-0 fw-semibold small">{{ $tx->note ?? '-' }}</p>
                                        <small class="text-muted">{{ $tx->category?->name ?? 'Tanpa kategori' }} &middot; {{ $tx->account->name }}</small>
                                    </td>
                                    <td class="text-end">
                                        <p class="mb-0 fw-bold small {{ $tx->type->value === 'income' ? 'text-success' : ($tx->type->value === 'expense' ? 'text-danger' : 'text-info') }}">
                                            {{ $tx->formatted_amount }}
                                        </p>
                                        <small class="text-muted">{{ $tx->date->format('d M Y') }}</small>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">
                                        Belum ada transaksi. <a href="{{ route('transactions.create') }}">Tambah sekarang</a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

</div>
@endsection

@push('scripts')
<script>
const trendData    = @json($trendData);
const categoryData = @json($categoryData);

// Line chart tren
const trendCtx = document.getElementById('trendChart');
if (trendCtx) {
    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: trendData.map(d => d.label),
            datasets: [
                {
                    label: 'Pemasukan',
                    data: trendData.map(d => d.income),
                    borderColor: '#198754',
                    backgroundColor: 'rgba(25,135,84,0.1)',
                    tension: 0.4,
                    fill: true,
                },
                {
                    label: 'Pengeluaran',
                    data: trendData.map(d => d.expense),
                    borderColor: '#dc3545',
                    backgroundColor: 'rgba(220,53,69,0.1)',
                    tension: 0.4,
                    fill: true,
                }
            ]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'top' } },
            scales: {
                y: {
                    ticks: {
                        callback: v => 'Rp ' + new Intl.NumberFormat('id').format(v)
                    }
                }
            }
        }
    });
}

// Donut chart kategori
const catCtx = document.getElementById('categoryChart');
if (catCtx && categoryData.length > 0) {
    new Chart(catCtx, {
        type: 'doughnut',
        data: {
            labels: categoryData.map(d => d.label),
            datasets: [{
                data: categoryData.map(d => d.amount),
                backgroundColor: categoryData.map(d => d.color),
                borderWidth: 2,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            cutout: '65%',
        }
    });
}
</script>
@endpush
