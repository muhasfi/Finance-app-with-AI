@extends('layouts.app')
@section('title', 'Admin Dashboard')

@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6">
                <h3>Admin Dashboard</h3>
                <p class="text-subtitle text-muted">Ringkasan keseluruhan sistem</p>
            </div>
        </div>
    </div>

    {{-- Stats --}}
    <section class="row">
        <div class="col-12 col-lg-2 col-md-4 col-sm-6">
            <div class="card">
                <div class="card-body px-4 py-3">
                    <h6 class="text-muted small">Total User</h6>
                    <h4 class="fw-bold mb-0">{{ number_format($stats['total_users']) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-2 col-md-4 col-sm-6">
            <div class="card">
                <div class="card-body px-4 py-3">
                    <h6 class="text-muted small">User Aktif</h6>
                    <h4 class="fw-bold mb-0 text-success">{{ number_format($stats['active_users']) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-2 col-md-4 col-sm-6">
            <div class="card">
                <div class="card-body px-4 py-3">
                    <h6 class="text-muted small">User Suspended</h6>
                    <h4 class="fw-bold mb-0 text-danger">{{ number_format($stats['suspended_users']) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-2 col-md-4 col-sm-6">
            <div class="card">
                <div class="card-body px-4 py-3">
                    <h6 class="text-muted small">User Baru (bulan ini)</h6>
                    <h4 class="fw-bold mb-0 text-info">{{ number_format($stats['new_this_month']) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-2 col-md-4 col-sm-6">
            <div class="card">
                <div class="card-body px-4 py-3">
                    <h6 class="text-muted small">Total Transaksi</h6>
                    <h4 class="fw-bold mb-0">{{ number_format($stats['total_tx']) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-2 col-md-4 col-sm-6">
            <div class="card">
                <div class="card-body px-4 py-3">
                    <h6 class="text-muted small">Transaksi Bulan Ini</h6>
                    <h4 class="fw-bold mb-0">{{ number_format($stats['tx_this_month']) }}</h4>
                </div>
            </div>
        </div>
    </section>

    <section class="row">
        {{-- User terbaru --}}
        <div class="col-12 col-lg-5">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">User Terbaru</h5>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-light">Lihat semua</a>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <tbody>
                            @foreach ($recentUsers as $user)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="avatar-content bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"
                                                  style="width:32px;height:32px;font-size:12px;font-weight:500;flex-shrink:0">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </span>
                                            <div>
                                                <p class="mb-0 fw-semibold small">{{ $user->name }}</p>
                                                <small class="text-muted">{{ $user->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        @if ($user->isAdmin())
                                            <span class="badge bg-danger">Admin</span>
                                        @else
                                            <span class="badge bg-light text-muted">User</span>
                                        @endif
                                        @if ($user->status === 'suspended')
                                            <span class="badge bg-warning text-dark">Suspended</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Audit log terbaru --}}
        <div class="col-12 col-lg-7">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Aktivitas Terbaru</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>User</th><th>Aksi</th><th>Waktu</th></tr></thead>
                        <tbody>
                            @forelse ($recentLogs as $log)
                                <tr>
                                    <td class="small">{{ $log->user?->name ?? 'Sistem' }}</td>
                                    <td><span class="badge bg-light text-dark">{{ $log->action }}</span> <small class="text-muted">{{ $log->description }}</small></td>
                                    <td class="text-muted small">{{ $log->created_at->diffForHumans() }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted py-3">Belum ada aktivitas</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
