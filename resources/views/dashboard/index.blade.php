@extends('layouts.app')

@section('title', 'Dashboard Eksekutif')
@section('header_title', 'Dashboard Eksekutif & Operasional')

@section('content')
<!-- Period Filter Bar -->
<div class="card card-custom p-3 mb-4">
    <form action="{{ route('dashboard') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-auto">
            <span class="small fw-bold text-forest-green"><i class="bi bi-funnel-fill text-royal-gold me-1"></i> Filter Periode:</span>
        </div>
        <div class="col-auto">
            <select name="period" id="period-select" class="form-select form-select-sm" onchange="toggleCustomDate(this.value)">
                <option value="all" {{ $period === 'all' ? 'selected' : '' }}>Semua Data</option>
                <option value="today" {{ $period === 'today' ? 'selected' : '' }}>Hari Ini</option>
                <option value="this_month" {{ $period === 'this_month' ? 'selected' : '' }}>Bulan Ini</option>
                <option value="this_year" {{ $period === 'this_year' ? 'selected' : '' }}>Tahun Ini ({{ date('Y') }})</option>
                <option value="custom" {{ $period === 'custom' ? 'selected' : '' }}>Rentang Tanggal</option>
            </select>
        </div>
        <div class="col-auto d-flex align-items-center gap-2 {{ $period === 'custom' ? '' : 'd-none' }}" id="custom-date-container">
            <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate }}">
            <span class="small text-muted">s/d</span>
            <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate }}">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-sm btn-forest-green px-3">
                <i class="bi bi-search me-1"></i> Terapkan
            </button>
            @if($period !== 'all')
                <a href="{{ route('dashboard') }}" class="btn btn-sm btn-light border">Reset</a>
            @endif
        </div>
    </form>
</div>

<!-- Primary Metric Cards -->
<div class="row g-3 mb-4">
    <!-- Total Jamaah -->
    <div class="col-sm-6 col-xl-3">
        <div class="card card-custom p-3 h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="small text-muted fw-semibold">Total Jamaah</span>
                <span class="p-2 rounded-circle bg-forest-green text-royal-gold"><i class="bi bi-people fs-5"></i></span>
            </div>
            <h3 class="fw-bold text-dark mb-1">{{ number_format($totalJamaah, 0, ',', '.') }}</h3>
            <div class="small text-muted">
                <span class="text-success fw-semibold"><i class="bi bi-check-circle-fill me-1"></i>{{ $activeJamaah }} Aktif</span> &bull; Umrah / Haji
            </div>
        </div>
    </div>

    <!-- Pemasukan Valid -->
    <div class="col-sm-6 col-xl-3">
        <div class="card card-custom p-3 h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="small text-muted fw-semibold">Total Pemasukan</span>
                <span class="p-2 rounded-circle bg-success bg-opacity-10 text-success"><i class="bi bi-cash-stack fs-5"></i></span>
            </div>
            <h4 class="fw-bold text-success mb-1">Rp {{ number_format($totalPayments, 0, ',', '.') }}</h4>
            <div class="small text-muted">
                Kwitansi terverifikasi sah
            </div>
        </div>
    </div>

    <!-- Pengeluaran Operasional -->
    <div class="col-sm-6 col-xl-3">
        <div class="card card-custom p-3 h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="small text-muted fw-semibold">Pengeluaran Operasional</span>
                <span class="p-2 rounded-circle bg-danger bg-opacity-10 text-danger"><i class="bi bi-wallet2 fs-5"></i></span>
            </div>
            <h4 class="fw-bold text-danger mb-1">Rp {{ number_format($totalExpenses, 0, ',', '.') }}</h4>
            <div class="small text-muted">
                Tiket, hotel &amp; visa Saudi
            </div>
        </div>
    </div>

    <!-- Sisa Tagihan (Piutang) -->
    <div class="col-sm-6 col-xl-3">
        <div class="card card-custom p-3 h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="small text-muted fw-semibold">Sisa Tagihan Jamaah</span>
                <span class="p-2 rounded-circle bg-warning bg-opacity-10 text-warning"><i class="bi bi-hourglass-split fs-5"></i></span>
            </div>
            <h4 class="fw-bold text-warning mb-1">Rp {{ number_format($totalOutstanding, 0, ',', '.') }}</h4>
            <div class="small text-muted">
                {{ $unpaidInvoicesCount }} invoice belum lunas
            </div>
        </div>
    </div>
</div>

