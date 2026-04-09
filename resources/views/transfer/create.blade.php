@extends('layouts.app')
@section('title', 'Transfer Antar Rekening')

@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12">
                <h3>Transfer Antar Rekening</h3>
                <p class="text-subtitle text-muted">Pindahkan saldo antara rekening Anda</p>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">

                    <form method="POST" action="{{ route('transfer.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Dari Rekening <span class="text-danger">*</span></label>
                            <select name="from_account_id" id="fromAccount"
                                    class="form-select @error('from_account_id') is-invalid @enderror"
                                    required onchange="updateBalance()">
                                <option value="">-- Pilih rekening asal --</option>
                                @foreach ($accounts as $acc)
                                    <option value="{{ $acc->id }}"
                                            data-balance="{{ $acc->balance }}"
                                            {{ old('from_account_id') == $acc->id ? 'selected' : '' }}>
                                        {{ $acc->name }} — Rp {{ number_format($acc->balance, 0, ',', '.') }}
                                    </option>
                                @endforeach
                            </select>
                            <div id="balanceInfo" class="form-text text-muted" style="display:none">
                                Saldo tersedia: <strong id="balanceDisplay"></strong>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Ke Rekening <span class="text-danger">*</span></label>
                            <select name="to_account_id"
                                    class="form-select @error('to_account_id') is-invalid @enderror"
                                    required>
                                <option value="">-- Pilih rekening tujuan --</option>
                                @foreach ($accounts as $acc)
                                    <option value="{{ $acc->id }}"
                                            {{ old('to_account_id') == $acc->id ? 'selected' : '' }}>
                                        {{ $acc->name }} — Rp {{ number_format($acc->balance, 0, ',', '.') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Jumlah <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="amount"
                                           class="form-control @error('amount') is-invalid @enderror"
                                           value="{{ old('amount') }}" min="1" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                                <input type="date" name="date"
                                       class="form-control @error('date') is-invalid @enderror"
                                       value="{{ old('date', now()->format('Y-m-d')) }}"
                                       max="{{ now()->format('Y-m-d') }}" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Catatan</label>
                            <input type="text" name="note"
                                   class="form-control @error('note') is-invalid @enderror"
                                   value="{{ old('note') }}"
                                   placeholder="Keterangan transfer (opsional)" maxlength="255">
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-arrow-left-right me-1"></i> Proses Transfer
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

@push('scripts')
<script>
function updateBalance() {
    const sel = document.getElementById('fromAccount');
    const opt = sel.options[sel.selectedIndex];
    const bal = opt.getAttribute('data-balance');
    const info = document.getElementById('balanceInfo');
    const disp = document.getElementById('balanceDisplay');

    if (bal) {
        info.style.display = 'block';
        disp.textContent = 'Rp ' + new Intl.NumberFormat('id').format(bal);
    } else {
        info.style.display = 'none';
    }
}
</script>
@endpush
