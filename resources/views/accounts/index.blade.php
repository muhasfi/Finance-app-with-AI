@extends('layouts.app')
@section('title', 'Rekening')

@section('content')
<div class="page-heading">
    <div class="page-title mb-4">
        <div class="row">
            <div class="col-12 col-md-6">
                <h3>Rekening</h3>
                <p class="text-subtitle text-muted">Kelola semua rekening dan dompet Anda</p>
            </div>
            <div class="col-12 col-md-6 d-flex justify-content-end align-items-center">
                <a href="{{ route('accounts.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i> Tambah Rekening
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        @forelse ($accounts as $account)
            <div class="col-12 col-md-4 col-lg-3">
                <div class="card {{ $account->trashed() ? 'opacity-50' : '' }}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="avatar rounded p-2" style="background:{{ $account->color }}20">
                                <i class="{{ $account->icon }} fs-4" style="color:{{ $account->color }}"></i>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light" data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    @unless ($account->trashed())
                                        <li>
                                            <a class="dropdown-item" href="{{ route('accounts.edit', $account) }}">
                                                <i class="bi bi-pencil me-2"></i> Edit
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('transactions.index', ['account_id' => $account->id]) }}">
                                                <i class="bi bi-list-ul me-2"></i> Lihat Transaksi
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form method="POST" action="{{ route('accounts.destroy', $account) }}"
                                                  onsubmit="return confirm('Yakin hapus rekening ini?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="bi bi-trash me-2"></i> Hapus
                                                </button>
                                            </form>
                                        </li>
                                    @endunless
                                </ul>
                            </div>
                        </div>

                        <h6 class="fw-bold mb-0">{{ $account->name }}</h6>
                        <small class="text-muted">{{ $account->type->label() }}</small>

                        <div class="mt-3">
                            <p class="text-muted small mb-0">Saldo</p>
                            <h5 class="fw-bold {{ $account->balance >= 0 ? 'text-success' : 'text-danger' }} mb-0">
                                Rp {{ number_format($account->balance, 0, ',', '.') }}
                            </h5>
                        </div>

                        @if (! $account->is_active)
                            <span class="badge bg-secondary mt-2">Nonaktif</span>
                        @endif
                        @if ($account->trashed())
                            <span class="badge bg-danger mt-2">Dihapus</span>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-wallet2 fs-1 text-muted d-block mb-3"></i>
                        <h5 class="text-muted">Belum ada rekening</h5>
                        <a href="{{ route('accounts.create') }}" class="btn btn-primary mt-2">Tambah Rekening Pertama</a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection
