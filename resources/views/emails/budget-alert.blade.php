<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
  body { font-family: Arial, sans-serif; font-size: 14px; color: #1f2937; margin: 0; padding: 0; background: #f9fafb; }
  .wrapper { max-width: 560px; margin: 0 auto; padding: 24px 16px; }
  .card { background: #fff; border-radius: 12px; padding: 28px; box-shadow: 0 1px 4px rgba(0,0,0,.08); }
  .alert-bar {
    border-radius: 8px;
    padding: 14px 16px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 12px;
  }
  .alert-exceeded { background: #fef2f2; border-left: 4px solid #ef4444; }
  .alert-warning   { background: #fffbeb; border-left: 4px solid #f59e0b; }
  .alert-icon { font-size: 24px; }
  .alert-title { font-weight: 700; font-size: 15px; }
  .alert-exceeded .alert-title { color: #dc2626; }
  .alert-warning  .alert-title { color: #d97706; }
  .progress-wrap { margin: 20px 0; }
  .progress-label { display: flex; justify-content: space-between; font-size: 12px; color: #6b7280; margin-bottom: 6px; }
  .progress-bar-bg { height: 12px; background: #f3f4f6; border-radius: 99px; overflow: hidden; }
  .progress-bar { height: 100%; border-radius: 99px; }
  .stats { display: table; width: 100%; margin: 16px 0; }
  .stat { display: table-cell; text-align: center; padding: 10px; }
  .stat-label { font-size: 11px; color: #6b7280; }
  .stat-value { font-size: 15px; font-weight: 700; margin-top: 3px; }
  .btn {
    display: inline-block;
    background: #6366f1;
    color: #fff !important;
    text-decoration: none;
    padding: 10px 24px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
  }
  .footer { text-align: center; font-size: 11px; color: #9ca3af; margin-top: 20px; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="card">

    <div style="text-align:center;margin-bottom:20px">
      <div style="font-size:20px;font-weight:700;color:#6366f1">{{ config('app.name') }}</div>
    </div>

    {{-- Alert bar --}}
    <div class="alert-bar alert-{{ $status }}">
      <div class="alert-icon">{{ $status === 'exceeded' ? '🚨' : '⚠️' }}</div>
      <div>
        <div class="alert-title">
          @if ($status === 'exceeded')
            Budget {{ $categoryName }} Telah Terlampaui!
          @else
            Budget {{ $categoryName }} Hampir Habis
          @endif
        </div>
        <div style="font-size:12px;color:#6b7280;margin-top:2px">
          Bulan {{ $monthName }}
        </div>
      </div>
    </div>

    {{-- Progress bar --}}
    <div class="progress-wrap">
      <div class="progress-label">
        <span>Terpakai {{ $percentage }}%</span>
        <span>{{ $status === 'exceeded' ? 'Melebihi batas!' : 'Sisa ' . (100 - $percentage) . '%' }}</span>
      </div>
      <div class="progress-bar-bg">
        <div class="progress-bar"
             style="width:{{ min($percentage, 100) }}%;background:{{ $status === 'exceeded' ? '#ef4444' : '#f59e0b' }}">
        </div>
      </div>
    </div>

    {{-- Stats --}}
    <div class="stats">
      <div class="stat">
        <div class="stat-label">Terpakai</div>
        <div class="stat-value" style="color:{{ $status === 'exceeded' ? '#dc2626' : '#d97706' }}">
          Rp {{ number_format($spent, 0, ',', '.') }}
        </div>
      </div>
      <div class="stat">
        <div class="stat-label">Batas Anggaran</div>
        <div class="stat-value" style="color:#374151">
          Rp {{ number_format($limit, 0, ',', '.') }}
        </div>
      </div>
      @if ($status === 'exceeded')
      <div class="stat">
        <div class="stat-label">Kelebihan</div>
        <div class="stat-value" style="color:#dc2626">
          Rp {{ number_format($spent - $limit, 0, ',', '.') }}
        </div>
      </div>
      @else
      <div class="stat">
        <div class="stat-label">Sisa Anggaran</div>
        <div class="stat-value" style="color:#16a34a">
          Rp {{ number_format($limit - $spent, 0, ',', '.') }}
        </div>
      </div>
      @endif
    </div>

    <div style="text-align:center;margin-top:20px">
      <a href="{{ config('app.url') }}/budgets" class="btn">Lihat Detail Budget</a>
    </div>

  </div>
  <div class="footer">
    Email ini dikirim otomatis oleh {{ config('app.name') }}
  </div>
</div>
</body>
</html>
