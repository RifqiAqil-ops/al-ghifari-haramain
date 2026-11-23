@extends('layouts.app')

@section('title', 'Detail Hotel - ' . $hotel->name)

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('hotel.master.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Hotel
            </a>
            <h4 class="fw-bold mb-0 text-dark">{{ $hotel->name }}</h4>
            <p class="text-muted small mb-0">{{ $hotel->city }}, {{ $hotel->country }} &bull; {{ $hotel->category }}</p>
        </div>
        <button type="button" class="btn btn-primary btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#roomTypeModal">
            <i class="bi bi-plus-circle me-1"></i> Tambah Tipe Kamar
        </button>
    </div>

    <!-- Room Types Table -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-door-closed text-primary me-2"></i>Daftar Tipe Kamar &amp; Tarif Standar (SAR)</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 13.5px;">
                    <thead class="table-light">
                        <tr>
                            <th>Tipe Kamar</th>
                            <th class="text-center">Kapasitas Maks</th>
                            <th class="text-end">Tarif Beli Standar (SAR)</th>
                            <th class="text-end">Tarif Jual Standar (SAR)</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($hotel->roomTypes as $rt)
                        <tr>
                            <td><strong>{{ $rt->name }}</strong></td>
                            <td class="text-center">{{ $rt->max_occupancy }} orang</td>
                            <td class="text-end">SAR {{ number_format($rt->default_purchase_rate, 2) }}</td>
                            <td class="text-end fw-bold text-dark">SAR {{ number_format($rt->default_selling_rate, 2) }}</td>
                            <td class="text-center">
                                <span class="badge bg-success-subtle text-success border">Aktif</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">Belum ada tipe kamar untuk hotel ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Add Room Type -->
<div class="modal fade" id="roomTypeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('hotel.master.room-types.store', $hotel) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-dark">Tambah Tipe Kamar Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Nama Tipe Kamar <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="Contoh: Double, Triple, Quad, Penthouse" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Maksimal Penghuni (Orang) <span class="text-danger">*</span></label>
                        <input type="number" name="max_occupancy" class="form-control" value="2" min="1" max="10" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Tarif Beli Standar (SAR)</label>
                        <input type="number" step="0.01" name="default_purchase_rate" class="form-control" value="700.00">
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold small">Tarif Jual Standar (SAR) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="default_selling_rate" class="form-control" value="850.00" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-1"></i> Simpan Tipe Kamar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
