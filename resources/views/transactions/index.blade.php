@extends('layouts.app')
@section('title', 'Transaksi')

@section('content')
<div class="page-heading">
    <div class="page-title mb-4">
        <div class="row">
            <div class="col-12 col-md-6">
                <h3>Transaksi</h3>
                <p class="text-subtitle text-muted">Riwayat semua transaksi keuangan Anda</p>
            </div>
            <div class="col-12 col-md-6 d-flex justify-content-end align-items-center mt-2 mt-md-0">
                <a href="{{ route('transactions.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i> Tambah Transaksi
                </a>
            </div>
        </div>
    </div>

    {{-- Filter --}}
    <div class="card mb-3">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('transactions.index') }}" class="row g-2 align-items-end">
                <div class="col-6 col-md-2">
                    <label class="form-label small text-muted mb-1">Bulan</label>
                    <select name="month" class="form-select form-select-sm">
                        @foreach (range(1, 12) as $m)
                            <option value="{{ $m }}" {{ request('month', now()->month) == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small text-muted mb-1">Tahun</label>
                    <select name="year" class="form-select form-select-sm">
                        @foreach (range(now()->year, now()->year - 3) as $y)
                            <option value="{{ $y }}" {{ request('year', now()->year) == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small text-muted mb-1">Tipe</label>
                    <select name="type" class="form-select form-select-sm">
                        <option value="">Semua</option>
                        <option value="income"   {{ request('type') === 'income'   ? 'selected' : '' }}>Pemasukan</option>
                        <option value="expense"  {{ request('type') === 'expense'  ? 'selected' : '' }}>Pengeluaran</option>
                        <option value="transfer" {{ request('type') === 'transfer' ? 'selected' : '' }}>Transfer</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small text-muted mb-1">Rekening</label>
                    <select name="account_id" class="form-select form-select-sm">
                        <option value="">Semua</option>
                        @foreach ($accounts as $acc)
                            <option value="{{ $acc->id }}" {{ request('account_id') == $acc->id ? 'selected' : '' }}>{{ $acc->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small text-muted mb-1">Kategori</label>
                    <select name="category_id" class="form-select form-select-sm">
                        <option value="">Semua</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-sm btn-primary flex-fill">Filter</button>
                    <a href="{{ route('transactions.index') }}" class="btn btn-sm btn-light">Reset</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabel --}}
    <div class="card">
        <div class="card-body">
            {{-- FIX UTAMA: tambah table-responsive --}}
            <div class="table-responsive">
                <table class="table table-hover table-striped" id="table1">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Keterangan</th>
                            <th>Kategori</th>
                            <th>Rekening</th>
                            <th>Tipe</th>
                            <th class="text-end">Jumlah</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transactions as $tx)
                            <tr>
                                <td class="text-muted small">{{ $tx->date->format('d M Y') }}</td>
                                <td>
                                    <span class="fw-semibold">{{ $tx->note ?? '-' }}</span>
                                    @if ($tx->receipt_path)
                                        <i class="bi bi-paperclip text-muted ms-1" title="Ada bukti"></i>
                                    @endif
                                </td>
                                <td>
                                    @if ($tx->category)
                                        <span class="badge rounded-pill"
                                              style="background:{{ $tx->category->color }}20;color:{{ $tx->category->color }}">
                                            {{ $tx->category->name }}
                                        </span>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td class="small">{{ $tx->account->name }}</td>
                                <td>
                                    @if ($tx->type->value === 'income')
                                        <span class="badge bg-light-success text-success">Pemasukan</span>
                                    @elseif ($tx->type->value === 'expense')
                                        <span class="badge bg-light-danger text-danger">Pengeluaran</span>
                                    @else
                                        <span class="badge bg-light-info text-info">Transfer</span>
                                    @endif
                                </td>
                                <td class="text-end fw-bold {{ $tx->type->value === 'income' ? 'text-success' : ($tx->type->value === 'expense' ? 'text-danger' : 'text-info') }}">
                                    {{ $tx->formatted_amount }}
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        <a href="{{ route('transactions.show', $tx) }}" class="btn btn-sm btn-light" title="Detail">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('transactions.edit', $tx) }}" class="btn btn-sm btn-light" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form method="POST" action="{{ route('transactions.destroy', $tx) }}"
                                              onsubmit="return confirm('Yakin hapus transaksi ini?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-light text-danger" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    Tidak ada transaksi ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{-- END table-responsive --}}
        </div>
    </div>
</div>
@endsection