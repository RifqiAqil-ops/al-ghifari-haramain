@extends('layouts.app')

@section('title', 'Edit Perlengkapan')
@section('header_title', 'Edit Data Perlengkapan')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card card-custom p-4">
            <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-bottom">
                <div>
                    <h5 class="fw-bold text-forest-green mb-1">
                        <i class="bi bi-pencil-square text-royal-gold me-2"></i> Edit Perlengkapan: {{ $equipment->name }}
                    </h5>
                    <p class="text-muted small mb-0">Perbarui spesifikasi, ambang batas peringatan, atau kategori barang</p>
                </div>
                <a href="{{ route('equipment.index') }}" class="btn btn-sm btn-light border">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>

            <form action="{{ route('equipment.update', $equipment) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Kode Barang <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control form-control-sm font-monospace @error('code') is-invalid @enderror" value="{{ old('code', $equipment->code) }}" required>
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-8">
                        <label class="form-label small fw-semibold">Nama Perlengkapan <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control form-control-sm @error('name') is-invalid @enderror" value="{{ old('name', $equipment->name) }}" required>
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
                                <option value="{{ $cat->id }}" {{ old('category_id', $equipment->category_id) == $cat->id ? 'selected' : '' }}>
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
                        <input type="text" name="unit" class="form-control form-control-sm @error('unit') is-invalid @enderror" value="{{ old('unit', $equipment->unit) }}" required>
                        @error('unit')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Stok Saat Ini (Tersedia)</label>
                        <input type="text" class="form-control form-control-sm bg-light fw-bold text-forest-green" value="{{ $equipment->stock }} {{ $equipment->unit }}" disabled>
                        <small class="text-muted" style="font-size: 0.72rem;">Perubahan kuantitas fisik wajib melalui menu <strong>Mutasi Stok</strong>.</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Ambang Batas Minimum (Alert) <span class="text-danger">*</span></label>
                        <input type="number" name="min_stock" class="form-control form-control-sm @error('min_stock') is-invalid @enderror" value="{{ old('min_stock', $equipment->min_stock) }}" min="0" required>
                        @error('min_stock')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted" style="font-size: 0.72rem;">Peringatan otomatis muncul jika stok &le; angka ini</small>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Keterangan / Spesifikasi Barang</label>
                    <textarea name="description" rows="3" class="form-control form-control-sm @error('description') is-invalid @enderror">{{ old('description', $equipment->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActiveCheck" {{ old('is_active', $equipment->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label small fw-semibold" for="isActiveCheck">
                            Status Barang Aktif (Tersedia untuk distribusi jamaah)
                        </label>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        @can('equipment.delete')
                        <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteEquipmentModal">
                            <i class="bi bi-trash me-1"></i> Hapus / Arsipkan
                        </button>
                        @endcan
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('equipment.index') }}" class="btn btn-sm btn-light border px-4">Batal</a>
                        <button type="submit" class="btn btn-sm btn-forest-green px-4">
                            <i class="bi bi-save me-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Delete -->
@can('equipment.delete')
<div class="modal fade" id="deleteEquipmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('equipment.destroy', $equipment) }}" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h6 class="modal-title fw-bold text-danger">Konfirmasi Hapus Perlengkapan</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-start">
                    <p class="small mb-2">Apakah Anda yakin ingin menghapus barang <strong>{{ $equipment->name }}</strong> ({{ $equipment->code }})?</p>
                    <p class="small text-muted mb-0">Catatan: Jika barang sudah memiliki riwayat distribusi ke jamaah, sistem akan secara otomatis mengarsipkan (menonaktifkan) barang tanpa menghapus data historisnya.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-danger">Hapus / Arsipkan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan
@endsection
