@extends('layouts.app')
@section('title', 'Edit Budget')

@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6">
                <h3>Edit Budget</h3>
                <p class="text-subtitle text-muted">
                    {{ $budget->category->name }} —
                    {{ \Carbon\Carbon::createFromDate($budget->year, $budget->month, 1)->translatedFormat('F Y') }}
                </p>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-md-6">
            <div class="card">
                <div class="card-body">
                    @include('components.alert')

                    {{-- Status sekarang --}}
                    @php
                        $spent      = $budget->spent();
                        $percentage = $budget->percentage();
                    @endphp
                    <div class="alert alert-light border mb-4">
                        <p class="small mb-2">
                            <strong>Status saat ini:</strong>
                            Terpakai Rp {{ number_format($spent, 0, ',', '.') }}
                            dari Rp {{ number_format($budget->amount, 0, ',', '.') }}
                            ({{ $percentage }}%)
                        </p>
                        <div class="progress" style="height:6px">
                            <div class="progress-bar bg-{{ $budget->statusColor() }}"
                                 style="width:{{ min($percentage, 100) }}%"></div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('budgets.update', $budget) }}">
                        @csrf @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Kategori</label>
                            <input type="text" class="form-control" value="{{ $budget->category->name }}" disabled>
                            <div class="form-text text-muted">Kategori tidak bisa diubah. Hapus dan buat budget baru jika perlu.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Batas Anggaran <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="amount"
                                       class="form-control @error('amount') is-invalid @enderror"
                                       value="{{ old('amount', $budget->amount) }}"
                                       min="1000" required>
                            </div>
                            @error('amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label d-flex justify-content-between">
                                <span>Alert pada <span id="thresholdVal" class="text-primary fw-semibold">{{ old('alert_threshold', $budget->alert_threshold) }}</span>%</span>
                            </label>
                            <input type="range" name="alert_threshold" id="alertThreshold"
                                   class="form-range" min="50" max="100" step="5"
                                   value="{{ old('alert_threshold', $budget->alert_threshold) }}"
                                   oninput="document.getElementById('thresholdVal').textContent = this.value">
                            <div class="d-flex justify-content-between">
                                <small class="text-muted">50%</small>
                                <small class="text-muted">100%</small>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active"
                                       id="is_active" value="1"
                                       {{ old('is_active', $budget->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Budget aktif</label>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i> Perbarui
                            </button>
                            <a href="{{ route('budgets.index', ['month' => $budget->month, 'year' => $budget->year]) }}"
                               class="btn btn-light">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
