@extends('layouts.app')

@section('title', 'Tambah Perlengkapan')
@section('header_title', 'Tambah Master Perlengkapan')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card card-custom p-4">
            <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-bottom">
                <div>
                    <h5 class="fw-bold text-forest-green mb-1">
                        <i class="bi bi-plus-circle text-royal-gold me-2"></i> Formulir Tambah Perlengkapan
                    </h5>
                    <p class="text-muted small mb-0">Daftarkan item perlengkapan baru untuk inventaris dan distribusi jamaah</p>
                </div>
                <a href="{{ route('equipment.index') }}" class="btn btn-sm btn-light border">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>

            <form action="{{ route('equipment.store') }}" method="POST">
                @csrf

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Kode Barang <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control form-control-sm font-monospace @error('code') is-invalid @enderror" value="{{ old('code') }}" placeholder="Contoh: EQP-KPR" required>
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted" style="font-size: 0.72rem;">Kode unik identitas barang</small>
                    </div>

                    <div class="col-md-8">
                        <label class="form-label small fw-semibold">Nama Perlengkapan <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control form-control-sm @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Contoh: Koper Bagasi 24 Inch" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Kategori Barang</label>
                        <select name="category_id" class="form-select form-select-sm @error('category_id') is-invalid @enderror">
                            <option value="">Pilih Kategori (Opsional)</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Satuan Barang <span class="text-danger">*</span></label>
                        <input type="text" name="unit" class="form-control form-control-sm @error('unit') is-invalid @enderror" value="{{ old('unit', 'pcs') }}" placeholder="pcs, set, potong, lembar, dll." required>
                        @error('unit')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Stok Awal Fisik <span class="text-danger">*</span></label>
                        <input type="number" name="initial_stock" class="form-control form-control-sm @error('initial_stock') is-invalid @enderror" value="{{ old('initial_stock', 0) }}" min="0" required>
                        @error('initial_stock')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted" style="font-size: 0.72rem;">Jumlah unit fisik yang tersedia saat ini di gudang</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Ambang Batas Minimum (Alert) <span class="text-danger">*</span></label>
                        <input type="number" name="min_stock" class="form-control form-control-sm @error('min_stock') is-invalid @enderror" value="{{ old('min_stock', 5) }}" min="0" required>
                        @error('min_stock')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted" style="font-size: 0.72rem;">Peringatan otomatis muncul bila stok mencapai atau di bawah angka ini</small>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Keterangan / Spesifikasi Barang</label>
                    <textarea name="description" rows="3" class="form-control form-control-sm @error('description') is-invalid @enderror" placeholder="Deskripsi bahan, ukuran, vendor, atau catatan penyimpanan...">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActiveCheck" checked>
                        <label class="form-check-label small fw-semibold" for="isActiveCheck">
                            Status Barang Aktif (Tersedia untuk distribusi jamaah)
                        </label>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('equipment.index') }}" class="btn btn-sm btn-light border px-4">Batal</a>
                    <button type="submit" class="btn btn-sm btn-forest-green px-4">
                        <i class="bi bi-save me-1"></i> Simpan Perlengkapan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
