@extends('layouts.app')
@section('title', 'Edit Transaksi Berulang')

@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6">
                <h3>Edit Transaksi Berulang</h3>
                <p class="text-subtitle text-muted">Perbarui rencana "{{ $recurring->name }}"</p>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-md-7">
            <div class="card">
                <div class="card-body">

                    @include('components.alert')

                    <form method="POST" action="{{ route('recurring.update', $recurring) }}">
                        @csrf @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Nama Rencana <span class="text-danger">*</span></label>
                            <input type="text" name="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $recurring->name) }}"
                                   required maxlength="100">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tipe <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="type"
                                           id="type_expense" value="expense"
                                           {{ old('type', $recurring->type->value) === 'expense' ? 'checked' : '' }}>
                                    <label class="form-check-label text-danger fw-semibold" for="type_expense">
                                        <i class="bi bi-arrow-down-circle me-1"></i> Pengeluaran
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="type"
                                           id="type_income" value="income"
                                           {{ old('type', $recurring->type->value) === 'income' ? 'checked' : '' }}>
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
                                           value="{{ old('amount', $recurring->amount) }}"
                                           min="1" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Frekuensi <span class="text-danger">*</span></label>
                                <select name="frequency" class="form-select @error('frequency') is-invalid @enderror" required>
                                    <option value="daily"   {{ old('frequency', $recurring->frequency->value) === 'daily'   ? 'selected' : '' }}>Harian</option>
                                    <option value="weekly"  {{ old('frequency', $recurring->frequency->value) === 'weekly'  ? 'selected' : '' }}>Mingguan</option>
                                    <option value="monthly" {{ old('frequency', $recurring->frequency->value) === 'monthly' ? 'selected' : '' }}>Bulanan</option>
                                    <option value="yearly"  {{ old('frequency', $recurring->frequency->value) === 'yearly'  ? 'selected' : '' }}>Tahunan</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Rekening <span class="text-danger">*</span></label>
                                <select name="account_id" class="form-select @error('account_id') is-invalid @enderror" required>
                                    @foreach ($accounts as $acc)
                                        <option value="{{ $acc->id }}"
                                            {{ old('account_id', $recurring->account_id) == $acc->id ? 'selected' : '' }}>
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
                                                {{ old('category_id', $recurring->category_id) == $parent->id ? 'selected' : '' }}>
                                                {{ $parent->name }}
                                            </option>
                                            @foreach ($parent->children as $child)
                                                <option value="{{ $child->id }}"
                                                    {{ old('category_id', $recurring->category_id) == $child->id ? 'selected' : '' }}>
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
                                <label class="form-label">Tanggal Berakhir</label>
                                <input type="date" name="ends_at"
                                       class="form-control @error('ends_at') is-invalid @enderror"
                                       value="{{ old('ends_at', $recurring->ends_at?->format('Y-m-d')) }}">
                                <div class="form-text">Kosongkan jika tidak ada batas waktu.</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" name="is_active"
                                           id="is_active" value="1"
                                           {{ old('is_active', $recurring->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">Aktif</label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Catatan</label>
                            <input type="text" name="note"
                                   class="form-control @error('note') is-invalid @enderror"
                                   value="{{ old('note', $recurring->note) }}" maxlength="255">
                        </div>

                        <div class="alert alert-info small">
                            <i class="bi bi-info-circle me-1"></i>
                            Eksekusi berikutnya: <strong>{{ $recurring->next_run_at->translatedFormat('d F Y') }}</strong>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i> Perbarui
                            </button>
                            <a href="{{ route('recurring.index') }}" class="btn btn-light">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
