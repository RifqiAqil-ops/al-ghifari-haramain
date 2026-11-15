@extends('layouts.app')

@section('title', 'Detail Distribusi - ' . $distribution->distribution_number)
@section('header_title', 'Tanda Terima Distribusi Perlengkapan')

@section('content')
<div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2 d-print-none">
    <a href="{{ route('equipment.distributions.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Distribusi
    </a>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-dark btn-sm" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Cetak Tanda Terima
        </button>
        @if($distribution->status === 'diserahkan')
            @can('equipment.distribute')
                <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#cancelModal">
                    <i class="bi bi-x-circle me-1"></i> Batalkan Transaksi
                </button>
            @endcan
        @endif
    </div>
</div>

@if($distribution->status === 'dibatalkan')
    <div class="alert alert-danger d-flex align-items-center mb-4 border-0 shadow-sm" role="alert">
        <i class="bi bi-exclamation-triangle-fill fs-3 me-3"></i>
        <div>
            <h6 class="alert-heading fw-bold mb-1">Transaksi Distribusi Ini Telah Dibatalkan</h6>
            <p class="mb-0 small">
                Seluruh stok barang pada transaksi ini telah otomatis dikembalikan ke inventaris gudang.
            </p>
        </div>
    </div>
@endif

<div class="card card-custom print-card border-0 shadow-sm">
    <div class="card-body p-4 p-md-5">
        <!-- Print Header -->
        <div class="border-bottom pb-4 mb-4">
            <div class="row align-items-center">
                <div class="col-8">
                    <h4 class="fw-bold text-forest mb-1">AL GHIFARI HARAMAIN</h4>
                    <p class="text-muted small mb-0">Layanan Umrah & Haji Khusus Terpercaya</p>
                    <h5 class="fw-bold text-dark mt-3 mb-0">BUKTI PENYERAHAN PERLENGKAPAN JAMAAH</h5>
                </div>
                <div class="col-4 text-end">
                    <span class="badge {{ $distribution->status === 'diserahkan' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle' }} fs-6 px-3 py-2 mb-2">
                        {{ strtoupper($distribution->status) }}
                    </span>
                    <div class="fw-bold text-dark fs-5">{{ $distribution->distribution_number }}</div>
                    <small class="text-muted">Tanggal: {{ $distribution->distribution_date ? $distribution->distribution_date->format('d/m/Y') : '-' }}</small>
                </div>
            </div>
        </div>

        <!-- Info Penerima & Penyerah -->
        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="p-3 bg-light rounded-3 h-100">
                    <h6 class="fw-bold text-forest mb-3">
                        <i class="bi bi-person-fill me-1"></i> Data Jamaah / Penerima
                    </h6>
                    <table class="table table-borderless table-sm mb-0">
                        <tr>
                            <td class="text-muted" style="width: 140px;">Nama Jamaah</td>
                            <td class="fw-semibold text-dark">
                                @if($distribution->jamaah)
                                    <a href="{{ route('jamaah.show', $distribution->jamaah) }}" class="text-decoration-none text-forest">
                                        {{ $distribution->jamaah->name }}
                                    </a>
                                @else
                                    <span class="text-muted fst-italic">Data jamaah tidak tersedia</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">NIK</td>
                            <td>{{ $distribution->jamaah->nik ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">No. Telepon / WA</td>
                            <td>{{ $distribution->jamaah->phone ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Nama Penerima Fisik</td>
                            <td class="fw-bold text-dark">{{ $distribution->received_by ?: ($distribution->jamaah->name ?? '-') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="col-md-6">
                <div class="p-3 bg-light rounded-3 h-100">
                    <h6 class="fw-bold text-forest mb-3">
                        <i class="bi bi-info-circle-fill me-1"></i> Rincian Penyerahan
                    </h6>
                    <table class="table table-borderless table-sm mb-0">
                        <tr>
                            <td class="text-muted" style="width: 140px;">Keberangkatan</td>
                            <td>
                                @if($distribution->departure)
                                    <strong>{{ $distribution->departure->departure_code ?? 'DEP-'.$distribution->departure->id }}</strong>
                                    <span class="text-muted">({{ $distribution->departure->package->name ?? 'Paket' }})</span>
                                    <div class="small text-muted">{{ $distribution->departure->departure_date ? $distribution->departure->departure_date->format('d/m/Y') : '' }}</div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Petugas Penyerah</td>
                            <td class="fw-semibold text-dark">{{ $distribution->officer->name ?? 'Petugas' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Waktu Pencatatan</td>
                            <td>{{ $distribution->created_at ? $distribution->created_at->format('d/m/Y H:i') : '-' }} WIB</td>
                        </tr>
                        @if($distribution->notes)
                            <tr>
                                <td class="text-muted">Catatan Transaksi</td>
                                <td class="small">{{ $distribution->notes }}</td>
                            </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        <!-- Tabel Barang yang Diterima -->
        <h6 class="fw-bold text-dark mb-3">
            <i class="bi bi-box-seam me-1 text-forest"></i> Rincian Perlengkapan yang Diserahkan
        </h6>
        <div class="table-responsive mb-4">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width: 45px;" class="text-center">No</th>
                        <th>Kode Barang</th>
                        <th>Nama Perlengkapan</th>
                        <th>Kategori</th>
                        <th style="width: 120px;" class="text-center">Jumlah Diserahkan</th>
                        <th>Keterangan Khusus</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalQty = 0; @endphp
                    @foreach($distribution->items as $idx => $item)
                        @php $totalQty += $item->quantity; @endphp
                        <tr>
                            <td class="text-center">{{ $idx + 1 }}</td>
                            <td><code>{{ $item->equipment->code ?? '-' }}</code></td>
                            <td class="fw-semibold text-dark">
                                @if($item->equipment)
                                    <a href="{{ route('equipment.show', $item->equipment) }}" class="text-decoration-none text-dark">
                                        {{ $item->equipment->name }}
                                    </a>
                                @else
                                    <span class="text-muted">Item telah dihapus</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-secondary border">
                                    {{ $item->equipment->category->name ?? 'Umum' }}
                                </span>
                            </td>
                            <td class="text-center fw-bold fs-6 text-forest">
                                {{ $item->quantity }} {{ $item->equipment->unit ?? 'pcs' }}
                            </td>
                            <td class="small text-muted">{{ $item->notes ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td colspan="4" class="text-end">Total Fisik Barang Diserahkan:</td>
                        <td class="text-center text-forest">{{ $totalQty }} Item</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Tanda Tangan Cetak -->
        <div class="row pt-4 mt-5 text-center d-none d-print-flex">
            <div class="col-6">
                <p class="mb-5 small text-muted">Penerima Barang,</p>
                <div class="border-bottom mx-auto" style="width: 200px;"></div>
                <p class="fw-bold text-dark mt-1 mb-0">{{ $distribution->received_by ?: ($distribution->jamaah->name ?? 'Jamaah') }}</p>
            </div>
            <div class="col-6">
                <p class="mb-5 small text-muted">Petugas Logistik / Penyerah,</p>
                <div class="border-bottom mx-auto" style="width: 200px;"></div>
                <p class="fw-bold text-dark mt-1 mb-0">{{ $distribution->officer->name ?? 'Petugas Al Ghifari' }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Modal Pembatalan Transaksi -->
@if($distribution->status === 'diserahkan')
    @can('equipment.distribute')
        <div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('equipment.distributions.cancel', $distribution) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title fw-bold" id="cancelModalLabel">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>Batalkan Transaksi Distribusi
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <p class="text-dark">
                                Anda akan membatalkan transaksi distribusi <strong>{{ $distribution->distribution_number }}</strong>.
                            </p>
                            <div class="alert alert-warning py-2 small mb-3">
                                <i class="bi bi-info-circle me-1"></i>
                                <strong>Perhatian:</strong> Pembatalan ini akan otomatis mengembalikan seluruh stok barang pada transaksi ini kembali ke stok inventaris.
                            </div>
                            <div class="mb-3">
                                <label for="cancellation_reason" class="form-label fw-semibold">Alasan Pembatalan <span class="text-danger">*</span></label>
                                <textarea name="cancellation_reason" id="cancellation_reason" rows="3" class="form-control" placeholder="Contoh: Kesalahan input nama jamaah / barang batal diambil" required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                            <button type="submit" class="btn btn-danger fw-semibold">
                                <i class="bi bi-x-circle me-1"></i> Ya, Batalkan Transaksi & Kembalikan Stok
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan
@endif

<style>
@media print {
    body {
        background-color: #fff !important;
    }
    .sidebar, .navbar, .d-print-none, .btn {
        display: none !important;
    }
    .content-wrapper {
        margin: 0 !important;
        padding: 0 !important;
    }
    .print-card {
        box-shadow: none !important;
        border: none !important;
    }
}
</style>
@endsection
