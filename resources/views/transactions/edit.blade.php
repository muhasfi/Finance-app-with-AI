@extends('layouts.app')
@section('title', 'Edit Transaksi')

@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6">
                <h3>Edit Transaksi</h3>
                <p class="text-subtitle text-muted">Perbarui detail transaksi</p>
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

                    <form method="POST" action="{{ route('transactions.update', $transaction) }}" enctype="multipart/form-data">
                        @csrf @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Tipe Transaksi <span class="text-danger">*</span></label>
                            <div class="d-flex gap-2">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="type" id="type_expense"
                                           value="expense" {{ old('type', $transaction->type->value) === 'expense' ? 'checked' : '' }}>
                                    <label class="form-check-label text-danger fw-semibold" for="type_expense">
                                        <i class="bi bi-arrow-down-circle me-1"></i> Pengeluaran
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="type" id="type_income"
                                           value="income" {{ old('type', $transaction->type->value) === 'income' ? 'checked' : '' }}>
                                    <label class="form-check-label text-success fw-semibold" for="type_income">
                                        <i class="bi bi-arrow-up-circle me-1"></i> Pemasukan
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Jumlah <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="amount" class="form-control @error('amount') is-invalid @enderror"
                                           value="{{ old('amount', $transaction->amount) }}" min="1" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                                <input type="date" name="date" class="form-control @error('date') is-invalid @enderror"
                                       value="{{ old('date', $transaction->date->format('Y-m-d')) }}"
                                       max="{{ now()->format('Y-m-d') }}" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Rekening <span class="text-danger">*</span></label>
                                <select name="account_id" class="form-select @error('account_id') is-invalid @enderror" required>
                                    @foreach ($accounts as $acc)
                                        <option value="{{ $acc->id }}"
                                            {{ old('account_id', $transaction->account_id) == $acc->id ? 'selected' : '' }}>
                                            {{ $acc->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kategori</label>
                                <select name="category_id" class="form-select @error('category_id') is-invalid @enderror">
                                    <option value="">-- Tanpa kategori --</option>
                                    @foreach ($categories as $parent)
                                        <optgroup label="{{ $parent->name }}">
                                            <option value="{{ $parent->id }}"
                                                {{ old('category_id', $transaction->category_id) == $parent->id ? 'selected' : '' }}>
                                                {{ $parent->name }}
                                            </option>
                                            @foreach ($parent->children as $child)
                                                <option value="{{ $child->id }}"
                                                    {{ old('category_id', $transaction->category_id) == $child->id ? 'selected' : '' }}>
                                                    &nbsp;&nbsp; {{ $child->name }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Keterangan</label>
                            <input type="text" name="note" class="form-control @error('note') is-invalid @enderror"
                                   value="{{ old('note', $transaction->note) }}" maxlength="500">
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Bukti Transaksi</label>
                            @if ($transaction->receipt_path)
                                <div class="mb-2">
                                    <small class="text-muted">
                                        <i class="bi bi-paperclip me-1"></i>
                                        Sudah ada bukti. Upload baru untuk mengganti.
                                    </small>
                                </div>
                            @endif
                            <input type="file" name="receipt" class="form-control @error('receipt') is-invalid @enderror"
                                   accept=".jpg,.jpeg,.png,.pdf">
                            <div class="form-text">JPG, PNG, atau PDF. Maks 5MB.</div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i> Perbarui
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
