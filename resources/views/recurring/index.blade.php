@extends('layouts.app')
@section('title', 'Transaksi Berulang')

@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6">
                <h3>Transaksi Berulang</h3>
                <p class="text-subtitle text-muted">Transaksi yang dibuat otomatis sesuai jadwal</p>
            </div>
            <div class="col-12 col-md-6 d-flex justify-content-end align-items-center">
                <a href="{{ route('recurring.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i> Tambah Rencana
                </a>
            </div>
        </div>
    </div>

    {{-- @include('components.alert') --}}

    {{-- Info cara kerja --}}
    {{-- <div class="alert alert-light border d-flex gap-2 align-items-start mb-4">
        <i class="bi bi-info-circle text-primary mt-1 flex-shrink-0"></i>
        <div class="small text-muted">
            Transaksi berulang dibuat <strong>otomatis setiap hari jam 07:00</strong> oleh Laravel Scheduler.
            Pastikan scheduler sudah aktif: <code>php artisan schedule:work</code> (development) atau crontab (production).
        </div>
    </div> --}}

    @if ($plans->isEmpty())
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-arrow-repeat fs-1 text-muted d-block mb-3"></i>
                <h5 class="text-muted">Belum ada transaksi berulang</h5>
                <p class="text-muted small mb-3">Contoh: gaji bulanan, tagihan listrik, cicilan, langganan streaming.</p>
                <a href="{{ route('recurring.create') }}" class="btn btn-primary">Tambah Sekarang</a>
            </div>
        </div>
    @else

        {{-- Aktif --}}
        @php $active = $plans->where('is_active', true); @endphp
        @if ($active->count())
            <h6 class="text-muted mb-2 mt-3">Aktif ({{ $active->count() }})</h6>
            <div class="card mb-4">
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nama</th>
                                <th>Rekening</th>
                                <th>Frekuensi</th>
                                <th>Eksekusi berikutnya</th>
                                <th>Berakhir</th>
                                <th class="text-end">Jumlah</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($active as $plan)
                                <tr>
                                    <td>
                                        <div class="fw-semibold small">{{ $plan->name }}</div>
                                        @if ($plan->category)
                                            <span class="badge rounded-pill small"
                                                  style="background:{{ $plan->category->color }}20;color:{{ $plan->category->color }}">
                                                {{ $plan->category->name }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="small text-muted">{{ $plan->account->name }}</td>
                                    <td>
                                        <span class="badge bg-light text-dark">{{ $plan->frequency->label() }}</span>
                                    </td>
                                    <td class="small">
                                        {{ $plan->next_run_at->translatedFormat('d M Y') }}
                                        @if ($plan->next_run_at->isToday())
                                            <span class="badge bg-warning text-dark ms-1">Hari ini</span>
                                        @elseif ($plan->next_run_at->isPast())
                                            <span class="badge bg-danger ms-1">Terlambat</span>
                                        @endif
                                    </td>
                                    <td class="small text-muted">
                                        {{ $plan->ends_at ? $plan->ends_at->translatedFormat('d M Y') : '—' }}
                                    </td>
                                    <td class="text-end fw-bold small {{ $plan->type->value === 'income' ? 'text-success' : 'text-danger' }}">
                                        {{ $plan->type->value === 'income' ? '+' : '-' }}Rp {{ number_format($plan->amount, 0, ',', '.') }}
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex gap-1 justify-content-center">
                                            <a href="{{ route('recurring.edit', $plan) }}" class="btn btn-sm btn-light" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form method="POST" action="{{ route('recurring.toggle', $plan) }}">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-light text-warning" title="Nonaktifkan">
                                                    <i class="bi bi-pause-circle"></i>
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('recurring.destroy', $plan) }}"
                                                  onsubmit="return confirm('Yakin hapus rencana ini?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-light text-danger" title="Hapus">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- Nonaktif --}}
        @php $inactive = $plans->where('is_active', false); @endphp
        @if ($inactive->count())
            <h6 class="text-muted mb-2">Nonaktif ({{ $inactive->count() }})</h6>
            <div class="card">
                <div class="card-body p-0">
                    <table class="table table-hover mb-0 opacity-50">
                        <tbody>
                            @foreach ($inactive as $plan)
                                <tr>
                                    <td>
                                        <div class="fw-semibold small">{{ $plan->name }}</div>
                                    </td>
                                    <td class="small text-muted">{{ $plan->account->name }}</td>
                                    <td><span class="badge bg-light text-dark">{{ $plan->frequency->label() }}</span></td>
                                    <td class="small text-muted">{{ $plan->next_run_at->translatedFormat('d M Y') }}</td>
                                    <td class="text-end fw-bold small {{ $plan->type->value === 'income' ? 'text-success' : 'text-danger' }}">
                                        {{ $plan->type->value === 'income' ? '+' : '-' }}Rp {{ number_format($plan->amount, 0, ',', '.') }}
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex gap-1 justify-content-center">
                                            <form method="POST" action="{{ route('recurring.toggle', $plan) }}">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-success btn-sm" title="Aktifkan kembali">
                                                    <i class="bi bi-play-circle me-1"></i> Aktifkan
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('recurring.destroy', $plan) }}"
                                                  onsubmit="return confirm('Yakin hapus?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-light text-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

    @endif
</div>
@endsection
