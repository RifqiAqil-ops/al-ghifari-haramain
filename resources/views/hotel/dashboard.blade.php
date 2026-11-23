@extends('layouts.app')

@section('title', 'Hotel Business Dashboard')

@section('content')
<div class="container-fluid py-3">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="h4 mb-1 fw-bold text-dark">
                <i class="bi bi-buildings-fill text-warning me-2"></i>Hotel Business Management
            </h2>
            <p class="text-muted small mb-0">Lini Bisnis Akomodasi, Multi-Currency (SAR/IDR), Live Exchange Rate, dan Pelaporan Terkonsolidasi</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('hotel.reservations.create') }}" class="btn btn-primary shadow-sm btn-sm">
                <i class="bi bi-plus-circle me-1"></i> Reservasi Baru
            </a>
            <a href="{{ route('hotel.rates.index') }}" class="btn btn-outline-secondary shadow-sm btn-sm">
                <i class="bi bi-currency-exchange me-1"></i> Panel Kurs SAR
            </a>
        </div>
    </div>

    <!-- Live Exchange Rate Banner -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white border-start border-4 border-warning">
        <div class="card-body py-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-warning-subtle text-warning p-2 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                    <i class="bi bi-arrow-repeat fs-4"></i>
                </div>
                <div>
                    <span class="text-muted small d-block">Kurs Acuan Saat Ini (SAR ke IDR):</span>
                    <strong class="fs-5 text-dark">1 SAR = Rp {{ number_format($liveFx['rate'], 2, ',', '.') }}</strong>
                    <span class="badge bg-{{ $liveFx['status'] === 'live' ? 'success' : 'secondary' }} ms-2 px-2">
                        {{ strtoupper($liveFx['status']) }}
                    </span>
                </div>
            </div>
            <div class="text-md-end small text-muted">
                <div>Sumber: <strong>{{ $liveFx['provider'] }}</strong></div>
                <div>Diperbarui: {{ \Carbon\Carbon::parse($liveFx['fetched_at'])->diffForHumans() }}</div>
            </div>
        </div>
    </div>

    <!-- 4 Primary Financial KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
                <div class="card-body">
                    <span class="text-muted small fw-semibold d-block mb-1">Total Penjualan Hotel</span>
                    <h5 class="fw-bold mb-1 text-dark">SAR {{ number_format($metrics['total_sales_sar'], 2, ',', '.') }}</h5>
                    <span class="text-primary small fw-semibold">Rp {{ number_format($metrics['total_sales_idr'], 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
                <div class="card-body">
                    <span class="text-muted small fw-semibold d-block mb-1">Kas Masuk Diterima</span>
                    <h5 class="fw-bold mb-1 text-success">SAR {{ number_format($metrics['cash_collected_sar'], 2, ',', '.') }}</h5>
                    <span class="text-success small fw-semibold">Rp {{ number_format($metrics['cash_collected_idr'], 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
                <div class="card-body">
                    <span class="text-muted small fw-semibold d-block mb-1">Piutang Berjalan</span>
                    <h5 class="fw-bold mb-1 text-danger">SAR {{ number_format($metrics['receivables_sar'], 2, ',', '.') }}</h5>
                    <span class="text-danger small fw-semibold">Rp {{ number_format($metrics['receivables_idr'], 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
                <div class="card-body">
                    <span class="text-muted small fw-semibold d-block mb-1">Laba Kotor Operasional</span>
                    <h5 class="fw-bold mb-1 text-dark">SAR {{ number_format($metrics['gross_profit_sar'], 2, ',', '.') }}</h5>
                    <span class="text-muted small">Est. IDR: Rp {{ number_format($metrics['gross_profit_idr'], 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 2 Column Layout: Upcoming Check-ins & Recent Invoices -->
    <div class="row g-4">
        <!-- Upcoming Check-ins -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-calendar2-check text-primary me-2"></i>Check-in Mendatang</h6>
                    <a href="{{ route('hotel.reservations.index') }}" class="small text-decoration-none">Lihat Semua &rarr;</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                            <thead class="table-light">
                                <tr>
                                    <th>Kode / Agen</th>
                                    <th>Hotel</th>
                                    <th>Check-in</th>
                                    <th>Kamar</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($upcomingCheckIns as $res)
                                <tr>
                                    <td>
                                        <a href="{{ route('hotel.reservations.show', $res) }}" class="fw-bold text-decoration-none">
                                            {{ $res->reservation_number }}
                                        </a>
                                        <small class="d-block text-muted">{{ $res->customer?->name }}</small>
                                    </td>
                                    <td>{{ $res->items->first()?->hotel?->name ?? 'Hotel' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($res->check_in_date)->format('d M Y') }}</td>
                                    <td>{{ $res->total_rooms }} kmr ({{ $res->total_nights }} mlm)</td>
                                    <td>
                                        <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill">
                                            {{ ucfirst($res->status) }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">Belum ada jadwal check-in mendatang.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Invoices -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-receipt text-warning me-2"></i>Invoice Hotel Terbaru</h6>
                    <a href="{{ route('hotel.invoices.index') }}" class="small text-decoration-none">Lihat Semua &rarr;</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                            <thead class="table-light">
                                <tr>
                                    <th>No Invoice</th>
                                    <th>Pelanggan</th>
                                    <th>Total SAR</th>
                                    <th>Total IDR</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentInvoices as $inv)
                                <tr>
                                    <td>
                                        <a href="{{ route('hotel.invoices.show', $inv) }}" class="fw-bold text-decoration-none">
                                            {{ $inv->invoice_number }}
                                        </a>
                                        <small class="d-block text-muted">{{ $inv->invoice_date->format('d M Y') }}</small>
                                    </td>
                                    <td>{{ $inv->customer?->name }}</td>
                                    <td class="fw-bold text-dark">SAR {{ number_format($inv->total_amount_sar, 2, ',', '.') }}</td>
                                    <td class="text-muted small">Rp {{ number_format($inv->total_amount_idr, 0, ',', '.') }}</td>
                                    <td>
                                        <span class="badge bg-{{ $inv->status === 'lunas' ? 'success' : 'warning' }}-subtle text-{{ $inv->status === 'lunas' ? 'success' : 'warning' }} border rounded-pill">
                                            {{ ucfirst($inv->status) }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">Belum ada invoice yang diterbitkan.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
