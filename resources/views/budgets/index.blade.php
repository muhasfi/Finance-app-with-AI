@extends('layouts.app')
@section('title', 'Budget per Kategori')

@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row align-items-center">
            <div class="col-12 col-md-5">
                <h3>Budget per Kategori</h3>
                <p class="text-subtitle text-muted">
                    {{ \Carbon\Carbon::createFromDate($year, $month, 1)->translatedFormat('F Y') }}
                </p>
            </div>
            <div class="col-12 col-md-7 d-flex gap-2 justify-content-md-end align-items-center flex-wrap">
                {{-- Navigasi bulan --}}
                @php
                    $prev = \Carbon\Carbon::createFromDate($year, $month, 1)->subMonth();
                    $next = \Carbon\Carbon::createFromDate($year, $month, 1)->addMonth();
                @endphp
                <a href="{{ route('budgets.index', ['month' => $prev->month, 'year' => $prev->year]) }}"
                   class="btn btn-light btn-sm">
                    <i class="bi bi-chevron-left"></i>
                </a>

                <form method="GET" action="{{ route('budgets.index') }}" class="d-flex gap-1">
                    <select name="month" class="form-select form-select-sm" style="width:110px" onchange="this.form.submit()">
                        @foreach(range(1,12) as $m)
                            <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                            </option>
                        @endforeach
                    </select>
                    <select name="year" class="form-select form-select-sm" style="width:80px" onchange="this.form.submit()">
                        @foreach(range(now()->year + 1, now()->year - 2) as $y)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </form>

                <a href="{{ route('budgets.index', ['month' => $next->month, 'year' => $next->year]) }}"
                   class="btn btn-light btn-sm">
                    <i class="bi bi-chevron-right"></i>
                </a>

                {{-- Salin dari bulan lalu --}}
                <form method="POST" action="{{ route('budgets.copy') }}">
                    @csrf
                    <input type="hidden" name="month" value="{{ $month }}">
                    <input type="hidden" name="year"  value="{{ $year }}">
                    <button type="submit" class="btn btn-light btn-sm"
                            onclick="return confirm('Salin semua budget dari bulan sebelumnya?')"
                            title="Salin dari bulan lalu">
                        <i class="bi bi-copy me-1"></i> Salin bulan lalu
                    </button>
                </form>

                <a href="{{ route('budgets.create', ['month' => $month, 'year' => $year]) }}"
                   class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle me-1"></i> Tambah Budget
                </a>
            </div>
        </div>
    </div>

    {{-- Ringkasan atas --}}
    <section class="row mb-3">
        <div class="col-6 col-md-3">
            <div class="card">
                <div class="card-body py-3">
                    <p class="text-muted small mb-1">Total Budget</p>
                    <h5 class="fw-bold mb-0">Rp {{ number_format($totalBudget, 0, ',', '.') }}</h5>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card">
                <div class="card-body py-3">
                    <p class="text-muted small mb-1">Total Terpakai</p>
                    <h5 class="fw-bold mb-0 {{ $totalSpent > $totalBudget ? 'text-danger' : 'text-success' }}">
                        Rp {{ number_format($totalSpent, 0, ',', '.') }}
                    </h5>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card">
                <div class="card-body py-3">
                    <p class="text-muted small mb-1">Kategori Melebihi</p>
                    <h5 class="fw-bold mb-0 {{ $exceededCount > 0 ? 'text-danger' : 'text-success' }}">
                        {{ $exceededCount }} kategori
                    </h5>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card">
                <div class="card-body py-3">
                    <p class="text-muted small mb-1">Perlu Perhatian</p>
                    <h5 class="fw-bold mb-0 {{ $warningCount > 0 ? 'text-warning' : 'text-success' }}">
                        {{ $warningCount }} kategori
                    </h5>
                </div>
            </div>
        </div>
    </section>

    {{-- Daftar budget --}}
    @if ($budgets->isEmpty())
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-pie-chart fs-1 text-muted d-block mb-3"></i>
                <h5 class="text-muted">Belum ada budget untuk bulan ini</h5>
                <p class="text-muted small mb-4">
                    Buat budget per kategori untuk mengontrol pengeluaran Anda.
                </p>
                <div class="d-flex gap-2 justify-content-center">
                    <a href="{{ route('budgets.create', ['month' => $month, 'year' => $year]) }}"
                       class="btn btn-primary">
                        <i class="bi bi-plus-circle me-1"></i> Buat Budget Pertama
                    </a>
                    <form method="POST" action="{{ route('budgets.copy') }}">
                        @csrf
                        <input type="hidden" name="month" value="{{ $month }}">
                        <input type="hidden" name="year"  value="{{ $year }}">
                        <button type="submit" class="btn btn-light">
                            <i class="bi bi-copy me-1"></i> Salin dari Bulan Lalu
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @else
        <div class="row">
            @foreach ($budgets as $budget)
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card mb-3 {{ $budget->status === 'exceeded' ? 'border-danger' : '' }}">
                        <div class="card-body">
                            {{-- Header kategori --}}
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded p-2"
                                         style="background:{{ $budget->category->color }}20;width:36px;height:36px;display:flex;align-items:center;justify-content:center">
                                        <i class="{{ $budget->category->icon }}"
                                           style="color:{{ $budget->category->color }}"></i>
                                    </div>
                                    <div>
                                        <p class="fw-semibold mb-0 small">{{ $budget->category->name }}</p>
                                        <span class="badge bg-{{ $budget->statusColor() }} bg-opacity-10 text-{{ $budget->statusColor() }}" style="font-size:10px">
                                            {{ $budget->statusLabel() }}
                                        </span>
                                    </div>
                                </div>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item small" href="{{ route('budgets.edit', $budget) }}">
                                                <i class="bi bi-pencil me-2"></i> Edit
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item small"
                                               href="{{ route('transactions.index', ['category_id' => $budget->category_id, 'month' => $month, 'year' => $year]) }}">
                                                <i class="bi bi-list-ul me-2"></i> Lihat Transaksi
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form method="POST" action="{{ route('budgets.destroy', $budget) }}"
                                                  onsubmit="return confirm('Hapus budget ini?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="dropdown-item small text-danger">
                                                    <i class="bi bi-trash me-2"></i> Hapus
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            {{-- Angka --}}
                            <div class="d-flex justify-content-between align-items-baseline mb-1">
                                <span class="fw-bold">
                                    Rp {{ number_format($budget->spent_amount, 0, ',', '.') }}
                                </span>
                                <span class="text-muted small">
                                    dari Rp {{ number_format($budget->amount, 0, ',', '.') }}
                                </span>
                            </div>

                            {{-- Progress bar --}}
                            <div class="progress mb-2" style="height:8px;border-radius:99px">
                                <div class="progress-bar bg-{{ $budget->statusColor() }}"
                                     style="width:{{ min($budget->percentage, 100) }}%;border-radius:99px"
                                     role="progressbar"
                                     aria-valuenow="{{ $budget->percentage }}"
                                     aria-valuemin="0" aria-valuemax="100">
                                </div>
                            </div>

                            {{-- Persentase & sisa --}}
                            <div class="d-flex justify-content-between">
                                <span class="small text-{{ $budget->statusColor() }} fw-semibold">
                                    {{ $budget->percentage }}%
                                </span>
                                <span class="small text-muted">
                                    @if ($budget->status === 'exceeded')
                                        Lebih Rp {{ number_format($budget->spent_amount - $budget->amount, 0, ',', '.') }}
                                    @else
                                        Sisa Rp {{ number_format($budget->remaining(), 0, ',', '.') }}
                                    @endif
                                </span>
                            </div>

                            {{-- Alert threshold info --}}
                            <div class="mt-2 pt-2 border-top">
                                <small class="text-muted">
                                    <i class="bi bi-bell me-1"></i>
                                    Alert pada {{ $budget->alert_threshold }}%
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Kategori tanpa budget (saran) --}}
        @if ($unusedCategories->isNotEmpty())
            <div class="card mt-2">
                <div class="card-header">
                    <h6 class="mb-0 text-muted">
                        <i class="bi bi-lightbulb me-1"></i>
                        Kategori belum diatur budgetnya
                    </h6>
                </div>
                <div class="card-body py-2">
                    <div class="d-flex flex-wrap gap-2">
                        @foreach ($unusedCategories as $cat)
                            <a href="{{ route('budgets.create', ['month' => $month, 'year' => $year, 'category_id' => $cat->id]) }}"
                               class="btn btn-sm btn-outline-secondary">
                                <i class="{{ $cat->icon }} me-1" style="color:{{ $cat->color }}"></i>
                                {{ $cat->name }}
                                <i class="bi bi-plus ms-1"></i>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>
@endsection
