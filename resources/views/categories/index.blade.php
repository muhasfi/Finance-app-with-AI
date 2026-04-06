@extends('layouts.app')
@section('title', 'Kategori')

@section('content')
<div class="page-heading">
    <div class="page-title mb-4">
        <div class="row">
            <div class="col-12 col-md-6">
                <h3>Kategori</h3>
                <p class="text-subtitle text-muted">Kategori default sistem dan kategori kustom Anda</p>
            </div>
            <div class="col-12 col-md-6 d-flex justify-content-end align-items-center">
                <a href="{{ route('categories.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i> Tambah Kategori
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Pengeluaran --}}
        <div class="col-12 col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-arrow-down-circle text-danger me-2"></i>Pengeluaran</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @foreach ($categories->where('type', 'expense') as $cat)
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge rounded-circle p-2" style="background:{{ $cat->color }}20">
                                            <i class="{{ $cat->icon }}" style="color:{{ $cat->color }}"></i>
                                        </span>
                                        <div>
                                            <span class="fw-semibold">{{ $cat->name }}</span>
                                            @if ($cat->is_default)
                                                <span class="badge bg-light text-muted ms-1 small">Default</span>
                                            @endif
                                            @if ($cat->children->count())
                                                <small class="text-muted d-block">{{ $cat->children->count() }} sub-kategori</small>
                                            @endif
                                        </div>
                                    </div>
                                    @if (! $cat->is_default)
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('categories.edit', $cat) }}" class="btn btn-sm btn-light">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form method="POST" action="{{ route('categories.destroy', $cat) }}"
                                                  onsubmit="return confirm('Yakin hapus kategori ini?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-light text-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                                {{-- Sub-kategori --}}
                                @if ($cat->children->count())
                                    <ul class="list-unstyled ms-4 mt-1 mb-0">
                                        @foreach ($cat->children as $child)
                                            <li class="d-flex align-items-center gap-1 py-1">
                                                <i class="{{ $child->icon }} small" style="color:{{ $child->color }}"></i>
                                                <small class="text-muted">{{ $child->name }}</small>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        {{-- Pemasukan --}}
        <div class="col-12 col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-arrow-up-circle text-success me-2"></i>Pemasukan</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @foreach ($categories->where('type', 'income') as $cat)
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge rounded-circle p-2" style="background:{{ $cat->color }}20">
                                            <i class="{{ $cat->icon }}" style="color:{{ $cat->color }}"></i>
                                        </span>
                                        <div>
                                            <span class="fw-semibold">{{ $cat->name }}</span>
                                            @if ($cat->is_default)
                                                <span class="badge bg-light text-muted ms-1 small">Default</span>
                                            @endif
                                        </div>
                                    </div>
                                    @if (! $cat->is_default)
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('categories.edit', $cat) }}" class="btn btn-sm btn-light">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form method="POST" action="{{ route('categories.destroy', $cat) }}"
                                                  onsubmit="return confirm('Yakin hapus kategori ini?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-light text-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
