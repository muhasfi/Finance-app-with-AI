@extends('layouts.app')
@section('title', 'Preview Import')

@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6">
                <h3>Mapping Kolom CSV</h3>
                <p class="text-subtitle text-muted">Tentukan kolom yang sesuai lalu konfirmasi import</p>
            </div>
        </div>
    </div>

    @include('components.alert')

    <div class="row">
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">Preview 5 baris pertama — rekening: {{ $account->name }}</h5>
                </div>
                <div class="card-body p-0" style="overflow-x:auto">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                @foreach ($headers as $h)
                                    <th class="small">{{ $h }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($preview as $row)
                                <tr>
                                    @foreach ($row as $val)
                                        <td class="small text-muted">{{ $val }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Atur Mapping Kolom</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('import.confirm') }}">
                        @csrf

                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Kolom Tanggal <span class="text-danger">*</span></label>
                                <select name="col_date" class="form-select form-select-sm @error('col_date') is-invalid @enderror" required>
                                    @foreach ($headers as $h)
                                        <option value="{{ $h }}" {{ str_contains(strtolower($h), 'tanggal') || str_contains(strtolower($h), 'date') ? 'selected' : '' }}>
                                            {{ $h }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Format Tanggal <span class="text-danger">*</span></label>
                                <select name="date_format" class="form-select form-select-sm @error('date_format') is-invalid @enderror" required>
                                    <option value="d/m/Y">DD/MM/YYYY (contoh: 25/04/2025)</option>
                                    <option value="Y-m-d">YYYY-MM-DD (contoh: 2025-04-25)</option>
                                    <option value="d-m-Y">DD-MM-YYYY (contoh: 25-04-2025)</option>
                                    <option value="m/d/Y">MM/DD/YYYY (contoh: 04/25/2025)</option>
                                    <option value="d/m/y">DD/MM/YY (contoh: 25/04/25)</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Kolom Jumlah <span class="text-danger">*</span></label>
                                <select name="col_amount" class="form-select form-select-sm @error('col_amount') is-invalid @enderror" required>
                                    @foreach ($headers as $h)
                                        <option value="{{ $h }}" {{ str_contains(strtolower($h), 'jumlah') || str_contains(strtolower($h), 'nominal') || str_contains(strtolower($h), 'amount') ? 'selected' : '' }}>
                                            {{ $h }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Kolom Keterangan</label>
                                <select name="col_description" class="form-select form-select-sm">
                                    <option value="">-- Tidak ada --</option>
                                    @foreach ($headers as $h)
                                        <option value="{{ $h }}" {{ str_contains(strtolower($h), 'ket') || str_contains(strtolower($h), 'desc') || str_contains(strtolower($h), 'transaksi') ? 'selected' : '' }}>
                                            {{ $h }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Kolom Debit/Kredit</label>
                                <select name="col_type" class="form-select form-select-sm">
                                    <option value="">-- Tidak ada --</option>
                                    @foreach ($headers as $h)
                                        <option value="{{ $h }}" {{ str_contains(strtolower($h), 'debit') || str_contains(strtolower($h), 'kredit') || str_contains(strtolower($h), 'type') ? 'selected' : '' }}>
                                            {{ $h }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">Jika kolom ini ada, tipe transaksi ditentukan otomatis.</div>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Tipe Default <span class="text-danger">*</span></label>
                                <select name="type_default" class="form-select form-select-sm" required>
                                    <option value="expense">Pengeluaran</option>
                                    <option value="income">Pemasukan</option>
                                </select>
                                <div class="form-text">Dipakai jika tidak ada kolom debit/kredit.</div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-circle me-1"></i> Konfirmasi Import
                            </button>
                            <a href="{{ route('import.create') }}" class="btn btn-light">
                                <i class="bi bi-arrow-left me-1"></i> Upload Ulang
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
