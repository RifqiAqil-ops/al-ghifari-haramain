@extends('layouts.app')

@section('title', 'Laporan Keuangan & Konsolidasi Bisnis Hotel')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark"><i class="bi bi-file-earmark-bar-graph text-success me-2"></i>Laporan Bisnis &amp; Konsolidasi Perusahaan</h4>
            <p class="text-muted small mb-0">Kinerja 3 Lini Bisnis: Haji, Umrah, dan Hotel Akomodasi</p>
        </div>
    </div>

    <!-- Filter Periode -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body py-3">
            <form action="{{ route('hotel.reports.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Tanggal Mulai</label>
                    <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Tanggal Selesai</label>
                    <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-filter me-1"></i> Terapkan Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 3 Business Lines Consolidation Table -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-diagram-3 text-primary me-2"></i>Kinerja 3 Lini Bisnis (Pelaporan Konsolidasi IDR)</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0" style="font-size: 13.5px;">
                    <thead class="table-light">
                        <tr>
                            <th>Lini Bisnis Perusahaan</th>
                            <th class="text-end">Total Penjualan / Omzet</th>
                            <th class="text-end">Kas Masuk (Cash In)</th>
                            <th class="text-end">Piutang Berjalan</th>
                            <th class="text-end">Biaya / Beban</th>
                            <th class="text-end">Laba Operasional Bersih</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>1. Haji &amp; Umrah</strong></td>
                            <td class="text-end">Rp {{ number_format($consolidated['haji_umrah']['sales_idr'], 0, ',', '.') }}</td>
                            <td class="text-end text-success">Rp {{ number_format($consolidated['haji_umrah']['cash_in_idr'], 0, ',', '.') }}</td>
                            <td class="text-end text-danger">Rp {{ number_format($consolidated['haji_umrah']['receivables_idr'], 0, ',', '.') }}</td>
                            <td class="text-end">Rp {{ number_format($consolidated['haji_umrah']['expenses_idr'], 0, ',', '.') }}</td>
                            <td class="text-end fw-bold">Rp {{ number_format($consolidated['haji_umrah']['profit_idr'], 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>
                                <strong>2. Hotel Business Management</strong><br>
                                <small class="text-muted">(SAR {{ number_format($consolidated['hotel']['sales_sar'], 2) }})</small>
                            </td>
                            <td class="text-end">Rp {{ number_format($consolidated['hotel']['sales_idr'], 0, ',', '.') }}</td>
                            <td class="text-end text-success">Rp {{ number_format($consolidated['hotel']['cash_in_idr'], 0, ',', '.') }}</td>
                            <td class="text-end text-danger">Rp {{ number_format($consolidated['hotel']['receivables_idr'], 0, ',', '.') }}</td>
                            <td class="text-end">Rp {{ number_format($consolidated['hotel']['costs_idr'], 0, ',', '.') }}</td>
                            <td class="text-end fw-bold">Rp {{ number_format($consolidated['hotel']['profit_idr'], 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                    <tfoot class="table-light">
                        <tr class="fs-6">
                            <th>TOTAL KONSOLIDASI PT. AL GHIFARI</th>
                            <th class="text-end fw-bold text-dark">Rp {{ number_format($consolidated['consolidated']['total_revenue_idr'], 0, ',', '.') }}</th>
                            <th class="text-end fw-bold text-success">Rp {{ number_format($consolidated['consolidated']['total_cash_inflow_idr'], 0, ',', '.') }}</th>
                            <th class="text-end fw-bold text-danger">Rp {{ number_format($consolidated['consolidated']['total_receivables_idr'], 0, ',', '.') }}</th>
                            <th class="text-end fw-bold">Rp {{ number_format($consolidated['consolidated']['total_expenses_idr'], 0, ',', '.') }}</th>
                            <th class="text-end fw-bold text-primary">Rp {{ number_format($consolidated['consolidated']['net_operating_profit_idr'], 0, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
