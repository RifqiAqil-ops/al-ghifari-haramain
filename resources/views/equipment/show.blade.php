@extends('layouts.app')

@section('title', 'Detail Perlengkapan - ' . $equipment->name)
@section('header_title', 'Detail Perlengkapan & Riwayat Mutasi')

@section('content')
<div class="row g-4">
    <!-- Kolom Kiri: Info Barang & Ringkasan Stok -->
    <div class="col-lg-4">
        <div class="card card-custom p-4 mb-4">
            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                <span class="badge bg-forest-green text-royal-gold font-monospace">{{ $equipment->code }}</span>
                @if($equipment->is_active)
                    <span class="badge badge-subtle-success">Aktif</span>
                @else
                    <span class="badge bg-secondary">Arsip</span>
                @endif
            </div>

            <h5 class="fw-bold text-dark mb-1">{{ $equipment->name }}</h5>
            <p class="text-muted small mb-3">
                <i class="bi bi-tag me-1"></i> {{ $equipment->category?->name ?? 'Tanpa Kategori' }}
            </p>

            @if($equipment->description)
                <div class="p-3 bg-light rounded-3 small text-muted mb-3">
                    {{ $equipment->description }}
                </div>
            @endif

            <hr class="my-3">

            <!-- Stok Tersedia Utama -->
            <div class="p-3 rounded-3 text-center mb-3 {{ $equipment->is_low_stock ? 'bg-danger bg-opacity-10 border border-danger-subtle' : 'bg-light border' }}">
                <span class="small text-muted d-block mb-1">Stok Fisik Tersedia Saat Ini</span>
                <h2 class="fw-bold mb-0 {{ $equipment->is_low_stock ? 'text-danger' : 'text-forest-green' }}">
                    {{ number_format($equipment->stock, 0, ',', '.') }}
                    <small class="fs-6 fw-normal text-muted">{{ $equipment->unit }}</small>
                </h2>
                @if($equipment->is_low_stock)
                    <span class="badge bg-danger mt-2">
                        <i class="bi bi-exclamation-triangle me-1"></i> Peringatan: Stok di bawah batas minimum ({{ $equipment->min_stock }} {{ $equipment->unit }})
                    </span>
                @else
                    <small class="text-muted mt-1 d-block" style="font-size: 0.75rem;">Batas minimum peringatan: {{ $equipment->min_stock }} {{ $equipment->unit }}</small>
                @endif
            </div>

            <!-- Rincian Masuk, Keluar, Rusak -->
            <div class="row g-2 mb-3">
                <div class="col-4">
                    <div class="p-2 border rounded-2 text-center bg-light">
                        <small class="text-muted d-block" style="font-size: 0.7rem;">Total Masuk</small>
                        <strong class="text-success small">+{{ $stats['total_in'] }}</strong>
                    </div>
                </div>
                <div class="col-4">
                    <div class="p-2 border rounded-2 text-center bg-light">
                        <small class="text-muted d-block" style="font-size: 0.7rem;">Terdistribusi</small>
                        <strong class="text-info small">-{{ $stats['total_out'] }}</strong>
                    </div>
                </div>
                <div class="col-4">
                    <div class="p-2 border rounded-2 text-center bg-light">
                        <small class="text-muted d-block" style="font-size: 0.7rem;">Rusak/Hilang</small>
                        <strong class="text-danger small">-{{ $stats['total_damaged'] }}</strong>
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2">
                @can('equipment.stock')
                <button type="button" class="btn btn-sm btn-forest-green" data-bs-toggle="modal" data-bs-target="#mutateModalShow">
                    <i class="bi bi-plus-slash-minus me-1"></i> Catat Mutasi Stok
                </button>
                @endcan

                @can('equipment.edit')
                <a href="{{ route('equipment.edit', $equipment) }}" class="btn btn-sm btn-outline-forest-green">
                    <i class="bi bi-pencil me-1"></i> Edit Informasi Barang
                </a>
                @endcan

                <a href="{{ route('equipment.index') }}" class="btn btn-sm btn-light border">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Inventaris
                </a>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Log Riwayat Mutasi Stok -->
    <div class="col-lg-8">
        <div class="card card-custom p-4">
            <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom">
                <div>
                    <h6 class="fw-bold text-forest-green mb-1">
                        <i class="bi bi-clock-history text-royal-gold me-2"></i> Log Mutasi Stok: {{ $equipment->name }}
                    </h6>
                    <p class="text-muted small mb-0">Audit jejak rekam setiap penambahan, pendistribusian, kerusakan, dan penyesuaian stok</p>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-custom table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width: 130px;">Waktu & Tanggal</th>
                            <th style="width: 120px;">Jenis Mutasi</th>
                            <th class="text-center" style="width: 90px;">Jumlah</th>
                            <th class="text-center" style="width: 120px;">Perubahan Stok</th>
                            <th>Keterangan / Referensi</th>
                            <th style="width: 120px;">Petugas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($mutations as $m)
                            <tr>
                                <td class="small">
                                    <span class="d-block text-dark fw-semibold">{{ $m->created_at->translatedFormat('d M Y') }}</span>
                                    <span class="text-muted" style="font-size: 0.72rem;">{{ $m->created_at->format('H:i') }} WIB</span>
                                </td>
                                <td>
                                    <span class="badge {{ $m->type_badge }} px-2 py-1">
                                        {{ $m->type_label }}
                                    </span>
                                </td>
                                <td class="text-center fw-bold">
                                    @if($m->type === 'in' || ($m->type === 'adjustment' && $m->stock_after >= $m->stock_before))
                                        <span class="text-success">+{{ $m->quantity }}</span>
                                    @else
                                        <span class="text-danger">-{{ $m->quantity }}</span>
                                    @endif
                                    <small class="text-muted fw-normal">{{ $equipment->unit }}</small>
                                </td>
                                <td class="text-center small font-monospace">
                                    <span class="text-muted">{{ $m->stock_before }}</span>
                                    <i class="bi bi-arrow-right mx-1 text-muted"></i>
                                    <span class="fw-bold text-dark">{{ $m->stock_after }}</span>
                                </td>
                                <td class="small text-muted">
                                    {{ $m->notes ?? '-' }}
                                </td>
                                <td class="small text-dark">
                                    <i class="bi bi-person me-1 text-muted"></i>{{ $m->creator?->name ?? 'Sistem' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-2 d-block mb-2 text-muted"></i>
                                    Belum ada catatan mutasi stok untuk barang ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $mutations->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Modal Mutasi Cepat -->
@can('equipment.stock')
<div class="modal fade" id="mutateModalShow" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered text-start">
        <div class="modal-content">
            <form action="{{ route('equipment.stock.mutate', $equipment) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h6 class="modal-title fw-bold text-forest-green">
                        <i class="bi bi-box-arrow-in-down me-1 text-royal-gold"></i> Mutasi Stok: {{ $equipment->name }}
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="p-2 mb-3 rounded bg-light border d-flex justify-content-between align-items-center">
                        <span class="small text-muted">Stok Tersedia Saat Ini:</span>
                        <span class="fw-bold text-forest-green fs-6">{{ $equipment->stock }} {{ $equipment->unit }}</span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Jenis Mutasi <span class="text-danger">*</span></label>
                        <select name="type" class="form-select form-select-sm" required onchange="toggleAdjustmentModeShow(this)">
                            <option value="in">Stok Masuk (Restock / Pembelian Baru)</option>
                            <option value="damaged">Stok Rusak / Hilang (Pengurangan)</option>
                            <option value="adjustment">Penyesuaian Manual (Stock Opname)</option>
                        </select>
                    </div>

                    <div class="mb-3 d-none" id="adjustmentModeDivShow">
                        <label class="form-label small fw-semibold">Metode Penyesuaian</label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="adjustment_mode" value="add" id="adjAddShow" checked>
                                <label class="form-check-label small" for="adjAddShow">Tambah Stok (+)</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="adjustment_mode" value="subtract" id="adjSubShow">
                                <label class="form-check-label small" for="adjSubShow">Kurangi Stok (-)</label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Jumlah (Quantity) <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm">
                            <input type="number" name="quantity" class="form-control" min="1" required placeholder="Contoh: 10">
                            <span class="input-group-text bg-light text-muted">{{ $equipment->unit }}</span>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Keterangan / Alasan Mutasi</label>
                        <textarea name="notes" rows="2" class="form-control form-control-sm" placeholder="Contoh: Pembelian tambahan supplier, barang reject, dll."></textarea>
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
@endsection

@push('scripts')
<script>
function toggleAdjustmentModeShow(selectEl) {
    const adjDiv = document.getElementById('adjustmentModeDivShow');
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
