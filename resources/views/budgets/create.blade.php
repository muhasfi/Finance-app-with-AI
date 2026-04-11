@extends('layouts.app')
@section('title', 'Tambah Budget')

@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6">
                <h3>Tambah Budget</h3>
                <p class="text-subtitle text-muted">
                    Untuk {{ \Carbon\Carbon::createFromDate($year, $month, 1)->translatedFormat('F Y') }}
                </p>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-md-6">
            <div class="card">
                <div class="card-body">
                    @include('components.alert')

                    <form method="POST" action="{{ route('budgets.store') }}">
                        @csrf
                        <input type="hidden" name="month" value="{{ $month }}">
                        <input type="hidden" name="year"  value="{{ $year }}">

                        <div class="mb-3">
                            <label class="form-label">Kategori <span class="text-danger">*</span></label>
                            <select name="category_id" id="categorySelect"
                                    class="form-select @error('category_id') is-invalid @enderror" required>
                                <option value="">-- Pilih kategori --</option>
                                @foreach ($categories as $parent)
                                    <optgroup label="{{ $parent->name }}">
                                        <option value="{{ $parent->id }}"
                                                data-color="{{ $parent->color }}"
                                                {{ (old('category_id', request('category_id')) == $parent->id) ? 'selected' : '' }}>
                                            {{ $parent->name }}
                                        </option>
                                        @foreach ($parent->children as $child)
                                            <option value="{{ $child->id }}"
                                                    data-color="{{ $child->color }}"
                                                    {{ (old('category_id', request('category_id')) == $child->id) ? 'selected' : '' }}>
                                                &nbsp;&nbsp; {{ $child->name }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            @error('category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @if ($categories->isEmpty())
                                <div class="form-text text-warning">
                                    Semua kategori pengeluaran sudah memiliki budget bulan ini.
                                </div>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Batas Anggaran <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="amount"
                                       class="form-control @error('amount') is-invalid @enderror"
                                       value="{{ old('amount') }}"
                                       placeholder="0" min="1000" required>
                            </div>
                            @error('amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label d-flex justify-content-between">
                                <span>Alert pada <span id="thresholdVal" class="text-primary fw-semibold">80</span>%</span>
                                <small class="text-muted">Notifikasi saat pengeluaran mencapai persentase ini</small>
                            </label>
                            <input type="range" name="alert_threshold" id="alertThreshold"
                                   class="form-range" min="50" max="100" step="5"
                                   value="{{ old('alert_threshold', 80) }}"
                                   oninput="document.getElementById('thresholdVal').textContent = this.value">
                            <div class="d-flex justify-content-between">
                                <small class="text-muted">50%</small>
                                <small class="text-muted">100%</small>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary"
                                    {{ $categories->isEmpty() ? 'disabled' : '' }}>
                                <i class="bi bi-save me-1"></i> Simpan Budget
                            </button>
                            <a href="{{ route('budgets.index', ['month' => $month, 'year' => $year]) }}"
                               class="btn btn-light">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Info panduan --}}
        <div class="col-12 col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Tips Mengatur Budget</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex gap-2 mb-3">
                            <i class="bi bi-check-circle text-success flex-shrink-0 mt-1"></i>
                            <div class="small">
                                <strong>Aturan 50/30/20</strong> — 50% untuk kebutuhan (makan, transport, tagihan),
                                30% untuk keinginan (hiburan, belanja), 20% untuk tabungan.
                            </div>
                        </li>
                        <li class="d-flex gap-2 mb-3">
                            <i class="bi bi-check-circle text-success flex-shrink-0 mt-1"></i>
                            <div class="small">
                                <strong>Alert 80%</strong> — Setting default 80% memberi waktu sebelum anggaran habis.
                                Naikkan ke 90% jika ingin lebih fleksibel.
                            </div>
                        </li>
                        <li class="d-flex gap-2">
                            <i class="bi bi-check-circle text-success flex-shrink-0 mt-1"></i>
                            <div class="small">
                                <strong>Salin dari bulan lalu</strong> — Bulan berikutnya bisa langsung salin semua
                                budget bulan ini tanpa perlu input ulang satu per satu.
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
