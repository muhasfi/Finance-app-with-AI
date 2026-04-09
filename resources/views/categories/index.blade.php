@extends('layouts.app')
@section('title', 'Kategori')

@section('content')
<div class="page-heading">
    <div class="page-title mb-4">
        <div class="row align-items-center">
            <div class="col-12 col-md-6">
                <h3>Manajemen Kategori</h3>
                <p class="text-subtitle text-muted">Kelola kategori pengeluaran dan pemasukan Anda</p>
            </div>
            <div class="col-12 col-md-6 text-md-end">
                <a href="{{ route('categories.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i> Tambah Kategori
                </a>
            </div>
        </div>
    </div>

    <div class="row">

        {{-- Pengeluaran --}}
        <div class="col-12 col-xl-6 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center gap-2 bg-danger bg-opacity-10 border-bottom border-danger border-opacity-25">
                    <div class="avatar bg-danger rounded p-1">
                        <i class="bi bi-arrow-down-circle-fill text-white fs-5"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold text-danger">Pengeluaran</h6>
                        <small class="text-muted">{{ $categories->where('type', 'expense')->count() }} kategori</small>
                    </div>
                </div>
                <div class="card-body p-0">
                    @forelse ($categories->where('type', 'expense') as $cat)
                        <div class="px-3 py-2 border-bottom">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                                        style="width:38px;height:38px;background:{{ $cat->color }}25">
                                        <i class="{{ $cat->icon }}" style="color:{{ $cat->color }};font-size:1.1rem"></i>
                                    </div>
                                    <div>
                                        <p class="mb-0 fw-semibold">{{ $cat->name }}</p>
                                        <div class="d-flex align-items-center gap-1">
                                            @if ($cat->is_default)
                                                <span class="badge bg-secondary bg-opacity-25 text-secondary" style="font-size:0.7rem">Default</span>
                                            @endif
                                            @if ($cat->children->count())
                                                <span class="badge bg-primary bg-opacity-10 text-primary" style="font-size:0.7rem">
                                                    <i class="bi bi-diagram-2 me-1"></i>{{ $cat->children->count() }} sub
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @if (!$cat->is_default)
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('categories.edit', $cat) }}" class="btn btn-sm btn-outline-warning">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form method="POST" action="{{ route('categories.destroy', $cat) }}"
                                            onsubmit="return confirm('Yakin hapus kategori ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                            @if ($cat->children->count())
                                <div class="ms-5 mt-2">
                                    @foreach ($cat->children as $child)
                                        <span class="badge rounded-pill me-1 mb-1"
                                            style="background:{{ $child->color }}20;color:{{ $child->color }};border:1px solid {{ $child->color }}40">
                                            <i class="{{ $child->icon }} me-1"></i>{{ $child->name }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                            <small>Belum ada kategori pengeluaran</small>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Pemasukan --}}
        <div class="col-12 col-xl-6 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center gap-2 bg-success bg-opacity-10 border-bottom border-success border-opacity-25">
                    <div class="avatar bg-success rounded p-1">
                        <i class="bi bi-arrow-up-circle-fill text-white fs-5"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold text-success">Pemasukan</h6>
                        <small class="text-muted">{{ $categories->where('type', 'income')->count() }} kategori</small>
                    </div>
                </div>
                <div class="card-body p-0">
                    @forelse ($categories->where('type', 'income') as $cat)
                        <div class="px-3 py-2 border-bottom">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                                        style="width:38px;height:38px;background:{{ $cat->color }}25">
                                        <i class="{{ $cat->icon }}" style="color:{{ $cat->color }};font-size:1.1rem"></i>
                                    </div>
                                    <div>
                                        <p class="mb-0 fw-semibold">{{ $cat->name }}</p>
                                        @if ($cat->is_default)
                                            <span class="badge bg-secondary bg-opacity-25 text-secondary" style="font-size:0.7rem">Default</span>
                                        @endif
                                    </div>
                                </div>
                                @if (!$cat->is_default)
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('categories.edit', $cat) }}" class="btn btn-sm btn-outline-warning">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form method="POST" action="{{ route('categories.destroy', $cat) }}"
                                            onsubmit="return confirm('Yakin hapus kategori ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                            <small>Belum ada kategori pemasukan</small>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
</div>
@endsection