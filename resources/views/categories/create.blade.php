@extends('layouts.app')
@section('title', 'Tambah Kategori')

@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6">
                <h3>Tambah Kategori</h3>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            @foreach ($errors->all() as $error)<p class="mb-0">{{ $error }}</p>@endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('categories.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Nama <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" required maxlength="100">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tipe <span class="text-danger">*</span></label>
                            <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                                <option value="expense" {{ old('type') === 'expense' ? 'selected' : '' }}>Pengeluaran</option>
                                <option value="income"  {{ old('type') === 'income'  ? 'selected' : '' }}>Pemasukan</option>
                                <option value="both"    {{ old('type') === 'both'    ? 'selected' : '' }}>Keduanya</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Sub-kategori dari</label>
                            <select name="parent_id" class="form-select @error('parent_id') is-invalid @enderror">
                                <option value="">-- Kategori utama (tidak ada induk) --</option>
                                @foreach ($parents as $parent)
                                    <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                                        {{ $parent->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label">Warna</label>
                                <input type="color" name="color" class="form-control form-control-color"
                                       value="{{ old('color', '#6366f1') }}">
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">Icon <small class="text-muted">(Bootstrap Icons)</small></label>
                                <input type="text" name="icon" class="form-control @error('icon') is-invalid @enderror"
                                       value="{{ old('icon', 'bi-tag') }}" placeholder="bi-tag">
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i> Simpan
                            </button>
                            <a href="{{ route('categories.index') }}" class="btn btn-light">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
