@extends('layouts.app')
@section('title', 'Edit Kategori')

@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6">
                <h3>Edit Kategori</h3>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-md-5">
            <div class="card">
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            @foreach ($errors->all() as $error)<p class="mb-0">{{ $error }}</p>@endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('categories.update', $category) }}">
                        @csrf @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Nama <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $category->name) }}" required maxlength="100">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tipe <span class="text-danger">*</span></label>
                            <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                                <option value="expense" {{ old('type', $category->type) === 'expense' ? 'selected' : '' }}>Pengeluaran</option>
                                <option value="income"  {{ old('type', $category->type) === 'income'  ? 'selected' : '' }}>Pemasukan</option>
                                <option value="both"    {{ old('type', $category->type) === 'both'    ? 'selected' : '' }}>Keduanya</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Sub-kategori dari</label>
                            <select name="parent_id" class="form-select @error('parent_id') is-invalid @enderror">
                                <option value="">-- Kategori utama --</option>
                                @foreach ($parents as $parent)
                                    <option value="{{ $parent->id }}"
                                        {{ old('parent_id', $category->parent_id) == $parent->id ? 'selected' : '' }}>
                                        {{ $parent->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label">Warna</label>
                                <input type="color" name="color" class="form-control form-control-color"
                                       value="{{ old('color', $category->color) }}">
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">Icon</label>
                                <input type="text" name="icon" class="form-control @error('icon') is-invalid @enderror"
                                       value="{{ old('icon', $category->icon) }}" placeholder="bi-tag">
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i> Perbarui
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
