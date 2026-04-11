@extends('layouts.app')
@section('title', 'Tambah Transaksi Berulang')

@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6">
                <h3>Tambah Transaksi Berulang</h3>
                <p class="text-subtitle text-muted">Contoh: gaji, cicilan, tagihan bulanan</p>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-md-7">
            <div class="card">
                <div class="card-body">

                    @include('components.alert')

                    <form method="POST" action="{{ route('recurring.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Nama Rencana <span class="text-danger">*</span></label>
                            <input type="text" name="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}"
                                   placeholder="Contoh: Gaji PT Maju, Cicilan Motor, Netflix"
                                   required maxlength="100">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tipe <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="type"
                                           id="type_expense" value="expense"
                                           {{ old('type', 'expense') === 'expense' ? 'checked' : '' }}>
                                    <label class="form-check-label text-danger fw-semibold" for="type_expense">
                                        <i class="bi bi-arrow-down-circle me-1"></i> Pengeluaran
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="type"
                                           id="type_income" value="income"
                                           {{ old('type') === 'income' ? 'checked' : '' }}>
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
                                    <input type="number" name="amount"
                                           class="form-control @error('amount') is-invalid @enderror"
                                           value="{{ old('amount') }}" min="1" required
                                           placeholder="0">
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Frekuensi <span class="text-danger">*</span></label>
                                <select name="frequency" class="form-select @error('frequency') is-invalid @enderror" required>
                                    <option value="daily"   {{ old('frequency') === 'daily'   ? 'selected' : '' }}>Harian</option>
                                    <option value="weekly"  {{ old('frequency') === 'weekly'  ? 'selected' : '' }}>Mingguan</option>
                                    <option value="monthly" {{ old('frequency', 'monthly') === 'monthly' ? 'selected' : '' }}>Bulanan</option>
                                    <option value="yearly"  {{ old('frequency') === 'yearly'  ? 'selected' : '' }}>Tahunan</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Rekening <span class="text-danger">*</span></label>
                                <select name="account_id" class="form-select @error('account_id') is-invalid @enderror" required>
                                    <option value="">-- Pilih rekening --</option>
                                    @foreach ($accounts as $acc)
                                        <option value="{{ $acc->id }}" {{ old('account_id') == $acc->id ? 'selected' : '' }}>
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

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                                <input type="date" name="start_date"
                                       class="form-control @error('start_date') is-invalid @enderror"
                                       value="{{ old('start_date', now()->format('Y-m-d')) }}" required>
                                <div class="form-text">Tanggal pertama transaksi dibuat.</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal Berakhir</label>
                                <input type="date" name="ends_at"
                                       class="form-control @error('ends_at') is-invalid @enderror"
                                       value="{{ old('ends_at') }}">
                                <div class="form-text">Kosongkan jika tidak ada batas waktu.</div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Catatan</label>
                            <input type="text" name="note"
                                   class="form-control @error('note') is-invalid @enderror"
                                   value="{{ old('note') }}"
                                   placeholder="Keterangan tambahan (opsional)" maxlength="255">
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i> Simpan
                            </button>
                            <a href="{{ route('recurring.index') }}" class="btn btn-light">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Contoh use case --}}
        <div class="col-12 col-md-5">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Contoh Penggunaan</h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr><th>Nama</th><th>Tipe</th><th>Frekuensi</th></tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="small">Gaji bulanan</td>
                                <td><span class="badge bg-success-light text-success">Pemasukan</span></td>
                                <td class="small text-muted">Bulanan</td>
                            </tr>
                            <tr>
                                <td class="small">Tagihan listrik</td>
                                <td><span class="badge bg-danger-light text-danger">Pengeluaran</span></td>
                                <td class="small text-muted">Bulanan</td>
                            </tr>
                            <tr>
                                <td class="small">Cicilan KPR</td>
                                <td><span class="badge bg-danger-light text-danger">Pengeluaran</span></td>
                                <td class="small text-muted">Bulanan</td>
                            </tr>
                            <tr>
                                <td class="small">Netflix</td>
                                <td><span class="badge bg-danger-light text-danger">Pengeluaran</span></td>
                                <td class="small text-muted">Bulanan</td>
                            </tr>
                            <tr>
                                <td class="small">Asuransi jiwa</td>
                                <td><span class="badge bg-danger-light text-danger">Pengeluaran</span></td>
                                <td class="small text-muted">Tahunan</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
