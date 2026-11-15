@extends('layouts.app')

@section('title', 'Audit & Riwayat Mutasi Stok Perlengkapan')
@section('header_title', 'Audit Mutasi Stok Perlengkapan')

@section('content')
<div class="card card-custom">
    <div class="card-body p-4">
        <!-- Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
            <div>
                <h5 class="fw-bold mb-1 text-dark">Log Audit Mutasi Stok</h5>
                <p class="text-muted small mb-0">Catatan riwayat lengkap pergerakan stok masuk, keluar (distribusi), rusak, dan penyesuaian</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('equipment.index') }}" class="btn btn-outline-forest">
                    <i class="bi bi-box-seam me-1"></i> Data Inventaris
                </a>
                <a href="{{ route('equipment.distributions.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-box-arrow-right me-1"></i> Data Distribusi
                </a>
            </div>
        </div>

        <!-- Filter Form -->
        <form action="{{ route('equipment.mutations') }}" method="GET" class="row g-2 mb-4">
            <div class="col-md-3">
                <select name="equipment_id" class="form-select">
                    <option value="">-- Semua Perlengkapan --</option>
                    @foreach($equipments as $eq)
                        <option value="{{ $eq->id }}" {{ $equipmentId == $eq->id ? 'selected' : '' }}>
                            {{ $eq->name }} ({{ $eq->code }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="type" class="form-select">
                    <option value="">-- Semua Jenis Mutasi --</option>
                    <option value="in" {{ $type === 'in' ? 'selected' : '' }}>Masuk / Restock</option>
                    <option value="out" {{ $type === 'out' ? 'selected' : '' }}>Keluar / Distribusi</option>
                    <option value="damaged" {{ $type === 'damaged' ? 'selected' : '' }}>Rusak</option>
                    <option value="adjustment" {{ $type === 'adjustment' ? 'selected' : '' }}>Penyesuaian</option>
                </select>
            </div>
            <div class="col-md-4">
                <div class="input-group">
                    <input type="date" name="start_date" class="form-control" title="Dari Tanggal" value="{{ $startDate }}">
                    <span class="input-group-text bg-light text-muted">-</span>
                    <input type="date" name="end_date" class="form-control" title="Sampai Tanggal" value="{{ $endDate }}">
                </div>
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-forest text-white w-100" title="Terapkan Filter">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
                @if($equipmentId || $type || $startDate || $endDate)
                    <a href="{{ route('equipment.mutations') }}" class="btn btn-outline-danger" title="Reset Filter">
                        <i class="bi bi-x-circle"></i>
                    </a>
                @endif
            </div>
        </form>

        <!-- Table -->
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width: 140px;">Waktu & Tanggal</th>
                        <th>Barang Perlengkapan</th>
                        <th style="width: 120px;" class="text-center">Jenis Mutasi</th>
                        <th style="width: 100px;" class="text-center">Jumlah</th>
                        <th style="width: 150px;" class="text-center">Perubahan Stok</th>
                        <th>Keterangan / Referensi</th>
                        <th style="width: 140px;">Dicatat Oleh</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mutations as $mut)
                        <tr>
                            <td>
                                <span class="small fw-semibold text-dark">{{ $mut->created_at ? $mut->created_at->format('d/m/Y') : '-' }}</span>
                                <small class="d-block text-muted">{{ $mut->created_at ? $mut->created_at->format('H:i') : '' }} WIB</small>
                            </td>
                            <td>
                                @if($mut->equipment)
                                    <a href="{{ route('equipment.show', $mut->equipment) }}" class="fw-semibold text-decoration-none text-dark">
                                        {{ $mut->equipment->name }}
                                    </a>
                                    <div class="d-flex align-items-center gap-1 mt-1">
                                        <small class="text-muted"><code>{{ $mut->equipment->code }}</code></small>
                                        <span class="badge bg-light text-secondary border" style="font-size: 0.65rem;">
                                            {{ $mut->equipment->category->name ?? 'Umum' }}
                                        </span>
                                    </div>
                                @else
                                    <span class="text-muted fst-italic">Barang telah dihapus</span>
                                @endif
                            </td>
                            <td class="text-center">
                                {!! $mut->type_badge !!}
                            </td>
                            <td class="text-center fw-bold fs-6 {{ in_array($mut->type, ['out', 'damaged']) ? 'text-danger' : 'text-success' }}">
                                {{ in_array($mut->type, ['out', 'damaged']) ? '-' : '+' }}{{ $mut->quantity }}
                                <small class="text-muted fw-normal d-block" style="font-size: 0.75rem;">{{ $mut->equipment->unit ?? 'pcs' }}</small>
                            </td>
                            <td class="text-center">
                                <span class="text-muted small">{{ $mut->stock_before }}</span>
                                <i class="bi bi-arrow-right mx-1 text-secondary"></i>
                                <span class="fw-bold text-dark">{{ $mut->stock_after }}</span>
                            </td>
                            <td>
                                <span class="small text-dark">{{ $mut->notes ?? '-' }}</span>
                                @if($mut->reference_type === 'distribution' && $mut->reference_id)
                                    <div class="mt-1">
                                        <a href="{{ route('equipment.distributions.show', $mut->reference_id) }}" class="badge bg-primary-subtle text-primary border border-primary-subtle text-decoration-none">
                                            <i class="bi bi-box-arrow-up-right me-1"></i> Lihat Distribusi #{{ $mut->reference_id }}
                                        </a>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <span class="small text-dark fw-semibold">{{ $mut->creator->name ?? 'Sistem' }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                                Belum ada catatan mutasi stok yang sesuai.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($mutations->hasPages())
            <div class="mt-4 d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    Menampilkan {{ $mutations->firstItem() }} - {{ $mutations->lastItem() }} dari total {{ $mutations->total() }} data mutasi
                </small>
                <div>
                    {{ $mutations->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
