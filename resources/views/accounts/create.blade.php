@extends('layouts.app')
@section('title', 'Tambah Rekening')

@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6">
                <h3>Tambah Rekening</h3>
                <p class="text-subtitle text-muted">Daftarkan rekening bank, e-wallet, atau kas</p>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-md-6">
            <div class="card">
                <div class="card-body">

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            @foreach ($errors->all() as $error)
                                <p class="mb-0">{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('accounts.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Nama Rekening <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" placeholder="Contoh: BCA Tabungan" required maxlength="100">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tipe <span class="text-danger">*</span></label>
                                <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                                    <option value="">-- Pilih tipe --</option>
                                    @foreach ($types as $type)
                                        <option value="{{ $type->value }}" {{ old('type') == $type->value ? 'selected' : '' }}>
                                            {{ $type->label() }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Mata Uang <span class="text-danger">*</span></label>
                                <select name="currency" class="form-select @error('currency') is-invalid @enderror" required>
                                    <option value="IDR" {{ old('currency', 'IDR') === 'IDR' ? 'selected' : '' }}>IDR — Rupiah</option>
                                    <option value="USD" {{ old('currency') === 'USD' ? 'selected' : '' }}>USD — Dollar</option>
                                    <option value="SGD" {{ old('currency') === 'SGD' ? 'selected' : '' }}>SGD — Singapore Dollar</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Saldo Awal <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="balance" class="form-control @error('balance') is-invalid @enderror"
                                       value="{{ old('balance', 0) }}" min="0" required>
                            </div>
                            <div class="form-text">Isi saldo saat ini dari rekening ini.</div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Warna</label>
                                <input type="color" name="color" class="form-control form-control-color"
                                       value="{{ old('color', '#6366f1') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Icon <small class="text-muted">(Bootstrap Icons)</small></label>
                                <input type="text" name="icon" class="form-control @error('icon') is-invalid @enderror"
                                       value="{{ old('icon', 'bi-wallet2') }}" placeholder="bi-wallet2">
                                <div class="form-text">Contoh: bi-bank, bi-phone, bi-cash</div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="description" class="form-control" rows="2"
                                      placeholder="Keterangan tambahan (opsional)" maxlength="255">{{ old('description') }}</textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i> Simpan
                            </button>
                            <a href="{{ route('accounts.index') }}" class="btn btn-light">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
