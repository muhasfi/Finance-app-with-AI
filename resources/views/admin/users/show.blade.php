@extends('layouts.app')
@section('title', 'Detail User')

@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6">
                <h3>Detail User</h3>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-md-5">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <span class="avatar-content bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"
                              style="width:56px;height:56px;font-size:22px;font-weight:500">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </span>
                        <div>
                            <h5 class="mb-0">{{ $user->name }}</h5>
                            <p class="text-muted mb-0">{{ $user->email }}</p>
                        </div>
                    </div>

                    <table class="table table-borderless">
                        <tr>
                            <td class="text-muted" width="130">Role</td>
                            <td>
                                @if ($user->isAdmin())
                                    <span class="badge bg-danger">Admin</span>
                                @else
                                    <span class="badge bg-secondary">User</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Status</td>
                            <td>
                                <span class="badge {{ $user->status === 'active' ? 'bg-success' : 'bg-warning text-dark' }}">
                                    {{ ucfirst($user->status) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Rekening</td>
                            <td>{{ $user->accounts_count }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Transaksi</td>
                            <td>{{ number_format($totalTransactions) }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Login terakhir</td>
                            <td class="small">
                                {{ $user->last_login_at ? $user->last_login_at->translatedFormat('d F Y, H:i') : '-' }}
                                @if ($user->last_login_ip)
                                    <br><small class="text-muted">{{ $user->last_login_ip }}</small>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Bergabung</td>
                            <td class="small">{{ $user->created_at->translatedFormat('d F Y') }}</td>
                        </tr>
                    </table>
                </div>
                <div class="card-footer d-flex gap-2 flex-wrap">
                    @if (! $user->isAdmin())
                        @if ($user->status === 'active')
                            <form method="POST" action="{{ route('admin.users.suspend', $user) }}">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-warning btn-sm"
                                        onclick="return confirm('Suspend akun ini?')">
                                    <i class="bi bi-pause-circle me-1"></i> Suspend
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('admin.users.activate', $user) }}">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-success btn-sm">
                                    <i class="bi bi-play-circle me-1"></i> Aktifkan
                                </button>
                            </form>
                        @endif
                    @endif
                    <a href="{{ route('admin.users.index') }}" class="btn btn-light btn-sm ms-auto">
                        <i class="bi bi-arrow-left me-1"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
