@extends('layouts.app')
@section('title', 'Detail Transaksi')

@section('content')
<div class="page-heading">
    <div class="page-title mb-3">
        <h3>Detail Transaksi</h3>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                
                {{-- HEADER --}}
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Informasi Transaksi</h5>

                    <span class="badge fs-6 px-3 py-2 
                        {{ $transaction->type->value === 'income' ? 'bg-success' : ($transaction->type->value === 'expense' ? 'bg-danger' : 'bg-info') }}">
                        {{ $transaction->type->label() }}
                    </span>
                </div>

                <div class="card-body">

                    {{-- JUMLAH --}}
                    <div class="mb-4 text-center">
                        <div class="text-muted small">Jumlah</div>
                        <div class="fw-bold fs-2 
                            {{ $transaction->type->value === 'income' ? 'text-success' : ($transaction->type->value === 'expense' ? 'text-danger' : 'text-info') }}">
                            {{ $transaction->formatted_amount }}
                        </div>
                    </div>

                    {{-- INFO GRID --}}
                    <div class="row g-3">

                        <div class="col-12 col-md-6">
                            <div class="text-muted small">Tanggal</div>
                            <div class="fw-semibold">
                                {{ $transaction->date->translatedFormat('l, d F Y') }}
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="text-muted small">Rekening</div>
                            <div class="fw-semibold">
                                {{ $transaction->account->name }}
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="text-muted small">Kategori</div>
                            <div>
                                @if ($transaction->category)
                                    <span class="badge rounded-pill"
                                          style="background:{{ $transaction->category->color }}20;color:{{ $transaction->category->color }}">
                                        {{ $transaction->category->name }}
                                    </span>
                                @else
                                    <span class="text-muted">Tanpa kategori</span>
                                @endif
                            </div>
                        </div>

                        @if ($transaction->transferPair)
                        <div class="col-12 col-md-6">
                            <div class="text-muted small">Transfer</div>
                            <div class="fw-semibold">
                                {{ $transaction->transferPair->account->name }}
                            </div>
                        </div>
                        @endif

                        <div class="col-12">
                            <div class="text-muted small">Keterangan</div>
                            <div class="fw-semibold">
                                {{ $transaction->note ?? '-' }}
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="text-muted small">Dicatat</div>
                            <div class="text-muted small">
                                {{ $transaction->created_at->diffForHumans() }}
                            </div>
                        </div>

                    </div>

                    {{-- RECEIPT --}}
                    @if ($transaction->receipt_path)
                        <div class="mt-4 text-center">
                            <a href="{{ url('/receipt/'.$transaction->receipt_path) }}" 
                               target="_blank" 
                               class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-paperclip me-1"></i> Lihat Bukti
                            </a>
                        </div>
                    @endif

                </div>

                {{-- FOOTER --}}
                <div class="card-footer d-flex flex-wrap gap-2">

                    <a href="{{ route('transactions.edit', $transaction) }}" 
                       class="btn btn-primary btn-sm">
                        <i class="bi bi-pencil me-1"></i> Edit
                    </a>

                    <form method="POST" action="{{ route('transactions.destroy', $transaction) }}"
                          onsubmit="return confirm('Yakin hapus transaksi ini?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i class="bi bi-trash me-1"></i> Hapus
                        </button>
                    </form>

                    <a href="{{ route('transactions.index') }}" 
                       class="btn btn-light btn-sm ms-auto">
                        <i class="bi bi-arrow-left me-1"></i> Kembali
                    </a>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection