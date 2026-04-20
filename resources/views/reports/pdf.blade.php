<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }

  body {
    font-family: 'DejaVu Serif', Georgia, serif;
    font-size: 11px;
    color: #1a1a2e;
    line-height: 1.6;
    background: #fff;
    /* A4: 210mm wide, usable ~180mm. At 96dpi ≈ 680px. Use px so DomPDF/wkhtmltopdf
       respects the layout; set @page margins instead of body padding. */
  }

  @page {
    size: A4 portrait;
    margin: 28px 36px 28px 36px;
  }

  /* inner wrapper keeps content from touching page edges */
  .page-wrap {
    max-width: 680px;
    margin: 0 auto;
  }

  /* ─────────────── HEADER ─────────────── */
  .header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 28px;
  }

  .brand {
    font-family: 'DejaVu Serif', Georgia, serif;
    font-size: 18px;
    font-weight: 700;
    color: #1a1a2e;
    letter-spacing: -0.5px;
  }

  .brand-accent { color: #c0a06b; }

  .brand-sub {
    font-size: 9px;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: #888;
    margin-top: 2px;
    font-family: 'DejaVu Sans', Arial, sans-serif;
  }

  .header-right { text-align: right; }

  .period-label {
    font-size: 9px;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: #aaa;
    margin-bottom: 4px;
    font-family: 'DejaVu Sans', Arial, sans-serif;
  }

  .period-badge {
    display: inline-block;
    background: #1a1a2e;
    color: #fff;
    font-size: 10px;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 4px;
    letter-spacing: 0.3px;
    font-family: 'DejaVu Sans', Arial, sans-serif;
  }

  .header-meta {
    font-size: 10px;
    color: #888;
    margin-top: 6px;
    line-height: 1.6;
    font-family: 'DejaVu Sans', Arial, sans-serif;
  }

  /* ── thin gold → grey gradient divider (matches preview) ── */
  .divider {
    height: 1px;
    /* DomPDF doesn't support CSS gradients on hr/div well;
       use a two-cell table trick for gradient effect */
    margin-bottom: 24px;
    background: #e5e7eb;
    position: relative;
  }

  .divider::before {
    content: '';
    position: absolute;
    top: 0; left: 0;
    width: 60px; height: 1px;
    background: #c0a06b;
  }

  /* ─────────────── SUMMARY CARDS ─────────────── */
  /* Use table layout for reliable 3-col in PDF renderers */
  .summary {
    width: 100%;
    border-collapse: separate;
    border-spacing: 10px 0;
    margin-bottom: 28px;
    display: table;
  }

  .summary-row {
    display: table-row;
  }

  .s-card {
    display: table-cell;
    width: 33.33%;
    padding: 13px 15px 13px 15px;
    border: 0.5px solid #e5e7eb;
    border-radius: 6px;
    position: relative;
    vertical-align: top;
  }

  /* top accent bar via border-top override */
  .s-income  { border-top: 2px solid #22a06b; }
  .s-expense { border-top: 2px solid #e5533d; }
  .s-balance { border-top: 2px solid #c0a06b; }

  .s-label {
    font-size: 9px;
    letter-spacing: 0.8px;
    text-transform: uppercase;
    color: #999;
    margin-bottom: 6px;
    font-family: 'DejaVu Sans', Arial, sans-serif;
  }

  .s-amount {
    font-family: 'DejaVu Serif', Georgia, serif;
    font-size: 15px;
    font-weight: 700;
  }

  .s-amount.green { color: #1a7a4e; }
  .s-amount.red   { color: #c0392b; }
  .s-amount.gold  { color: #8a6c2f; }

  .s-amount-suffix {
    font-size: 9px;
    font-weight: 400;
    font-family: 'DejaVu Sans', Arial, sans-serif;
  }

  .s-note {
    font-size: 9px;
    color: #bbb;
    margin-top: 4px;
    font-family: 'DejaVu Sans', Arial, sans-serif;
  }

  /* ─────────────── SECTION TITLE ─────────────── */
  /* replicate preview's label + full-width line */
  .section-head-wrap {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 14px;
  }

  .section-head {
    font-size: 9px;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: #aaa;
    white-space: nowrap;
    font-family: 'DejaVu Sans', Arial, sans-serif;
  }

  .section-head-line {
    flex: 1;
    height: 0.5px;
    background: #e5e7eb;
  }

  /* ─────────────── CATEGORY BARS ─────────────── */
  .cat-section { margin-bottom: 28px; }

  .cat-row {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 9px;
  }

  .cat-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    flex-shrink: 0;
  }

  .cat-name {
    width: 120px;
    font-size: 10px;
    color: #555;
    font-family: 'DejaVu Sans', Arial, sans-serif;
  }

  .bar-track {
    flex: 1;
    height: 5px;
    background: #f3f4f6;
    border-radius: 99px;
    overflow: hidden;
  }

  .bar-fill {
    height: 100%;
    border-radius: 99px;
  }

  .cat-amount {
    width: 90px;
    font-size: 10px;
    color: #333;
    text-align: right;
    font-weight: 600;
    font-family: 'DejaVu Sans', Arial, sans-serif;
  }

  .cat-pct {
    width: 32px;
    font-size: 9px;
    color: #bbb;
    text-align: right;
    font-family: 'DejaVu Sans', Arial, sans-serif;
  }

  /* ─────────────── TRANSACTION TABLE ─────────────── */
  .tx-section { margin-top: 4px; }

  .tx-table { width: 100%; border-collapse: collapse; }

  .tx-table thead tr {
    border-bottom: 1px solid #1a1a2e;
  }

  .tx-table th {
    font-size: 9px;
    letter-spacing: 0.8px;
    text-transform: uppercase;
    color: #999;
    padding: 0 8px 8px;
    text-align: left;
    font-weight: 400;
    font-family: 'DejaVu Sans', Arial, sans-serif;
  }

  .tx-table td {
    padding: 8px 8px;
    font-size: 10px;
    color: #333;
    border-bottom: 0.5px solid #f0f0f0;
    font-family: 'DejaVu Sans', Arial, sans-serif;
  }

  .tx-table tbody tr:last-child td { border-bottom: none; }

  .text-right { text-align: right; }

  .badge {
    display: inline-block;
    padding: 2px 7px;
    border-radius: 3px;
    font-size: 8.5px;
    font-weight: 600;
    letter-spacing: 0.3px;
    font-family: 'DejaVu Sans', Arial, sans-serif;
  }

  .badge-income   { background: #e8f5ee; color: #1a7a4e; }
  .badge-expense  { background: #fdecea; color: #c0392b; }
  .badge-transfer { background: #eef2ff; color: #3b4fcf; }

  .amount-income   { color: #1a7a4e; font-weight: 600; }
  .amount-expense  { color: #c0392b; font-weight: 600; }
  .amount-transfer { color: #3b4fcf; font-weight: 600; }

  .empty-row td {
    text-align: center;
    padding: 20px;
    color: #ccc;
    font-style: italic;
  }

  /* ─────────────── FOOTER ─────────────── */
  .footer {
    margin-top: 28px;
    padding-top: 12px;
    border-top: 0.5px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-family: 'DejaVu Sans', Arial, sans-serif;
  }

  .footer-left {
    font-size: 9px;
    color: #bbb;
  }

  .footer-brand { color: #c0a06b; font-weight: 700; }

  .footer-right {
    font-size: 9px;
    color: #bbb;
  }

  .page-break { page-break-after: always; }
</style>
</head>
<body>
<div class="page-wrap">

{{-- HEADER --}}
<div class="header">
  <div>
    <div class="brand">{{ config('app.name') }}<span class="brand-accent">.</span></div>
    <div class="brand-sub">Laporan Keuangan Pribadi</div>
  </div>
  <div class="header-right">
    <div class="period-label">Periode</div>
    <div class="period-badge">{{ $fromDate }} — {{ $toDate }}</div>
    <div class="header-meta">
      {{ $user->name }} &nbsp;·&nbsp; {{ $user->email }}<br>
      Dicetak: {{ now()->translatedFormat('d F Y, H:i') }}
    </div>
  </div>
</div>

<div class="divider"></div>

{{-- SUMMARY CARDS --}}
{{-- Use table layout for reliable 3-col rendering in PDF engines --}}
<table style="width:100%;border-collapse:separate;border-spacing:10px 0;margin-bottom:28px;">
  <tr>
    <td style="width:33.33%;padding:13px 15px;border:0.5px solid #e5e7eb;border-top:2px solid #22a06b;border-radius:6px;vertical-align:top;">
      <div class="s-label">Pemasukan</div>
      <div class="s-amount green">Rp {{ number_format($income, 0, ',', '.') }}</div>
      <div class="s-note">{{ $incomeCount ?? $transactions->where('type.value', 'income')->count() }} transaksi</div>
    </td>
    <td style="width:33.33%;padding:13px 15px;border:0.5px solid #e5e7eb;border-top:2px solid #e5533d;border-radius:6px;vertical-align:top;">
      <div class="s-label">Pengeluaran</div>
      <div class="s-amount red">Rp {{ number_format($expense, 0, ',', '.') }}</div>
      <div class="s-note">{{ $expenseCount ?? $transactions->where('type.value', 'expense')->count() }} transaksi</div>
    </td>
    <td style="width:33.33%;padding:13px 15px;border:0.5px solid #e5e7eb;border-top:2px solid #c0a06b;border-radius:6px;vertical-align:top;">
      <div class="s-label">Selisih bersih</div>
      <div class="s-amount {{ $balance >= 0 ? 'gold' : 'red' }}">
        Rp {{ number_format(abs($balance), 0, ',', '.') }}
        <span class="s-amount-suffix">{{ $balance >= 0 ? 'surplus' : 'defisit' }}</span>
      </div>
      @php
        $savingRatio = $income > 0 ? round(($balance / $income) * 100) : 0;
      @endphp
      <div class="s-note">Rasio tabungan {{ $savingRatio }}%</div>
    </td>
  </tr>
</table>

{{-- CATEGORY BREAKDOWN --}}
@if ($byCategory->isNotEmpty())
<div class="cat-section">
  <div class="section-head-wrap">
    <span class="section-head">Pengeluaran per kategori</span>
    <div class="section-head-line"></div>
  </div>
  @foreach ($byCategory->take(8) as $cat)
    @php $pct = $expense > 0 ? round(($cat['total'] / $expense) * 100) : 0; @endphp
    <div class="cat-row">
      <div class="cat-dot" style="background: {{ $cat['color'] }}"></div>
      <div class="cat-name">{{ $cat['name'] }}</div>
      <div class="bar-track">
        <div class="bar-fill" style="width: {{ $pct }}%; background: {{ $cat['color'] }}"></div>
      </div>
      <div class="cat-amount">Rp {{ number_format($cat['total'], 0, ',', '.') }}</div>
      <div class="cat-pct">{{ $pct }}%</div>
    </div>
  @endforeach
</div>
@endif

{{-- TRANSACTION TABLE --}}
<div class="tx-section">
  <div class="section-head-wrap">
    <span class="section-head">Daftar transaksi</span>
    <div class="section-head-line"></div>
  </div>

  <table class="tx-table">
    <thead>
      <tr>
        <th width="58">Tanggal</th>
        <th width="60">Tipe</th>
        <th width="95">Rekening</th>
        <th width="100">Kategori</th>
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
        <td>{{ $tx->category?->name ?? '—' }}</td>
        <td>{{ $tx->note ?? '—' }}</td>
        <td class="text-right amount-{{ $tx->type->value }}">
          {{ $tx->type->value === 'expense' ? '−' : '+' }}Rp {{ number_format($tx->amount, 0, ',', '.') }}
        </td>
      </tr>
      @empty
      <tr class="empty-row">
        <td colspan="6">Tidak ada transaksi di periode ini.</td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>

{{-- FOOTER --}}
<div class="footer">
  <div class="footer-left">
    <span class="footer-brand">{{ config('app.name') }}</span> &nbsp;—&nbsp; Laporan ini dibuat otomatis oleh sistem
  </div>
  <div class="footer-right">{{ $fromDate }} s/d {{ $toDate }}</div>
</div>

</div>{{-- /.page-wrap --}}
</body>
</html>