<!-- Secondary Metrics Breakdown -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card card-custom p-3 d-flex flex-row align-items-center gap-3">
            <div class="p-3 rounded-3 bg-forest-green text-royal-gold fs-4">
                <i class="bi bi-moon-stars-fill"></i>
            </div>
            <div>
                <small class="text-muted d-block">Pendaftar Program Umrah</small>
                <h5 class="fw-bold text-dark mb-0">{{ $umrahJamaah }} Jamaah</h5>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-custom p-3 d-flex flex-row align-items-center gap-3">
            <div class="p-3 rounded-3 bg-royal-gold text-white fs-4">
                <i class="bi bi-award-fill"></i>
            </div>
            <div>
                <small class="text-muted d-block">Pendaftar Program Haji</small>
                <h5 class="fw-bold text-dark mb-0">{{ $hajiJamaah }} Jamaah</h5>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-custom p-3 d-flex flex-row align-items-center gap-3">
            <div class="p-3 rounded-3 bg-light border text-forest-green fs-4">
                <i class="bi bi-box-seam-fill"></i>
            </div>
            <div>
                <small class="text-muted d-block">Paket Aktif Ditawarkan</small>
                <h5 class="fw-bold text-dark mb-0">{{ $totalPackages }} Paket Pilihan</h5>
            </div>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="row g-4 mb-4">
    <!-- Chart: Keuangan (Pemasukan vs Pengeluaran) -->
    <div class="col-lg-8">
        <div class="card card-custom p-4 h-100">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 class="fw-bold text-forest-green mb-0">
                    <i class="bi bi-graph-up me-2 text-royal-gold"></i> Tren Pemasukan &amp; Pengeluaran (6 Bulan Terakhir)
                </h6>
                <span class="badge bg-light text-muted border">Dalam Rupiah</span>
            </div>
            <div style="height: 290px;">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Chart: Pendaftaran Jamaah -->
    <div class="col-lg-4">
        <div class="card card-custom p-4 h-100">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 class="fw-bold text-forest-green mb-0">
                    <i class="bi bi-person-check me-2 text-royal-gold"></i> Registrasi Jamaah
                </h6>
                <span class="badge bg-light text-muted border">Per Bulan</span>
            </div>
            <div style="height: 290px;">
                <canvas id="registrationChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Operational Reminders & Alerts -->
<div class="row g-4 mb-4">
    <!-- Upcoming Departures -->
    <div class="col-lg-4">
        <div class="card card-custom p-3 h-100">
            <div class="d-flex align-items-center justify-content-between border-bottom pb-2 mb-3">
                <h6 class="fw-bold text-forest-green mb-0">
                    <i class="bi bi-airplane-engines me-2 text-royal-gold"></i> Keberangkatan Mendatang
                </h6>
                <a href="{{ route('departures.index') }}" class="small text-decoration-none text-royal-gold fw-semibold">Lihat Semua</a>
            </div>

            @forelse($upcomingDepartures as $dep)
                <div class="p-2 mb-2 rounded bg-light border-start border-3 border-royal-gold">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="fw-bold text-dark small">{{ $dep->code }}</span>
                        <span class="badge bg-forest-green text-white" style="font-size: 0.65rem;">
                            {{ $dep->departure_date->translatedFormat('d M Y') }}
                        </span>
                    </div>
                    <div class="small text-muted text-truncate">{{ $dep->package->name }}</div>
                    <div class="d-flex justify-content-between text-muted" style="font-size: 0.75rem;">
                        <span><i class="bi bi-people me-1"></i>{{ $dep->registrations_count ?? $dep->registrations()->count() }} / {{ $dep->quota }} Pax</span>
                        <span>{{ $dep->airline ?? 'Maskapai' }}</span>
                    </div>
                </div>
            @empty
                <div class="text-center py-4 text-muted small">
                    <i class="bi bi-calendar-x fs-3 d-block mb-1 text-muted"></i>
                    Belum ada jadwal keberangkatan aktif.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Payment Due Alerts -->
    <div class="col-lg-4">
        <div class="card card-custom p-3 h-100">
            <div class="d-flex align-items-center justify-content-between border-bottom pb-2 mb-3">
                <h6 class="fw-bold text-forest-green mb-0">
                    <i class="bi bi-exclamation-circle me-2 text-warning"></i> Tagihan Jatuh Tempo
                </h6>
                <a href="{{ route('finance.invoices.index') }}" class="small text-decoration-none text-royal-gold fw-semibold">Lihat Semua</a>
            </div>

            @forelse($dueInvoices as $inv)
                <div class="p-2 mb-2 rounded bg-light border-start border-3 border-warning">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="fw-bold text-dark small">{{ $inv->registration->jamaah->name ?? 'Jamaah' }}</span>
                        <span class="badge bg-warning text-dark" style="font-size: 0.65rem;">
                            Jatuh: {{ $inv->due_date ? $inv->due_date->translatedFormat('d M') : '-' }}
                        </span>
                    </div>
                    <div class="small text-muted">{{ $inv->invoice_number }}</div>
                    <div class="d-flex justify-content-between align-items-center text-muted" style="font-size: 0.75rem;">
                        <span>Sisa: <strong class="text-danger">Rp {{ number_format($inv->remaining_amount, 0, ',', '.') }}</strong></span>
                        <a href="{{ route('finance.invoices.show', $inv) }}" class="btn btn-xs btn-outline-secondary py-0 px-2">Bayar</a>
                    </div>
                </div>
            @empty
                <div class="text-center py-4 text-muted small">
                    <i class="bi bi-check-circle fs-3 d-block mb-1 text-success"></i>
                    Tidak ada tagihan mendesak jatuh tempo.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Incomplete Documents -->
    <div class="col-lg-4">
        <div class="card card-custom p-3 h-100">
            <div class="d-flex align-items-center justify-content-between border-bottom pb-2 mb-3">
                <h6 class="fw-bold text-forest-green mb-0">
                    <i class="bi bi-file-earmark-medical me-2 text-danger"></i> Dokumen Belum Lengkap
                </h6>
                <a href="{{ route('jamaah.index') }}" class="small text-decoration-none text-royal-gold fw-semibold">Lihat Semua</a>
            </div>

            @forelse($incompleteDocsJamaahs as $inc)
                <div class="p-2 mb-2 rounded bg-light border-start border-3 border-danger">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="fw-bold text-dark small">{{ $inc->name }}</span>
                        <span class="badge bg-danger bg-opacity-25 text-danger" style="font-size: 0.65rem;">Paspor Belum Verif</span>
                    </div>
                    <div class="small text-muted">{{ $inc->phone }}</div>
                    <div class="text-end" style="font-size: 0.75rem;">
                        <a href="{{ route('jamaah.show', $inc) }}" class="text-decoration-none text-forest-green fw-semibold">Periksa Berkas &rarr;</a>
                    </div>
                </div>
            @empty
                <div class="text-center py-4 text-muted small">
                    <i class="bi bi-shield-check fs-3 d-block mb-1 text-success"></i>
                    Semua jamaah aktif telah memiliki dokumen terverifikasi.
                </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Recent Activities Table -->
