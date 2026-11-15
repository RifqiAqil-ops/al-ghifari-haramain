@extends('layouts.app')

@section('title', 'Inventaris Perlengkapan')
@section('header_title', 'Manajemen Inventaris & Perlengkapan')

@section('content')
<div class="row g-3 mb-4">
    <!-- Stat 1: Total Jenis Barang -->
    <div class="col-sm-6 col-xl-3">
        <div class="card card-custom p-3 h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-forest-green text-royal-gold rounded-3 p-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                    <i class="bi bi-box-seam fs-4"></i>
                </div>
                <div>
                    <span class="text-muted small d-block">Total Jenis Barang</span>
                    <h4 class="fw-bold mb-0 text-dark">{{ $totalItems }} <small class="text-muted fs-6 fw-normal">Item</small></h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Stat 2: Total Stok Tersedia -->
    <div class="col-sm-6 col-xl-3">
        <div class="card card-custom p-3 h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-primary-subtle text-primary rounded-3 p-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                    <i class="bi bi-stack fs-4"></i>
                </div>
                <div>
                    <span class="text-muted small d-block">Total Stok Tersedia</span>
                    <h4 class="fw-bold mb-0 text-dark">{{ number_format($totalAvailableStock, 0, ',', '.') }} <small class="text-muted fs-6 fw-normal">Unit</small></h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Stat 3: Stok Menipis -->
    <div class="col-sm-6 col-xl-3">
        <div class="card card-custom p-3 h-100 {{ $lowStockCount > 0 ? 'border border-danger-subtle bg-danger-subtle bg-opacity-10' : '' }}">
            <div class="d-flex align-items-center gap-3">
                <div class="{{ $lowStockCount > 0 ? 'bg-danger text-white' : 'bg-light text-muted' }} rounded-3 p-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                    <i class="bi bi-exclamation-triangle fs-4"></i>
                </div>
                <div>
                    <span class="text-muted small d-block">Stok Menipis / Kritis</span>
                    <h4 class="fw-bold mb-0 {{ $lowStockCount > 0 ? 'text-danger' : 'text-dark' }}">
                        {{ $lowStockCount }} <small class="fs-6 fw-normal">Item</small>
                    </h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Stat 4: Riwayat Mutasi -->
    <div class="col-sm-6 col-xl-3">
        <div class="card card-custom p-3 h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-info-subtle text-info rounded-3 p-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                    <i class="bi bi-arrow-left-right fs-4"></i>
                </div>
                <div>
                    <span class="text-muted small d-block">Histori Mutasi Stok</span>
                    <h4 class="fw-bold mb-0 text-dark">{{ number_format($totalMutationsCount, 0, ',', '.') }} <small class="text-muted fs-6 fw-normal">Catatan</small></h4>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card card-custom p-4">
    <!-- Header & Action Buttons -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h5 class="fw-bold text-forest-green mb-1">
                <i class="bi bi-boxes text-royal-gold me-2"></i> Daftar Master Inventaris Perlengkapan
            </h5>
            <p class="text-muted small mb-0">Kelola stok masuk, stok keluar, status ketersediaan barang dan batas peringatan stok</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @can('equipment.distribute')
            <a href="{{ route('equipment.distributions.create') }}" class="btn btn-sm btn-forest-green">
                <i class="bi bi-box-arrow-right me-1"></i> Distribusi ke Jamaah
            </a>
            @endcan
            @can('equipment.create')
            <a href="{{ route('equipment.create') }}" class="btn btn-sm btn-outline-forest-green">
                <i class="bi bi-plus-circle me-1"></i> Tambah Barang
            </a>
            <a href="{{ route('equipment.categories.index') }}" class="btn btn-sm btn-light border">
                <i class="bi bi-tags me-1"></i> Kategori
            </a>
            @endcan
            <a href="{{ route('equipment.mutations.index') }}" class="btn btn-sm btn-light border">
                <i class="bi bi-clock-history me-1"></i> Riwayat Mutasi
            </a>
        </div>
    </div>

    <!-- Search & Filter -->
    <form action="{{ route('equipment.index') }}" method="GET" class="row g-2 mb-4 bg-light p-3 rounded-3 border">
        <div class="col-md-4">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-white text-muted border-end-0"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Cari kode atau nama perlengkapan..." value="{{ $search }}">
            </div>
        </div>
        <div class="col-md-3">
            <select name="category_id" class="form-select form-select-sm">
                <option value="">Semua Kategori</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ (string)$categoryId === (string)$cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select name="status" class="form-select form-select-sm">
                <option value="">Semua Status</option>
                <option value="1" {{ $status === '1' ? 'selected' : '' }}>Aktif</option>
                <option value="0" {{ $status === '0' ? 'selected' : '' }}>Nonaktif / Arsip</option>
            </select>
        </div>
        <div class="col-md-3 d-flex align-items-center gap-2">
            <div class="form-check form-switch mb-0">
                <input class="form-check-input" type="checkbox" name="low_stock" value="1" id="lowStockToggle" {{ $lowStock ? 'checked' : '' }} onchange="this.form.submit()">
                <label class="form-check-label small fw-semibold text-danger" for="lowStockToggle">
                    Stok Menipis
                </label>
            </div>
            <button type="submit" class="btn btn-sm btn-forest-green ms-auto px-3">Filter</button>
            @if($search || $categoryId || $lowStock || $status !== null)
                <a href="{{ route('equipment.index') }}" class="btn btn-sm btn-light border" title="Reset Filter">
                    <i class="bi bi-arrow-repeat"></i>
                </a>
            @endif
        </div>
    </form>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table table-custom table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th style="width: 120px;">Kode</th>
                    <th>Nama Perlengkapan</th>
                    <th>Kategori</th>
                    <th class="text-center" style="width: 130px;">Stok Tersedia</th>
                    <th class="text-center" style="width: 100px;">Batas Min.</th>
                    <th class="text-center" style="width: 110px;">Status</th>
                    <th class="text-end" style="width: 170px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($equipments as $item)
                    <tr class="{{ $item->is_low_stock && $item->is_active ? 'table-warning bg-opacity-25' : '' }}">
                        <td>
                            <span class="badge bg-forest-green text-royal-gold font-monospace">{{ $item->code }}</span>
                        </td>
                        <td>
                            <a href="{{ route('equipment.show', $item) }}" class="fw-bold text-dark text-decoration-none d-block">
                                {{ $item->name }}
                            </a>
                            @if($item->description)
                                <small class="text-muted text-truncate d-inline-block" style="max-width: 280px;" title="{{ $item->description }}">{{ $item->description }}</small>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">
                                {{ $item->category?->name ?? 'Tanpa Kategori' }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="fw-bold fs-6 {{ $item->is_low_stock ? 'text-danger' : 'text-forest-green' }}">
                                {{ number_format($item->stock, 0, ',', '.') }}
                            </span>
                            <span class="text-muted small">{{ $item->unit }}</span>
                            @if($item->is_low_stock && $item->is_active)
                                <span class="badge bg-danger d-block mt-1" style="font-size: 0.65rem;">
                                    <i class="bi bi-exclamation-circle me-1"></i>Stok Menipis
                                </span>
                            @endif
                        </td>
                        <td class="text-center text-muted small">
                            {{ $item->min_stock }} {{ $item->unit }}
                        </td>
                        <td class="text-center">
                            @if($item->is_active)
                                <span class="badge badge-subtle-success px-2 py-1">Aktif</span>
                            @else
                                <span class="badge bg-secondary px-2 py-1">Arsip</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('equipment.show', $item) }}" class="btn btn-light border text-forest-green" title="Detail & Riwayat Mutasi">
                                    <i class="bi bi-eye"></i>
                                </a>

                                @can('equipment.stock')
                                <button type="button" class="btn btn-light border text-primary" data-bs-toggle="modal" data-bs-target="#mutateModal{{ $item->id }}" title="Mutasi Stok">
                                    <i class="bi bi-plus-slash-minus"></i>
                                </button>
                                @endcan

                                @can('equipment.edit')
                                <a href="{{ route('equipment.edit', $item) }}" class="btn btn-light border text-muted" title="Edit Barang">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @endcan
                            </div>

                            <!-- Modal Mutasi Cepat -->
                            @can('equipment.stock')
                            <div class="modal fade" id="mutateModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered text-start">
                                    <div class="modal-content">
                                        <form action="{{ route('equipment.stock.mutate', $item) }}" method="POST">
                                            @csrf
                                            <div class="modal-header">
                                                <h6 class="modal-title fw-bold text-forest-green">
                                                    <i class="bi bi-box-arrow-in-down me-1 text-royal-gold"></i> Mutasi Stok: {{ $item->name }}
                                                </h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="p-2 mb-3 rounded bg-light border d-flex justify-content-between align-items-center">
                                                    <span class="small text-muted">Stok Tersedia Saat Ini:</span>
                                                    <span class="fw-bold text-forest-green fs-6">{{ $item->stock }} {{ $item->unit }}</span>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label small fw-semibold">Jenis Mutasi <span class="text-danger">*</span></label>
                                                    <select name="type" class="form-select form-select-sm" required onchange="toggleAdjustmentMode(this, {{ $item->id }})">
                                                        <option value="in">Stok Masuk (Restock / Pembelian Baru)</option>
                                                        <option value="damaged">Stok Rusak / Hilang (Pengurangan)</option>
                                                        <option value="adjustment">Penyesuaian Manual (Stock Opname)</option>
                                                    </select>
                                                </div>

                                                <div class="mb-3 d-none" id="adjustmentModeDiv{{ $item->id }}">
                                                    <label class="form-label small fw-semibold">Metode Penyesuaian</label>
                                                    <div class="d-flex gap-3">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="adjustment_mode" value="add" id="adjAdd{{ $item->id }}" checked>
                                                            <label class="form-check-label small" for="adjAdd{{ $item->id }}">Tambah Stok (+)</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="adjustment_mode" value="subtract" id="adjSub{{ $item->id }}">
                                                            <label class="form-check-label small" for="adjSub{{ $item->id }}">Kurangi Stok (-)</label>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label small fw-semibold">Jumlah (Quantity) <span class="text-danger">*</span></label>
                                                    <div class="input-group input-group-sm">
                                                        <input type="number" name="quantity" class="form-control" min="1" required placeholder="Contoh: 10">
                                                        <span class="input-group-text bg-light text-muted">{{ $item->unit }}</span>
                                                    </div>
                                                </div>

                                                <div class="mb-2">
                                                    <label class="form-label small fw-semibold">Keterangan / Alasan Mutasi</label>
                                                    <textarea name="notes" rows="2" class="form-control form-control-sm" placeholder="Contoh: Penerimaan pesanan batch 2 dari vendor, barang cacat jahitan, dll."></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Tutup</button>
                                                <button type="submit" class="btn btn-sm btn-forest-green">Simpan Mutasi</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-box-seam fs-1 d-block mb-2 text-muted"></i>
                            Belum ada data perlengkapan yang ditemukan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $equipments->links() }}
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleAdjustmentMode(selectEl, itemId) {
    const adjDiv = document.getElementById('adjustmentModeDiv' + itemId);
    if (adjDiv) {
        if (selectEl.value === 'adjustment') {
            adjDiv.classList.remove('d-none');
        } else {
            adjDiv.classList.add('d-none');
        }
    }
}
</script>
@endpush
