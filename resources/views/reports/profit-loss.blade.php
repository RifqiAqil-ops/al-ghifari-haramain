@extends('layouts.app')

@section('title', 'Laporan Laba Rugi Operasional - Al Ghifari Haramain')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <a href="{{ route('reports.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Pusat Laporan
        </a>
        <h4 class="fw-bold mb-1 text-dark">Laporan Laba Rugi Operasional Travel</h4>
        <p class="text-muted small mb-0">Periode: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('reports.profit-loss', ['start_date' => $startDate, 'end_date' => $endDate, 'export' => 'excel']) }}" class="btn btn-success shadow-sm">
            <i class="bi bi-file-earmark-excel me-1"></i> Unduh Excel
        </a>
        <a href="{{ route('reports.profit-loss', ['start_date' => $startDate, 'end_date' => $endDate, 'export' => 'pdf']) }}" class="btn btn-danger shadow-sm">
            <i class="bi bi-file-earmark-pdf me-1"></i> Unduh PDF
        </a>
    </div>
</div>

<!-- Filter Box -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
        <form action="{{ route('reports.profit-loss') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-4">
                <label class="form-label small text-muted mb-1">Mulai Tanggal</label>
                <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate }}">
            </div>
            <div class="col-md-4">
                <label class="form-label small text-muted mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate }}">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary btn-sm w-100">
                    <i class="bi bi-filter me-1"></i> Terapkan Periode
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Statement Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white border-start border-4 border-success">
            <span class="text-muted small fw-semibold">Total Pendapatan (Kas Masuk)</span>
            <h4 class="fw-bold text-success mb-0 mt-1">Rp {{ number_format($totalIncome, 0, ',', '.') }}</h4>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white border-start border-4 border-danger">
            <span class="text-muted small fw-semibold">Total Beban Operasional</span>
            <h4 class="fw-bold text-danger mb-0 mt-1">Rp {{ number_format($totalExpense, 0, ',', '.') }}</h4>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white border-start border-4 {{ $netProfit >= 0 ? 'border-primary' : 'border-danger' }}">
            <span class="text-muted small fw-semibold">Laba Bersih Operasional</span>
            <h4 class="fw-bold {{ $netProfit >= 0 ? 'text-primary' : 'text-danger' }} mb-0 mt-1">Rp {{ number_format($netProfit, 0, ',', '.') }}</h4>
        </div>
    </div>
</div>

<!-- Breakdown Table -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white border-bottom py-3">
        <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-journal-text me-1"></i> Rincian Beban Operasional per Kategori</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 40px;">No</th>
                        <th>Kategori Pengeluaran</th>
                        <th class="text-center">Jumlah Transaksi</th>
                        <th class="text-end">Total Biaya (Rp)</th>
                        <th class="text-end">Porsi Beban (%)</th>
                    </tr>
                </thead>
                <tbody>
                    @php $idx = 1; @endphp
                    @forelse($expensesByCategory as $catName => $expGroup)
                        @php
                            $catTotal = $expGroup->sum('amount');
                            $catPct = $totalExpense > 0 ? round(($catTotal / $totalExpense) * 100, 1) : 0;
                        @endphp
                        <tr>
                            <td>{{ $idx++ }}</td>
                            <td class="fw-semibold text-dark">{{ $catName ?: 'Lain-lain / Umum' }}</td>
                            <td class="text-center">{{ $expGroup->count() }} transaksi</td>
                            <td class="text-end fw-bold text-danger">Rp {{ number_format($catTotal, 0, ',', '.') }}</td>
                            <td class="text-end">
                                <span class="badge bg-light text-dark border">{{ $catPct }}%</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">Belum ada rincian beban operasional pada periode ini.</td>
                        </tr>
                    @endforelse
                    <tr class="table-light fw-bold">
                        <td colspan="3" class="text-end">TOTAL BEBAN OPERASIONAL:</td>
                        <td class="text-end text-danger">Rp {{ number_format($totalExpense, 0, ',', '.') }}</td>
                        <td class="text-end">100%</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