<div class="card card-custom p-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h6 class="fw-bold text-forest-green mb-0">
            <i class="bi bi-clock-history me-2 text-royal-gold"></i> Aktivitas Sistem &amp; Log Perubahan Terbaru
        </h6>
        <a href="{{ route('audit-logs.index') }}" class="small text-decoration-none text-royal-gold fw-semibold">Seluruh Audit Log</a>
    </div>

    <div class="table-responsive">
        <table class="table table-custom table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Pengguna</th>
                    <th>Modul</th>
                    <th>Aksi</th>
                    <th>Deskripsi Aktivitas</th>
                    <th>Alamat IP</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentActivities as $act)
                    <tr>
                        <td class="text-muted small" style="white-space: nowrap;">
                            {{ $act->created_at->translatedFormat('d M Y, H:i') }}
                        </td>
                        <td class="fw-semibold text-dark">
                            {{ $act->user_name ?? ($act->user->name ?? 'Sistem') }}
                        </td>
                        <td>
                            <span class="badge bg-light text-forest-green border">
                                {{ $act->module }}
                            </span>
                        </td>
                        <td>
                            @php
                                $badgeClass = match($act->action) {
                                    'CREATE', 'REGISTER', 'PAYMENT' => 'badge-subtle-success',
                                    'UPDATE', 'CHECKLIST' => 'badge-subtle-info',
                                    'DELETE', 'CANCEL_REGISTRATION', 'REVERSAL' => 'badge-subtle-danger',
                                    default => 'badge-subtle-warning',
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }} px-2 py-1">
                                {{ $act->action }}
                            </span>
                        </td>
                        <td class="text-muted small">
                            {{ $act->description }}
                        </td>
                        <td class="text-muted small font-monospace">
                            {{ $act->ip_address ?? '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted small">
                            Belum ada riwayat aktivitas yang tercatat.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function toggleCustomDate(val) {
        const container = document.getElementById('custom-date-container');
        if (container) {
            if (val === 'custom') {
                container.classList.remove('d-none');
            } else {
                container.classList.add('d-none');
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        // 1. Revenue & Expense Chart
        const revCtx = document.getElementById('revenueChart');
        if (revCtx && window.Chart) {
            new Chart(revCtx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($revenueMonths) !!},
                    datasets: [
                        {
                            label: 'Pemasukan (IDR)',
                            data: {!! json_encode($revenueValues) !!},
                            backgroundColor: '#073B2E',
                            borderRadius: 6,
                        },
                        {
                            label: 'Pengeluaran (IDR)',
                            data: {!! json_encode($expenseValues) !!},
                            backgroundColor: '#C9A45B',
                            borderRadius: 6,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top' },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': Rp ' + context.parsed.y.toLocaleString('id-ID');
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    if (value >= 1000000) return (value / 1000000) + ' Jt';
                                    return value;
                                }
                            }
                        }
                    }
                }
            });
        }

        // 2. Registration Trend Chart
        const regCtx = document.getElementById('registrationChart');
        if (regCtx && window.Chart) {
            new Chart(regCtx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($revenueMonths) !!},
                    datasets: [{
                        label: 'Pendaftar',
                        data: {!! json_encode($registrationValues) !!},
                        borderColor: '#073B2E',
                        backgroundColor: 'rgba(7, 59, 46, 0.1)',
                        fill: true,
                        tension: 0.35,
                        pointBackgroundColor: '#C9A45B',
                        pointBorderColor: '#073B2E',
                        pointRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { stepSize: 1 }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush
