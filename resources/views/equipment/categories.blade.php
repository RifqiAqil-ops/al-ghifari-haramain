@extends('layouts.app')

@section('title', 'Kategori Perlengkapan')
@section('header_title', 'Kelola Kategori Perlengkapan')

@section('content')
<div class="row g-4">
    <!-- Kolom Kiri: Form Tambah Kategori -->
    <div class="col-lg-4">
        <div class="card card-custom p-4">
            <h6 class="fw-bold text-forest-green mb-3">
                <i class="bi bi-tag-fill text-royal-gold me-2"></i> Tambah Kategori Baru
            </h6>
            <form action="{{ route('equipment.categories.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Nama Kategori <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control form-control-sm @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Contoh: Koper & Tas, Aksesoris..." required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Deskripsi</label>
                    <textarea name="description" rows="3" class="form-control form-control-sm @error('description') is-invalid @enderror" placeholder="Keterangan singkat kelompok barang...">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-sm btn-forest-green w-100">
                    <i class="bi bi-plus-circle me-1"></i> Simpan Kategori
                </button>
            </form>
        </div>
    </div>

    <!-- Kolom Kanan: Tabel Kategori -->
    <div class="col-lg-8">
        <div class="card card-custom p-4">
            <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom">
                <div>
                    <h6 class="fw-bold text-forest-green mb-1">
                        <i class="bi bi-tags text-royal-gold me-2"></i> Daftar Kategori Perlengkapan
                    </h6>
                    <p class="text-muted small mb-0">Kelompok kategori untuk memudahkan klasifikasi dan pelaporan stok barang</p>
                </div>
                <a href="{{ route('equipment.index') }}" class="btn btn-sm btn-light border">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Inventaris
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-custom table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Nama Kategori</th>
                            <th>Deskripsi</th>
                            <th class="text-center" style="width: 120px;">Jumlah Barang</th>
                            <th class="text-end" style="width: 120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $cat)
                            <tr>
                                <td class="fw-bold text-dark">
                                    {{ $cat->name }}
                                </td>
                                <td class="small text-muted">
                                    {{ $cat->description ?? '-' }}
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-forest-green border font-monospace">
                                        {{ $cat->equipments_count }} Item
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-light border text-muted" data-bs-toggle="modal" data-bs-target="#editCatModal{{ $cat->id }}" title="Edit Kategori">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        @if($cat->equipments_count == 0)
                                            <button type="button" class="btn btn-light border text-danger" data-bs-toggle="modal" data-bs-target="#deleteCatModal{{ $cat->id }}" title="Hapus Kategori">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        @endif
                                    </div>

                                    <!-- Modal Edit Kategori -->
                                    <div class="modal fade" id="editCatModal{{ $cat->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered text-start">
                                            <div class="modal-content">
                                                <form action="{{ route('equipment.categories.update', $cat) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-header">
                                                        <h6 class="modal-title fw-bold text-forest-green">Edit Kategori: {{ $cat->name }}</h6>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label small fw-semibold">Nama Kategori <span class="text-danger">*</span></label>
                                                            <input type="text" name="name" class="form-control form-control-sm" value="{{ old('name', $cat->name) }}" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label small fw-semibold">Deskripsi</label>
                                                            <textarea name="description" rows="3" class="form-control form-control-sm">{{ old('description', $cat->description) }}</textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-sm btn-forest-green">Simpan Perubahan</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Modal Delete Kategori -->
                                    @if($cat->equipments_count == 0)
                                    <div class="modal fade" id="deleteCatModal{{ $cat->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered text-start">
                                            <div class="modal-content">
                                                <form action="{{ route('equipment.categories.destroy', $cat) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <div class="modal-header">
                                                        <h6 class="modal-title fw-bold text-danger">Hapus Kategori</h6>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p class="small mb-0">Apakah Anda yakin ingin menghapus kategori <strong>{{ $cat->name }}</strong>?</p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-sm btn-danger">Hapus Kategori</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">
                                    Belum ada kategori perlengkapan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $categories->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
