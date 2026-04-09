@extends('layouts.app')
@section('title', 'Import Transaksi')

@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6">
                <h3>Import Transaksi</h3>
                <p class="text-subtitle text-muted">Upload file CSV mutasi rekening dari m-banking</p>
            </div>
        </div>
    </div>

    @include('components.alert')

    <div class="row">
        <div class="col-12 col-md-6">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('import.upload') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Rekening Tujuan <span class="text-danger">*</span></label>
                            <select name="account_id" class="form-select @error('account_id') is-invalid @enderror" required>
                                <option value="">-- Pilih rekening --</option>
                                @foreach ($accounts as $acc)
                                    <option value="{{ $acc->id }}" {{ old('account_id') == $acc->id ? 'selected' : '' }}>
                                        {{ $acc->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">Transaksi yang diimport akan masuk ke rekening ini.</div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">File CSV <span class="text-danger">*</span></label>
                            <input type="file" name="file"
                                   class="form-control @error('file') is-invalid @enderror"
                                   accept=".csv,.txt" required>
                            <div class="form-text">Format: CSV, maks 2MB. Export dari m-banking atau internet banking bank Anda.</div>
                            @error('file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-upload me-1"></i> Upload & Preview
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Cara Export CSV dari Bank</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item">
                            <p class="fw-semibold small mb-1">BCA</p>
                            <p class="text-muted small mb-0">KlikBCA → Rekening → Mutasi Rekening → Download CSV</p>
                        </div>
                        <div class="list-group-item">
                            <p class="fw-semibold small mb-1">Mandiri</p>
                            <p class="text-muted small mb-0">Livin → Rekening → Mutasi → Export Excel/CSV</p>
                        </div>
                        <div class="list-group-item">
                            <p class="fw-semibold small mb-1">BRI</p>
                            <p class="text-muted small mb-0">BRImo → Rekening → Cetak Mutasi → Download</p>
                        </div>
                        <div class="list-group-item">
                            <p class="fw-semibold small mb-1">GoPay / OVO / Dana</p>
                            <p class="text-muted small mb-0">Menu Riwayat → Filter periode → Export</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
