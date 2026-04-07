@extends('layouts.app')
@section('title', 'Detail Transaksi')

@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6">
                <h3>Detail Transaksi</h3>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Informasi Transaksi</h5>
                    <span class="badge fs-6 {{ $transaction->type->value === 'income' ? 'bg-success' : ($transaction->type->value === 'expense' ? 'bg-danger' : 'bg-info') }}">
                        {{ $transaction->type->label() }}
                    </span>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td class="text-muted" width="150">Jumlah</td>
                            <td class="fw-bold fs-5 {{ $transaction->type->value === 'income' ? 'text-success' : ($transaction->type->value === 'expense' ? 'text-danger' : 'text-info') }}">
                                {{ $transaction->formatted_amount }}
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Tanggal</td>
                            <td>{{ $transaction->date->translatedFormat('l, d F Y') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Rekening</td>
                            <td>{{ $transaction->account->name }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Kategori</td>
                            <td>
                                @if ($transaction->category)
                                    <span class="badge rounded-pill"
                                          style="background:{{ $transaction->category->color }}20;color:{{ $transaction->category->color }}">
                                        {{ $transaction->category->name }}
                                    </span>
                                @else
                                    <span class="text-muted">Tanpa kategori</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Keterangan</td>
                            <td>{{ $transaction->note ?? '-' }}</td>
                        </tr>
                        @if ($transaction->transferPair)
                            <tr>
                                <td class="text-muted">Transfer ke/dari</td>
                                <td>{{ $transaction->transferPair->account->name }}</td>
                            </tr>
                        @endif
                        <tr>
                            <td class="text-muted">Dicatat</td>
                            <td class="text-muted small">{{ $transaction->created_at->diffForHumans() }}</td>
                        </tr>
                    </table>

                    @if ($transaction->receipt_path)
                        <div class="mt-3">
                            <p class="text-muted small mb-1">Bukti transaksi:</p>
                            <a href="{{ url('/receipt/'.$transaction->receipt_path) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-paperclip me-1"></i> Lihat Bukti
                            </a>
                        </div>
                    @endif
                </div>
                <div class="card-footer d-flex gap-2">
                    <a href="{{ route('transactions.edit', $transaction) }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-pencil me-1"></i> Edit
                    </a>
                    <form method="POST" action="{{ route('transactions.destroy', $transaction) }}"
                          onsubmit="return confirm('Yakin hapus transaksi ini?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i class="bi bi-trash me-1"></i> Hapus
                        </button>
                    </form>
                    <a href="{{ route('transactions.index') }}" class="btn btn-light btn-sm ms-auto">
                        <i class="bi bi-arrow-left me-1"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
