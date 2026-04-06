@extends('layouts.app')
@section('title', 'Edit Rekening')

@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6">
                <h3>Edit Rekening</h3>
                <p class="text-subtitle text-muted">Perbarui informasi rekening</p>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-md-6">
            <div class="card">
                <div class="card-body">

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            @foreach ($errors->all() as $error)
                                <p class="mb-0">{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('accounts.update', $account) }}">
                        @csrf @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Nama Rekening <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $account->name) }}" required maxlength="100">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tipe <span class="text-danger">*</span></label>
                                <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                                    @foreach ($types as $type)
                                        <option value="{{ $type->value }}"
                                            {{ old('type', $account->type->value) == $type->value ? 'selected' : '' }}>
                                            {{ $type->label() }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Mata Uang <span class="text-danger">*</span></label>
                                <select name="currency" class="form-select @error('currency') is-invalid @enderror" required>
                                    <option value="IDR" {{ old('currency', $account->currency) === 'IDR' ? 'selected' : '' }}>IDR — Rupiah</option>
                                    <option value="USD" {{ old('currency', $account->currency) === 'USD' ? 'selected' : '' }}>USD — Dollar</option>
                                    <option value="SGD" {{ old('currency', $account->currency) === 'SGD' ? 'selected' : '' }}>SGD — Singapore Dollar</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Warna</label>
                                <input type="color" name="color" class="form-control form-control-color"
                                       value="{{ old('color', $account->color) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Icon</label>
                                <input type="text" name="icon" class="form-control @error('icon') is-invalid @enderror"
                                       value="{{ old('icon', $account->icon) }}" placeholder="bi-wallet2">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="description" class="form-control" rows="2" maxlength="255">{{ old('description', $account->description) }}</textarea>
                        </div>

                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                                       {{ old('is_active', $account->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Rekening aktif</label>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i> Perbarui
                            </button>
                            <a href="{{ route('accounts.index') }}" class="btn btn-light">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
