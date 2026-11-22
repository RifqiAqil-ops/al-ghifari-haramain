@extends('layouts.app')

@section('title', 'AI Executive Reports')

@section('content')
<div class="container-fluid py-3">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h4 mb-1 fw-bold text-dark">
                <i class="bi bi-clipboard2-data-fill text-info me-2"></i>Executive Intelligence Report
            </h2>
            <p class="text-muted small mb-0">Laporan Komprehensif Eksekutif: Kinerja Bisnis, Analitik Finansial, dan Rekomendasi Strategis</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('intelligence.reports.print') }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-printer-fill me-1"></i> Cetak / Simpan PDF
            </a>
        </div>
    </div>

    <!-- Executive Summary Card -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-dark mb-0">Ringkasan Eksekutif &amp; Kesehatan Usaha</h5>
                <span class="text-muted small">Generated: {{ $reportData['generated_at'] }}</span>
            </div>
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="p-3 border rounded bg-light">
                        <small class="text-muted d-block">Indeks Kesehatan Finansial</small>
                        <h3 class="fw-bold mb-0 text-{{ $reportData['financial_health']['badge_class'] }}">
                            {{ $reportData['financial_health']['health_score'] }}/100
                        </h3>
                        <small class="text-muted">{{ $reportData['financial_health']['health_status'] }}</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 border rounded bg-light">
                        <small class="text-muted d-block">Realisasi Kas Masuk</small>
                        <h3 class="fw-bold mb-0 text-dark">{{ $reportData['financial_health']['collection_rate'] }}%</h3>
                        <small class="text-muted">Paid: Rp {{ number_format($reportData['financial_health']['total_paid'], 0, ',', '.') }}</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 border rounded bg-light">
                        <small class="text-muted d-block">Total Beban Operasional</small>
                        <h3 class="fw-bold mb-0 text-dark">Rp {{ number_format($reportData['financial_health']['total_expenses'], 0, ',', '.') }}</h3>
                        <small class="text-muted">Langsung: Rp {{ number_format($reportData['financial_health']['direct_expenses'], 0, ',', '.') }}</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 border rounded bg-light">
                        <small class="text-muted d-block">Net Arus Kas Operasional</small>
                        <h3 class="fw-bold mb-0 {{ $reportData['financial_health']['net_cash_flow'] >= 0 ? 'text-success' : 'text-danger' }}">
                            Rp {{ number_format($reportData['financial_health']['net_cash_flow'], 0, ',', '.') }}
                        </h3>
                        <small class="text-muted">Margin: {{ $reportData['financial_health']['net_margin'] }}%</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Strategic Evidence-Based Recommendations -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="card-title mb-0 fw-bold fs-6">
                <i class="bi bi-lightbulb-fill text-warning me-2"></i>Rekomendasi Strategis Berbasis Data Aktual (Evidence-Based Recommendations)
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="list-group list-group-flush">
                @foreach($reportData['recommendations'] as $rec)
                <div class="list-group-item p-3 border-light">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-{{ $rec['priority'] === 'Kritis' ? 'danger' : ($rec['priority'] === 'Tinggi' ? 'warning text-dark' : 'success') }}">
                            Prioritas: {{ $rec['priority'] }}
                        </span>
                        <span class="badge bg-light text-dark border">{{ $rec['category'] }}</span>
                    </div>
                    <h6 class="fw-bold text-dark mb-1">{{ $rec['title'] }}</h6>
                    <p class="mb-0 text-muted small" style="line-height: 1.5;">{{ $rec['narrative'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Departures Readiness Scorecard -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="card-title mb-0 fw-bold fs-6">
                <i class="bi bi-airplane-fill text-primary me-2"></i>Ringkasan Kesiapan Keberangkatan ({{ $reportData['upcoming_departures_count'] }} Kloter Aktif)
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                    <thead class="table-light">
                        <tr>
                            <th>Kloter</th>
                            <th>Tanggal Berangkat</th>
                            <th>Sisa Waktu</th>
                            <th>Keterisian Kuota</th>
                            <th>Skor Kesiapan</th>
                            <th>Status Kesiapan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reportData['upcoming_readiness'] as $kloter)
                        <tr>
                            <td class="fw-bold">{{ $kloter['code'] }}</td>
                            <td>{{ \Carbon\Carbon::parse($kloter['departure_date'])->format('d M Y') }}</td>
                            <td>{{ $kloter['days_to_departure'] }} hari</td>
                            <td>{{ $kloter['total_registered'] }}/{{ $kloter['quota'] }} seat</td>
                            <td>
                                <strong>{{ $kloter['readiness_score'] }}%</strong>
                                <div class="progress mt-1" style="height: 4px;">
                                    <div class="progress-bar bg-{{ $kloter['badge'] }}" style="width: {{ $kloter['readiness_score'] }}%"></div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-{{ $kloter['badge'] }}">{{ $kloter['status_label'] }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3">Belum ada kloter aktif terdaftar.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
