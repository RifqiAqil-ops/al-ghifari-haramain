@extends('layouts.app')

@section('title', 'Pusat Laporan & Analitik - Al Ghifari Haramain')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Pusat Laporan Operasional & Keuangan</h4>
        <p class="text-muted small mb-0">Akses laporan terpadu, rekapitulasi data jamaah, keuangan, dan keberangkatan dalam format PDF dan Excel</p>
    </div>
</div>

<div class="row g-4">
    <!-- Card 1: Laporan Pembayaran Masuk -->
    <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm rounded-3 h-100 p-3 hover-shadow transition">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="stat-icon bg-success-subtle text-success p-3 rounded-3">
                    <i class="bi bi-cash-stack fs-3"></i>
                </div>
                <div>
                    <h6 class="fw-bold text-dark mb-0">Laporan Kas Pembayaran</h6>
                    <span class="text-muted small">Mutasi kas masuk pelunasan jamaah</span>
                </div>
            </div>
            <p class="small text-muted mb-4">
                Rekapitulasi penerimaan kas jamaah harian, bulanan, atau tahunan dengan filter metode transfer / tunai.
            </p>
            <div class="mt-auto d-flex gap-2">
                <a href="{{ route('reports.payments') }}" class="btn btn-outline-success btn-sm w-100">
                    <i class="bi bi-eye me-1"></i> Buka Laporan
                </a>
                <a href="{{ route('reports.payments', ['export' => 'excel']) }}" class="btn btn-success btn-sm" title="Download Excel">
                    <i class="bi bi-file-earmark-excel"></i>
                </a>
                <a href="{{ route('reports.payments', ['export' => 'pdf']) }}" class="btn btn-danger btn-sm" title="Download PDF">
                    <i class="bi bi-file-pdf"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Card 2: Laporan Tunggakan & Piutang -->
    <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm rounded-3 h-100 p-3 hover-shadow transition">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="stat-icon bg-warning-subtle text-warning p-3 rounded-3">
                    <i class="bi bi-hourglass-bottom fs-3"></i>
                </div>
                <div>
                    <h6 class="fw-bold text-dark mb-0">Laporan Piutang & Sisa Tagihan</h6>
                    <span class="text-muted small">Monitoring tagihan jatuh tempo</span>
                </div>
            </div>
            <p class="small text-muted mb-4">
                Daftar jamaah dengan sisa tagihan paket yang belum lunas beserta tanggal jatuh tempo untuk follow up pembayaran.
            </p>
            <div class="mt-auto d-flex gap-2">
                <a href="{{ route('reports.receivables') }}" class="btn btn-outline-warning btn-sm w-100">
                    <i class="bi bi-eye me-1"></i> Buka Laporan
                </a>
                <a href="{{ route('reports.receivables', ['export' => 'excel']) }}" class="btn btn-success btn-sm" title="Download Excel">
                    <i class="bi bi-file-earmark-excel"></i>
                </a>
                <a href="{{ route('reports.receivables', ['export' => 'pdf']) }}" class="btn btn-danger btn-sm" title="Download PDF">
                    <i class="bi bi-file-pdf"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Card 3: Laporan Laba Rugi Operasional -->
    <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm rounded-3 h-100 p-3 hover-shadow transition">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="stat-icon bg-primary-subtle text-primary p-3 rounded-3">
                    <i class="bi bi-graph-up-arrow fs-3"></i>
                </div>
                <div>
                    <h6 class="fw-bold text-dark mb-0">Laporan Laba Rugi</h6>
                    <span class="text-muted small">Pemasukan vs Beban Operasional</span>
                </div>
            </div>
            <p class="small text-muted mb-4">
                Laporan laba bersih travel membandingkan total pembayaran masuk dengan pengeluaran tiket, hotel, visa, dan logistik.
            </p>
            <div class="mt-auto d-flex gap-2">
                <a href="{{ route('reports.profit-loss') }}" class="btn btn-outline-primary btn-sm w-100">
                    <i class="bi bi-eye me-1"></i> Buka Laporan
                </a>
                <a href="{{ route('reports.profit-loss', ['export' => 'excel']) }}" class="btn btn-success btn-sm" title="Download Excel">
                    <i class="bi bi-file-earmark-excel"></i>
                </a>
                <a href="{{ route('reports.profit-loss', ['export' => 'pdf']) }}" class="btn btn-danger btn-sm" title="Download PDF">
                    <i class="bi bi-file-pdf"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Card 4: Laporan Keberangkatan Rombongan -->
    <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm rounded-3 h-100 p-3 hover-shadow transition">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="stat-icon bg-info-subtle text-info p-3 rounded-3">
                    <i class="bi bi-airplane fs-3"></i>
                </div>
                <div>
                    <h6 class="fw-bold text-dark mb-0">Laporan Keberangkatan</h6>
                    <span class="text-muted small">Realisasi kuota & manifest per tahun</span>
                </div>
            </div>
            <p class="small text-muted mb-4">
                Rekapitulasi seluruh jadwal penerbangan, okupansi kursi, dan status kepulangan rombongan jamaah tahunan.
            </p>
            <div class="mt-auto d-flex gap-2">
                <a href="{{ route('reports.departures') }}" class="btn btn-outline-info btn-sm w-100">
                    <i class="bi bi-eye me-1"></i> Buka Laporan
                </a>
                <a href="{{ route('reports.departures', ['export' => 'excel']) }}" class="btn btn-success btn-sm" title="Download Excel">
                    <i class="bi bi-file-earmark-excel"></i>
                </a>
                <a href="{{ route('reports.departures', ['export' => 'pdf']) }}" class="btn btn-danger btn-sm" title="Download PDF">
                    <i class="bi bi-file-pdf"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Card 5: Laporan Database Jamaah -->
    <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm rounded-3 h-100 p-3 hover-shadow transition">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="stat-icon bg-secondary-subtle text-dark p-3 rounded-3">
                    <i class="bi bi-people fs-3"></i>
                </div>
                <div>
                    <h6 class="fw-bold text-dark mb-0">Laporan Database Jamaah</h6>
                    <span class="text-muted small">Master data profil jamaah</span>
                </div>
            </div>
            <p class="small text-muted mb-4">
                Ekspor seluruh data jamaah terdaftar lengkap dengan nomor paspor, NIK, alamat, dan nomor kontak keluarga.
            </p>
            <div class="mt-auto d-flex gap-2">
                <a href="{{ route('jamaahs.export-excel') }}" class="btn btn-outline-success btn-sm w-100">
                    <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
                </a>
                <a href="{{ route('jamaahs.export-pdf') }}" class="btn btn-danger btn-sm" title="Download PDF">
                    <i class="bi bi-file-pdf"></i>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
