@extends('layouts.app')
@section('title', 'Tambah Transaksi')

@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6">
                <h3>Tambah Transaksi</h3>
                <p class="text-subtitle text-muted">Catat pemasukan atau pengeluaran baru</p>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-md-8">
            <div class="card">
                <div class="card-body">

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            @foreach ($errors->all() as $error)
                                <p class="mb-0">{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('transactions.store') }}" enctype="multipart/form-data">
                        @csrf

                        {{-- Tipe --}}
                        <div class="mb-3">
                            <label class="form-label">Tipe Transaksi <span class="text-danger">*</span></label>
                            <div class="d-flex gap-2">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="type" id="type_expense"
                                           value="expense" {{ old('type', 'expense') === 'expense' ? 'checked' : '' }}>
                                    <label class="form-check-label text-danger fw-semibold" for="type_expense">
                                        <i class="bi bi-arrow-down-circle me-1"></i> Pengeluaran
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="type" id="type_income"
                                           value="income" {{ old('type') === 'income' ? 'checked' : '' }}>
                                    <label class="form-check-label text-success fw-semibold" for="type_income">
                                        <i class="bi bi-arrow-up-circle me-1"></i> Pemasukan
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            {{-- Jumlah --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Jumlah <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="amount" class="form-control @error('amount') is-invalid @enderror"
                                           value="{{ old('amount') }}" placeholder="0" min="1" required>
                                </div>
                            </div>

                            {{-- Tanggal --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                                <input type="date" name="date" class="form-control @error('date') is-invalid @enderror"
                                       value="{{ old('date', now()->format('Y-m-d')) }}" max="{{ now()->format('Y-m-d') }}" required>
                            </div>
                        </div>

                        <div class="row">
                            {{-- Rekening --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Rekening <span class="text-danger">*</span></label>
                                <select name="account_id" class="form-select @error('account_id') is-invalid @enderror" required>
                                    <option value="">-- Pilih rekening --</option>
                                    @foreach ($accounts as $acc)
                                        <option value="{{ $acc->id }}" {{ old('account_id') == $acc->id ? 'selected' : '' }}>
                                            {{ $acc->name }} (Rp {{ number_format($acc->balance, 0, ',', '.') }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Kategori --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kategori</label>
                                <select name="category_id" class="form-select @error('category_id') is-invalid @enderror">
                                    <option value="">-- Tanpa kategori --</option>
                                    @foreach ($categories as $parent)
                                        <optgroup label="{{ $parent->name }}">
                                            <option value="{{ $parent->id }}" {{ old('category_id') == $parent->id ? 'selected' : '' }}>
                                                {{ $parent->name }}
                                            </option>
                                            @foreach ($parent->children as $child)
                                                <option value="{{ $child->id }}" {{ old('category_id') == $child->id ? 'selected' : '' }}>
                                                    &nbsp;&nbsp; {{ $child->name }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Keterangan --}}
                        <div class="mb-3">
                            <label class="form-label">Keterangan</label>
                            <input type="text" name="note" class="form-control @error('note') is-invalid @enderror"
                                   value="{{ old('note') }}" placeholder="Contoh: Makan siang di warung bu Sari" maxlength="500">
                        </div>

                        {{-- Bukti --}}
                        <div class="mb-4">
                            <label class="form-label">Bukti Transaksi</label>
                            <input type="file" name="receipt" class="form-control @error('receipt') is-invalid @enderror"
                                   accept=".jpg,.jpeg,.png,.pdf">
                            <div class="form-text">JPG, PNG, atau PDF. Maks 5MB.</div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i> Simpan Transaksi
                            </button>
                            <a href="{{ route('transactions.index') }}" class="btn btn-light">Batal</a>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
