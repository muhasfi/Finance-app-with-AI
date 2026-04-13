<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    font-family: 'DejaVu Sans', sans-serif;
    font-size: 11px;
    color: #1f2937;
    line-height: 1.5;
  }

  /* Header */
  .header {
    padding: 20px 0 16px;
    border-bottom: 2px solid #6366f1;
    margin-bottom: 20px;
  }
  .header-top { display: flex; justify-content: space-between; align-items: flex-start; }
  .app-name { font-size: 20px; font-weight: 700; color: #6366f1; }
  .report-title { font-size: 13px; font-weight: 600; color: #374151; margin-top: 2px; }
  .report-meta { font-size: 10px; color: #6b7280; margin-top: 2px; }
  .header-right { text-align: right; font-size: 10px; color: #6b7280; }

  /* Summary cards */
  .summary { display: flex; gap: 10px; margin-bottom: 20px; }
  .summary-card {
    flex: 1;
    padding: 12px;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
  }
  .summary-card .label { font-size: 10px; color: #6b7280; margin-bottom: 4px; }
  .summary-card .value { font-size: 14px; font-weight: 700; }
  .card-income  { border-left: 4px solid #22c55e; }
  .card-expense { border-left: 4px solid #ef4444; }
  .card-balance { border-left: 4px solid #6366f1; }
  .text-green  { color: #16a34a; }
  .text-red    { color: #dc2626; }
  .text-purple { color: #6366f1; }

  /* Kategori breakdown */
  .section-title {
    font-size: 12px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 10px;
    padding-bottom: 4px;
    border-bottom: 1px solid #e5e7eb;
  }
  .category-row { display: flex; align-items: center; gap: 8px; margin-bottom: 6px; }
  .cat-bar-wrap {
    flex: 1;
    height: 8px;
    background: #f3f4f6;
    border-radius: 99px;
    overflow: hidden;
  }
  .cat-bar { height: 100%; border-radius: 99px; }
  .cat-name  { width: 130px; font-size: 10px; color: #374151; }
  .cat-value { width: 100px; font-size: 10px; color: #374151; text-align: right; }
  .cat-pct   { width: 36px; font-size: 10px; color: #6b7280; text-align: right; }

  /* Tabel transaksi */
  .tx-table { width: 100%; border-collapse: collapse; margin-top: 16px; }
  .tx-table th {
    background: #f3f4f6;
    padding: 7px 8px;
    text-align: left;
    font-size: 10px;
    font-weight: 600;
    color: #374151;
    border-bottom: 1px solid #d1d5db;
  }
  .tx-table td {
    padding: 6px 8px;
    font-size: 10px;
    border-bottom: 1px solid #f3f4f6;
    color: #374151;
  }
  .tx-table tr:nth-child(even) td { background: #fafafa; }
  .text-right { text-align: right; }
  .badge {
    display: inline-block;
    padding: 1px 6px;
    border-radius: 99px;
    font-size: 9px;
    font-weight: 600;
  }
  .badge-income  { background: #dcfce7; color: #16a34a; }
  .badge-expense { background: #fee2e2; color: #dc2626; }
  .badge-transfer{ background: #dbeafe; color: #2563eb; }
  .amount-income  { color: #16a34a; font-weight: 600; }
  .amount-expense { color: #dc2626; font-weight: 600; }
  .amount-transfer{ color: #2563eb; font-weight: 600; }

  /* Footer */
  .footer {
    margin-top: 24px;
    padding-top: 10px;
    border-top: 1px solid #e5e7eb;
    font-size: 9px;
    color: #9ca3af;
    display: flex;
    justify-content: space-between;
  }
  .page-break { page-break-after: always; }
</style>
</head>
<body>

{{-- Header --}}
<div class="header">
  <div class="header-top">
    <div>
      <div class="app-name">{{ config('app.name') }}</div>
      <div class="report-title">Laporan Keuangan</div>
      <div class="report-meta">{{ $fromDate }} — {{ $toDate }} &nbsp;·&nbsp; {{ $user->name }}</div>
    </div>
    <div class="header-right">
      Dicetak: {{ now()->translatedFormat('d F Y, H:i') }}<br>
      {{ $user->email }}
    </div>
  </div>
</div>

{{-- Summary --}}
<div class="summary">
  <div class="summary-card card-income">
    <div class="label">Total Pemasukan</div>
    <div class="value text-green">Rp {{ number_format($income, 0, ',', '.') }}</div>
  </div>
  <div class="summary-card card-expense">
    <div class="label">Total Pengeluaran</div>
    <div class="value text-red">Rp {{ number_format($expense, 0, ',', '.') }}</div>
  </div>
  <div class="summary-card card-balance">
    <div class="label">Selisih</div>
    <div class="value {{ $balance >= 0 ? 'text-green' : 'text-red' }}">
      Rp {{ number_format(abs($balance), 0, ',', '.') }}
      {{ $balance >= 0 ? '(surplus)' : '(defisit)' }}
    </div>
  </div>
</div>

{{-- Breakdown kategori --}}
@if ($byCategory->isNotEmpty())
<div class="section-title">Pengeluaran per Kategori</div>
@foreach ($byCategory->take(8) as $cat)
  @php $pct = $expense > 0 ? round(($cat['total'] / $expense) * 100) : 0; @endphp
  <div class="category-row">
    <div class="cat-name">{{ $cat['name'] }}</div>
    <div class="cat-bar-wrap">
      <div class="cat-bar" style="width:{{ $pct }}%;background:{{ $cat['color'] }}"></div>
    </div>
    <div class="cat-value">Rp {{ number_format($cat['total'], 0, ',', '.') }}</div>
    <div class="cat-pct">{{ $pct }}%</div>
  </div>
@endforeach
@endif

{{-- Tabel transaksi --}}
<div class="section-title" style="margin-top:20px">
  Daftar Transaksi ({{ $transactions->count() }} transaksi)
</div>

<table class="tx-table">
  <thead>
    <tr>
      <th width="60">Tanggal</th>
      <th width="55">Tipe</th>
      <th width="90">Rekening</th>
      <th width="90">Kategori</th>
      <th>Keterangan</th>
      <th width="90" class="text-right">Jumlah</th>
    </tr>
  </thead>
  <tbody>
    @forelse ($transactions as $tx)
    <tr>
      <td>{{ $tx->date->format('d/m/Y') }}</td>
      <td>
        <span class="badge badge-{{ $tx->type->value }}">{{ $tx->type->label() }}</span>
      </td>
      <td>{{ $tx->account->name }}</td>
      <td>{{ $tx->category?->name ?? '-' }}</td>
      <td>{{ $tx->note ?? '-' }}</td>
      <td class="text-right amount-{{ $tx->type->value }}">
        {{ $tx->type->value === 'expense' ? '-' : '+' }}Rp {{ number_format($tx->amount, 0, ',', '.') }}
      </td>
    </tr>
    @empty
    <tr>
      <td colspan="6" style="text-align:center;color:#9ca3af;padding:16px">
        Tidak ada transaksi di periode ini
      </td>
    </tr>
    @endforelse
  </tbody>
</table>

{{-- Footer --}}
<div class="footer">
  <span>{{ config('app.name') }} — Laporan ini dibuat otomatis oleh sistem</span>
  <span>{{ $fromDate }} s/d {{ $toDate }}</span>
</div>

</body>
</html>
