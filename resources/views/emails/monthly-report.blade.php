<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
  body { font-family: Arial, sans-serif; font-size: 14px; color: #1f2937; margin: 0; padding: 0; background: #f9fafb; }
  .wrapper { max-width: 600px; margin: 0 auto; padding: 24px 16px; }
  .card { background: #fff; border-radius: 12px; padding: 28px; box-shadow: 0 1px 4px rgba(0,0,0,.08); }
  .header { text-align: center; margin-bottom: 24px; }
  .app-name { font-size: 22px; font-weight: 700; color: #6366f1; }
  .subtitle { font-size: 13px; color: #6b7280; margin-top: 4px; }
  .divider { border: none; border-top: 1px solid #e5e7eb; margin: 20px 0; }
  .greeting { font-size: 15px; margin-bottom: 16px; }
  /* Summary */
  .summary { display: table; width: 100%; margin-bottom: 20px; }
  .summary-item { display: table-cell; text-align: center; padding: 12px; border-radius: 8px; }
  .s-income  { background: #f0fdf4; }
  .s-expense { background: #fef2f2; }
  .s-balance { background: #eef2ff; }
  .s-label { font-size: 11px; color: #6b7280; }
  .s-value { font-size: 16px; font-weight: 700; margin-top: 4px; }
  .green  { color: #16a34a; }
  .red    { color: #dc2626; }
  .purple { color: #6366f1; }
  /* Kategori */
  .section-title { font-size: 13px; font-weight: 700; color: #374151; margin-bottom: 12px; }
  .cat-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; font-size: 13px; }
  .cat-name { color: #374151; }
  .cat-bar-wrap { flex: 1; height: 6px; background: #f3f4f6; border-radius: 99px; margin: 0 12px; overflow: hidden; }
  .cat-bar { height: 100%; border-radius: 99px; }
  .cat-value { color: #6b7280; font-size: 12px; min-width: 90px; text-align: right; }
  /* CTA */
  .cta { text-align: center; margin-top: 24px; }
  .btn {
    display: inline-block;
    background: #6366f1;
    color: #fff !important;
    text-decoration: none;
    padding: 11px 28px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
  }
  .footer { text-align: center; font-size: 11px; color: #9ca3af; margin-top: 20px; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="card">

    {{-- Header --}}
    <div class="header">
      <div class="app-name">{{ config('app.name') }}</div>
      <div class="subtitle">Laporan Keuangan {{ $monthName }}</div>
    </div>

    <hr class="divider">

    <p class="greeting">Halo <strong>{{ $user->name }}</strong>,</p>
    <p style="color:#6b7280;font-size:13px;margin-bottom:20px">
      Berikut ringkasan keuangan kamu bulan <strong>{{ $monthName }}</strong>.
    </p>

    {{-- Summary --}}
    <div class="summary">
      <div class="summary-item s-income">
        <div class="s-label">Pemasukan</div>
        <div class="s-value green">Rp {{ number_format($summary['income'], 0, ',', '.') }}</div>
      </div>
      <div class="summary-item s-expense" style="margin: 0 8px">
        <div class="s-label">Pengeluaran</div>
        <div class="s-value red">Rp {{ number_format($summary['expense'], 0, ',', '.') }}</div>
      </div>
      <div class="summary-item s-balance">
        <div class="s-label">{{ $summary['balance'] >= 0 ? 'Surplus' : 'Defisit' }}</div>
        <div class="s-value {{ $summary['balance'] >= 0 ? 'green' : 'red' }}">
          Rp {{ number_format(abs($summary['balance']), 0, ',', '.') }}
        </div>
      </div>
    </div>

    {{-- Kategori terbesar --}}
    @if (! empty($byCategory))
    <hr class="divider">
    <div class="section-title">Top Pengeluaran per Kategori</div>
    @php $maxAmount = collect($byCategory)->max('amount'); @endphp
    @foreach (array_slice($byCategory, 0, 5) as $cat)
      <div class="cat-row">
        <div class="cat-name">{{ $cat['label'] }}</div>
        <div class="cat-bar-wrap">
          <div class="cat-bar" style="width:{{ $maxAmount > 0 ? round(($cat['amount']/$maxAmount)*100) : 0 }}%;background:{{ $cat['color'] }}"></div>
        </div>
        <div class="cat-value">Rp {{ number_format($cat['amount'], 0, ',', '.') }}</div>
      </div>
    @endforeach
    @endif

    <hr class="divider">

    <div class="cta">
      <a href="{{ config('app.url') }}/dashboard" class="btn">Lihat Dashboard Lengkap</a>
    </div>

  </div>

  <div class="footer">
    Email ini dikirim otomatis oleh {{ config('app.name') }}<br>
    {{ config('app.url') }}
  </div>
</div>
</body>
</html>
