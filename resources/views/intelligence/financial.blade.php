@extends('layouts.app')

@section('title', 'Smart Financial Intelligence')

@section('content')
<div class="container-fluid py-3">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h4 mb-1 fw-bold text-dark">
                <i class="bi bi-graph-up-arrow text-primary me-2"></i>Smart Financial Intelligence
            </h2>
            <p class="text-muted small mb-0">Audit Otomatis Anomali Biaya, Indikasi Pembayaran Ganda, dan Analisis Umur Piutang</p>
        </div>
        <div class="badge bg-light text-dark border px-3 py-2">
            <i class="bi bi-shield-check text-success me-1"></i> Perhitungan Deterministik SQL &bull; FinanceService
        </div>
    </div>

    <!-- Health Indicators Bar -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3">
                <span class="text-muted small fw-semibold">Status Kesehatan Finansial</span>
                <div class="d-flex align-items-baseline mt-1">
                    <h3 class="fw-bold mb-0 text-{{ $indicators['badge_class'] }}">{{ $indicators['health_score'] }}</h3>
                    <span class="text-muted ms-1">/100</span>
                </div>
                <span class="badge bg-{{ $indicators['badge_class'] }} mt-2 d-inline-block">{{ $indicators['health_status'] }}</span>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3">
                <span class="text-muted small fw-semibold">Tingkat Penagihan (Collection Rate)</span>
                <h3 class="fw-bold mb-0 text-dark mt-1">{{ $indicators['collection_rate'] }}%</h3>
                <small class="text-muted mt-2 d-block">
                    Terbayar: Rp {{ number_format($indicators['total_paid'], 0, ',', '.') }}
                </small>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3">
                <span class="text-muted small fw-semibold">Net Operating Cash Flow</span>
                <h3 class="fw-bold mb-0 mt-1 {{ $indicators['net_cash_flow'] >= 0 ? 'text-success' : 'text-danger' }}">
                    Rp {{ number_format($indicators['net_cash_flow'], 0, ',', '.') }}
                </h3>
                <small class="text-muted mt-2 d-block">
                    Margin: {{ $indicators['net_margin'] }}%
                </small>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3">
                <span class="text-muted small fw-semibold">Sisa Piutang Berjalan</span>
                <h3 class="fw-bold mb-0 text-danger mt-1">
                    Rp {{ number_format($indicators['total_outstanding'], 0, ',', '.') }}
                </h3>
                <small class="text-muted mt-2 d-block">
                    Total Invoice: Rp {{ number_format($indicators['total_invoiced'], 0, ',', '.') }}
                </small>
            </div>
        </div>
    </div>

    <!-- Duplicate Payments Alert -->
    @if(!empty($duplicatePayments))
    <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4 p-3">
        <div class="d-flex align-items-center mb-2">
            <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
            <h6 class="mb-0 fw-bold">Peringatan: Terdeteksi {{ count($duplicatePayments) }} Potensi Pembayaran Ganda (Double Payment)</h6>
        </div>
        <p class="small mb-3">Sistem menemukan transaksi pembayaran dengan nominal identik pada invoice yang sama dalam rentang waktu $\le 48$ jam.</p>
        <div class="table-responsive bg-white rounded-2">
            <table class="table table-sm table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Invoice</th>
                        <th>Jamaah</th>
                        <th>Nominal</th>
                        <th>Transaksi 1</th>
                        <th>Transaksi 2</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($duplicatePayments as $dup)
                    <tr>
                        <td class="fw-bold">{{ $dup['invoice_number'] }}</td>
                        <td>{{ $dup['jamaah_name'] }}</td>
                        <td class="text-danger fw-bold">Rp {{ number_format($dup['amount'], 0, ',', '.') }}</td>
                        <td>Kwt: {{ $dup['payment_1']['receipt_number'] }} ({{ $dup['payment_1']['date'] }})</td>
                        <td>Kwt: {{ $dup['payment_2']['receipt_number'] }} ({{ $dup['payment_2']['date'] }})</td>
                        <td class="small text-muted">{{ $dup['reason'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Aging Receivables & Anomalies Section -->
    <div class="row g-4 mb-4">
        <!-- Aging Receivables Breakdown -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="card-title mb-0 fw-bold fs-6">
                        <i class="bi bi-hourglass-split text-warning me-2"></i>Analisis Umur Piutang (Aging Receivables)
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-2 text-center mb-3">
                        <div class="col-4">
                            <div class="p-2 border rounded bg-light">
                                <span class="d-block small text-muted">&le; 30 Hari</span>
                                <strong class="text-success small">Rp {{ number_format($agingReceivables['current_30_days'], 0, ',', '.') }}</strong>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 border rounded bg-light">
                                <span class="d-block small text-muted">31 - 60 Hari</span>
                                <strong class="text-warning small">Rp {{ number_format($agingReceivables['overdue_31_60_days'], 0, ',', '.') }}</strong>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 border rounded bg-light">
                                <span class="d-block small text-muted">&gt; 60 Hari</span>
                                <strong class="text-danger small">Rp {{ number_format($agingReceivables['overdue_over_60_days'], 0, ',', '.') }}</strong>
                            </div>
                        </div>
                    </div>

                    <h6 class="fw-bold small text-muted mb-2">Daftar Tagihan Melewati Jatuh Tempo (&gt; 30 Hari):</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover" style="font-size: 12px;">
                            <thead class="table-light">
                                <tr>
                                    <th>Invoice</th>
                                    <th>Jamaah</th>
                                    <th>Sisa Tagihan</th>
                                    <th>Keterlambatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($agingReceivables['overdue_items'] as $item)
                                <tr>
                                    <td class="fw-bold">{{ $item['invoice_number'] }}</td>
                                    <td>{{ $item['jamaah_name'] }}</td>
                                    <td class="text-danger fw-semibold">Rp {{ number_format($item['remaining'], 0, ',', '.') }}</td>
                                    <td><span class="badge bg-danger">{{ $item['days_overdue'] }} hari</span></td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">Tidak ada piutang macet &gt; 30 hari.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Unusual Transactions (Expense Anomalies) -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="card-title mb-0 fw-bold fs-6">
                        <i class="bi bi-shield-exclamation text-danger me-2"></i>Deteksi Transaksi Pengeluaran Tidak Biasa
                    </h5>
                </div>
                <div class="card-body">
                    @if(empty($unusualTransactions))
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-check-circle-fill text-success fs-1 mb-2"></i>
                        <p class="mb-0">Tidak terdeteksi anomali pengeluaran di luar batas rata-rata wajar.</p>
                    </div>
                    @else
                    <div class="table-responsive">
                        <table class="table table-sm table-hover" style="font-size: 12px;">
                            <thead class="table-light">
                                <tr>
                                    <th>No. Bukti</th>
                                    <th>Keterangan</th>
                                    <th>Kategori</th>
                                    <th>Nominal</th>
                                    <th>Deviasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($unusualTransactions as $tx)
                                <tr>
                                    <td class="fw-bold">{{ $tx['expense_number'] }}</td>
                                    <td>{{ $tx['title'] }}</td>
                                    <td><span class="badge bg-light text-dark border">{{ $tx['category'] }}</span></td>
                                    <td class="text-danger fw-bold">Rp {{ number_format($tx['amount'], 0, ',', '.') }}</td>
                                    <td><span class="badge bg-warning text-dark">{{ $tx['average_comparison'] }}</span></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <small class="text-muted d-block mt-2">
                        *Anomali ditandai otomatis jika pengeluaran melampaui 2.5x rata-rata pengeluaran operasional normal.
                    </small>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
