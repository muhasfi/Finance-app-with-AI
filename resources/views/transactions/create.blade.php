@extends('layouts.app')
@section('title', 'Tambah Transaksi')

@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Tambah Transaksi</h3>
                <p class="text-subtitle text-muted">Catat pemasukan atau pengeluaran baru dengan mudah.</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('transactions.index') }}">Transaksi</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Tambah</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <section id="multiple-column-form">
        <div class="row match-height">
            <div class="col-12"> {{-- Melebarkan sedikit agar lebih pas di desktop --}}
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Formulir Transaksi</h4>
                    </div>
                    <div class="card-content">
                        <div class="card-body">
                            {{-- Alert Error --}}
                            @if ($errors->any())
                                <div class="alert alert-light-danger color-danger alert-dismissible show fade">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            <form class="form" method="POST" action="{{ route('transactions.store') }}" enctype="multipart/form-data">
                                @csrf

                                <div class="row">
                                    {{-- Tipe Transaksi - Tampilan Lebih Modern --}}
                                    <div class="col-12 mb-4">
                                        <label class="form-label fw-bold">Tipe Transaksi <span class="text-danger">*</span></label>
                                        <div class="d-flex gap-4 mt-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="type" id="type_expense"
                                                       value="expense" {{ old('type', 'expense') === 'expense' ? 'checked' : '' }}>
                                                <label class="form-check-label text-danger" for="type_expense">
                                                    <i class="bi bi-dash-circle-fill me-1"></i> Pengeluaran
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="type" id="type_income"
                                                       value="income" {{ old('type') === 'income' ? 'checked' : '' }}>
                                                <label class="form-check-label text-success" for="type_income">
                                                    <i class="bi bi-plus-circle-fill me-1"></i> Pemasukan
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Baris 1: Jumlah & Tanggal --}}
                                    <div class="col-md-6 col-12">
                                        <div class="form-group mb-3">
                                            <label class="form-label" for="amount">Jumlah <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-primary text-white">Rp</span>
                                                <input type="number" id="amount" name="amount" class="form-control @error('amount') is-invalid @enderror"
                                                       value="{{ old('amount') }}" placeholder="0" min="1" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6 col-12">
                                        <div class="form-group mb-3">
                                            <label class="form-label" for="date">Tanggal <span class="text-danger">*</span></label>
                                            <input type="date" id="date" name="date" class="form-control @error('date') is-invalid @enderror"
                                                   value="{{ old('date', now()->format('Y-m-d')) }}" max="{{ now()->format('Y-m-d') }}" required>
                                        </div>
                                    </div>

                                    {{-- Baris 2: Rekening & Kategori --}}
                                    <div class="col-md-6 col-12">
                                        <div class="form-group mb-3">
                                            <label class="form-label">Rekening <span class="text-danger">*</span></label>
                                            <select name="account_id" class="form-select @error('account_id') is-invalid @enderror" required>
                                                <option value="" selected disabled>-- Pilih rekening --</option>
                                                @foreach ($accounts as $acc)
                                                    <option value="{{ $acc->id }}" {{ old('account_id') == $acc->id ? 'selected' : '' }}>
                                                        {{ $acc->name }} (Rp {{ number_format($acc->balance, 0, ',', '.') }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-6 col-12">
                                        <div class="form-group mb-3">
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

                                    {{-- Baris 3: Keterangan (Full) --}}
                                    <div class="col-12">
                                        <div class="form-group mb-3">
                                            <label class="form-label">Keterangan</label>
                                            <textarea name="note" class="form-control @error('note') is-invalid @enderror" 
                                                      placeholder="Contoh: Makan siang di warung bu Sari" rows="2">{{ old('note') }}</textarea>
                                        </div>
                                    </div>

                                    {{-- Baris 4: Bukti Transaksi --}}
                                    <div class="col-12">
                                        <div class="form-group mb-4">
                                            <label class="form-label">Bukti Transaksi</label>
                                            <input type="file" name="receipt" class="form-control @error('receipt') is-invalid @enderror"
                                                   accept=".jpg,.jpeg,.png,.pdf">
                                            <div class="form-text text-muted">Format: JPG, PNG, atau PDF. Maksimal 5MB.</div>
                                        </div>
                                    </div>

                                    {{-- Tombol Aksi --}}
                                    <div class="col-12 d-flex justify-content-end mt-4 gap-2">
                                        <a href="{{ route('transactions.index') }}" class="btn btn-light-secondary px-4">Batal</a>
                                        <button type="submit" class="btn btn-primary px-4">
                                            <i class="bi bi-check-circle me-1"></i> Simpan Transaksi
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection