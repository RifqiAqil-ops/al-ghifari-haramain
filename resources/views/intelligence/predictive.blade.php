@extends('layouts.app')

@section('title', 'Predictive Analytics')

@section('content')
<div class="container-fluid py-3">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="h4 mb-1 fw-bold text-dark">
                <i class="bi bi-clock-history text-success me-2"></i>Predictive Analytics &amp; Forecasting
            </h2>
            <p class="text-muted small mb-0">Model Proyeksi Keterisian Kloter, Estimasi Kebutuhan Logistik, dan Prakiraan Arus Kas</p>
        </div>
        <div class="badge bg-light text-dark border px-3 py-2">
            <i class="bi bi-bar-chart-line text-primary me-1"></i> Statistical &amp; Heuristic Trend Analysis
        </div>
    </div>

    <!-- Transparency Disclaimer Banner -->
    <div class="alert alert-info border-0 shadow-sm rounded-3 mb-4 p-3" style="font-size: 13px;">
        <div class="d-flex align-items-start gap-2">
            <i class="bi bi-info-circle-fill fs-5 mt-1"></i>
            <div>
                <strong>Transparansi Metodologi Model:</strong><br>
                Seluruh proyeksi pada modul ini dihitung menggunakan <strong>Analisis Tren Statistik &amp; Kurva Lead-Time Heuristik</strong> (bukan model Machine Learning permanen). Tingkat keyakinan (*confidence*) dihitung secara jujur berdasarkan kecukupan sampel data aktual.
            </div>
        </div>
    </div>

    <!-- Top Forecast Overview -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 p-3 h-100">
                <span class="text-muted small fw-semibold">Proyeksi Pertumbuhan Jamaah (30 Hari)</span>
                <div class="d-flex align-items-baseline mt-1">
                    <h3 class="fw-bold mb-0 text-success">+{{ $growthForecast['projected_new_jamaah_next_30_days'] }}</h3>
                    <span class="text-muted ms-2">Jamaah Baru</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-2">
                    <small class="text-muted">Total Proyeksi: <strong>{{ $growthForecast['projected_total_jamaah'] }}</strong></small>
                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">Keyakinan: {{ $growthForecast['confidence_label'] }} ({{ $growthForecast['confidence_score'] }}%)</span>
                </div>
                <small class="text-muted mt-2 d-block" style="font-size: 11px;">{{ $growthForecast['limitations_note'] }}</small>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 p-3 h-100">
                <span class="text-muted small fw-semibold">Jendela Proyeksi Logistik (60 Hari)</span>
                <div class="d-flex align-items-baseline mt-1">
                    <h3 class="fw-bold mb-0 text-primary">{{ $equipmentNeeds['total_upcoming_jamaah'] }}</h3>
                    <span class="text-muted ms-2">Jamaah Berangkat</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-2">
                    <small class="text-muted">Item Defisit: <strong>{{ collect($equipmentNeeds['items'])->where('deficit', '>', 0)->count() }} item</strong></small>
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25">Keyakinan: {{ $equipmentNeeds['confidence_label'] }}</span>
                </div>
                <small class="text-muted mt-2 d-block" style="font-size: 11px;">{{ $equipmentNeeds['limitations_note'] }}</small>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 p-3 h-100">
                <span class="text-muted small fw-semibold">Prakiraan Arus Kas Bersih (Kuartal)</span>
                <div class="d-flex align-items-baseline mt-1">
                    @php $quarterNet = collect($cashflowForecast['forecast_months'])->sum('projected_net'); @endphp
                    <h3 class="fw-bold mb-0 {{ $quarterNet >= 0 ? 'text-success' : 'text-danger' }}">
                        Rp {{ number_format($quarterNet, 0, ',', '.') }}
                    </h3>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-2">
                    <small class="text-muted">Periode: <strong>3 Bulan ke Depan</strong></small>
                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25">Keyakinan: {{ $cashflowForecast['confidence_label'] }}</span>
                </div>
                <small class="text-muted mt-2 d-block" style="font-size: 11px;">{{ $cashflowForecast['limitations_note'] }}</small>
            </div>
        </div>
    </div>

    <!-- Occupancy Projections per Kloter -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 fw-bold fs-6">
                <i class="bi bi-pie-chart-fill text-primary me-2"></i>Proyeksi Keterisian Kursi Kloter (Occupancy Forecast)
            </h5>
            <span class="badge bg-light text-muted border">Berdasarkan Lead-Time Acceleration Model</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Kloter</th>
                            <th>Tanggal Berangkat</th>
                            <th>Sisa Waktu</th>
                            <th>Kuota</th>
                            <th>Keterisian Saat Ini</th>
                            <th>Proyeksi Akhir</th>
                            <th>Tingkat Keyakinan</th>
                            <th>Fase Pendaftaran</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($occupancyPredictions as $pred)
                        <tr>
                            <td class="fw-bold">{{ $pred['departure_code'] }}</td>
                            <td>{{ \Carbon\Carbon::parse($pred['departure_date'])->format('d M Y') }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $pred['days_remaining'] }} hari</span></td>
                            <td>{{ $pred['quota'] }} seat</td>
                            <td>
                                <div>{{ $pred['current_filled'] }} seat ({{ $pred['current_occupancy_percent'] }}%)</div>
                                <div class="progress mt-1" style="height: 5px;">
                                    <div class="progress-bar bg-secondary" style="width: {{ $pred['current_occupancy_percent'] }}%"></div>
                                </div>
                            </td>
                            <td>
                                <strong class="text-primary">{{ $pred['predicted_final_occupancy'] }} seat ({{ $pred['predicted_occupancy_percent'] }}%)</strong>
                                <div class="progress mt-1" style="height: 5px;">
                                    <div class="progress-bar bg-primary" style="width: {{ $pred['predicted_occupancy_percent'] }}%"></div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-{{ $pred['confidence_score'] >= 85 ? 'success' : 'warning text-dark' }}">
                                    {{ $pred['confidence_label'] }} ({{ $pred['confidence_score'] }}%)
                                </span>
                            </td>
                            <td><small class="text-muted">{{ $pred['status_phase'] }}</small></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">Belum ada jadwal keberangkatan aktif.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Equipment Demand & Cashflow Projections Grid -->
    <div class="row g-4">
        <!-- Equipment Demand Forecast -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="card-title mb-0 fw-bold fs-6">
                        <i class="bi bi-box-seam text-warning me-2"></i>Prakiraan Kebutuhan Stok Perlengkapan (60 Hari ke Depan)
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0" style="font-size: 13px;">
                            <thead class="table-light">
                                <tr>
                                    <th>Barang</th>
                                    <th>Stok Sekarang</th>
                                    <th>Estimasi Kebutuhan</th>
                                    <th>Sisa Proyeksi</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($equipmentNeeds['items'] as $item)
                                <tr>
                                    <td class="fw-semibold">{{ $item['name'] }}</td>
                                    <td>{{ $item['current_stock'] }}</td>
                                    <td>{{ $item['projected_demand'] }}</td>
                                    <td class="{{ $item['projected_balance'] < 0 ? 'text-danger fw-bold' : '' }}">
                                        {{ $item['projected_balance'] }}
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $item['badge'] }}">{{ $item['status'] }}</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3-Month Cash Flow Forecast -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="card-title mb-0 fw-bold fs-6">
                        <i class="bi bi-cash-coin text-success me-2"></i>Prakiraan Arus Kas 3 Bulan ke Depan
                    </h5>
                </div>
                <div class="card-body p-3">
                    @foreach($cashflowForecast['forecast_months'] as $m)
                    <div class="p-3 mb-3 border rounded bg-light">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <h6 class="fw-bold mb-0 text-dark">{{ $m['month_name'] }}</h6>
                            <span class="badge bg-secondary">{{ $m['departures_count'] }} Kloter</span>
                        </div>
                        <div class="d-flex justify-content-between small text-muted mt-2">
                            <span>Estimasi Masuk (Piutang Matang):</span>
                            <span class="text-success fw-semibold">Rp {{ number_format($m['projected_inflow'], 0, ',', '.') }}</span>
                        </div>
                        <div class="d-flex justify-content-between small text-muted mt-1">
                            <span>Estimasi Beban Kloter:</span>
                            <span class="text-danger fw-semibold">Rp {{ number_format($m['projected_outflow'], 0, ',', '.') }}</span>
                        </div>
                        <div class="d-flex justify-content-between small fw-bold pt-2 mt-2 border-top">
                            <span>Net Projected:</span>
                            <span class="{{ $m['projected_net'] >= 0 ? 'text-success' : 'text-danger' }}">
                                Rp {{ number_format($m['projected_net'], 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
