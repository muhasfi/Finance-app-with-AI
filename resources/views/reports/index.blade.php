@extends('layouts.app')
@section('title', 'Laporan & Export')

@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6">
                <h3>Laporan & Export</h3>
                <p class="text-subtitle text-muted">Export transaksi ke CSV atau PDF</p>
            </div>
        </div>
    </div>

    @include('components.alert')

    <div class="row">
        <div class="col-12 col-md-7">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-funnel me-2"></i>Filter Laporan</h5>
                </div>
                <div class="card-body">

                    {{-- Form filter — dipakai oleh kedua tombol export --}}
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Dari Tanggal <span class="text-danger">*</span></label>
                            <input type="date" id="from" class="form-control"
                                   value="{{ now()->startOfMonth()->format('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Sampai Tanggal <span class="text-danger">*</span></label>
                            <input type="date" id="to" class="form-control"
                                   value="{{ now()->format('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tipe Transaksi</label>
                            <select id="type" class="form-select">
                                <option value="">Semua tipe</option>
                                <option value="income">Pemasukan</option>
                                <option value="expense">Pengeluaran</option>
                                <option value="transfer">Transfer</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Rekening</label>
                            <select id="account_id" class="form-select">
                                <option value="">Semua rekening</option>
                                @foreach ($accounts as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Kategori</label>
                            <select id="category_id" class="form-select">
                                <option value="">Semua kategori</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Tombol export --}}
                    <div class="d-flex gap-2 flex-wrap">
                        <button onclick="exportFile('csv')" class="btn btn-success">
                            <i class="bi bi-file-earmark-spreadsheet me-1"></i> Download CSV
                        </button>
                        <button onclick="exportFile('pdf')" class="btn btn-danger">
                            <i class="bi bi-file-earmark-pdf me-1"></i> Download PDF
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-5">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Shortcut Periode</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button onclick="setRange('this_month')" class="btn btn-outline-secondary text-start">
                            <i class="bi bi-calendar-month me-2"></i>
                            Bulan ini ({{ now()->translatedFormat('F Y') }})
                        </button>
                        <button onclick="setRange('last_month')" class="btn btn-outline-secondary text-start">
                            <i class="bi bi-calendar-minus me-2"></i>
                            Bulan lalu ({{ now()->subMonth()->translatedFormat('F Y') }})
                        </button>
                        <button onclick="setRange('this_year')" class="btn btn-outline-secondary text-start">
                            <i class="bi bi-calendar-range me-2"></i>
                            Tahun ini ({{ now()->year }})
                        </button>
                        <button onclick="setRange('last_3_months')" class="btn btn-outline-secondary text-start">
                            <i class="bi bi-calendar3 me-2"></i>
                            3 bulan terakhir
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function getParams() {
    const params = new URLSearchParams();
    params.set('from',        document.getElementById('from').value);
    params.set('to',          document.getElementById('to').value);
    const type       = document.getElementById('type').value;
    const account_id = document.getElementById('account_id').value;
    const category_id= document.getElementById('category_id').value;
    if (type)        params.set('type', type);
    if (account_id)  params.set('account_id', account_id);
    if (category_id) params.set('category_id', category_id);
    return params;
}

function exportFile(format) {
    const from = document.getElementById('from').value;
    const to   = document.getElementById('to').value;
    if (!from || !to) {
        alert('Harap isi tanggal dari dan sampai.');
        return;
    }
    const params = getParams();
    const url    = format === 'pdf'
        ? '/reports/export/pdf?' + params.toString()
        : '/reports/export?' + params.toString();
    window.location.href = url;
}

function setRange(range) {
    const now = new Date();
    let from, to;

    if (range === 'this_month') {
        from = new Date(now.getFullYear(), now.getMonth(), 1);
        to   = now;
    } else if (range === 'last_month') {
        from = new Date(now.getFullYear(), now.getMonth() - 1, 1);
        to   = new Date(now.getFullYear(), now.getMonth(), 0);
    } else if (range === 'this_year') {
        from = new Date(now.getFullYear(), 0, 1);
        to   = now;
    } else if (range === 'last_3_months') {
        from = new Date(now.getFullYear(), now.getMonth() - 3, 1);
        to   = now;
    }

    const fmt = d => d.toISOString().split('T')[0];
    document.getElementById('from').value = fmt(from);
    document.getElementById('to').value   = fmt(to);
}
</script>
@endpush
