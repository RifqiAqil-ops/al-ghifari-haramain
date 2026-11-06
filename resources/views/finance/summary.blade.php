@extends('layouts.app')

@section('title', 'Ringkasan Keuangan - Al Ghifari Haramain')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Ringkasan & Arus Keuangan</h4>
        <p class="text-muted small mb-0">Pemantauan pendapatan kas masuk, beban operasional, dan laba berjalan</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('finance.invoices') }}" class="btn btn-outline-secondary">
            <i class="bi bi-receipt me-1"></i> Daftar Invoice
        </a>
        <a href="{{ route('finance.expenses') }}" class="btn btn-outline-danger">
            <i class="bi bi-dash-circle me-1"></i> Catat Pengeluaran
        </a>
    </div>
</div>

<!-- Date Filter Form -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
        <form action="{{ route('finance.summary') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-auto">
                <span class="small fw-semibold text-secondary"><i class="bi bi-funnel me-1"></i> Filter Periode:</span>
            </div>
            <div class="col-md-3">
                <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate }}" placeholder="Tanggal Mulai">
            </div>
            <div class="col-auto text-muted small">s/d</div>
            <div class="col-md-3">
                <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate }}" placeholder="Tanggal Selesai">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary btn-sm px-3">
                    <i class="bi bi-filter me-1"></i> Terapkan
                </button>
                @if($startDate || $endDate)
                    <a href="{{ route('finance.summary') }}" class="btn btn-light btn-sm text-secondary">Reset</a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Top Metrics -->
<div class="row g-3 mb-4">
    <div class="col-md-3 col-sm-6">
        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100 border-start border-4 border-success">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small fw-semibold">Total Pendapatan Masuk</span>
                    <h4 class="fw-bold text-success mb-0 mt-1">Rp {{ number_format($totalIncome, 0, ',', '.') }}</h4>
                    <span class="text-muted small">Pembayaran jamaah valid</span>
                </div>
                <div class="stat-icon bg-success-subtle text-success p-3 rounded-3">
                    <i class="bi bi-arrow-down-left-circle fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100 border-start border-4 border-danger">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small fw-semibold">Total Beban Pengeluaran</span>
                    <h4 class="fw-bold text-danger mb-0 mt-1">Rp {{ number_format($totalExpense, 0, ',', '.') }}</h4>
                    <span class="text-muted small">Operasional & logistik</span>
                </div>
                <div class="stat-icon bg-danger-subtle text-danger p-3 rounded-3">
                    <i class="bi bi-arrow-up-right-circle fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100 border-start border-4 border-primary">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small fw-semibold">Laba Bersih Operasional</span>
                    <h4 class="fw-bold {{ $netProfit >= 0 ? 'text-primary' : 'text-danger' }} mb-0 mt-1">
                        Rp {{ number_format($netProfit, 0, ',', '.') }}
                    </h4>
                    <span class="text-muted small">Kas bersih tersisa</span>
                </div>
                <div class="stat-icon bg-primary-subtle text-primary p-3 rounded-3">
                    <i class="bi bi-graph-up fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100 border-start border-4 border-warning">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small fw-semibold">Total Piutang Jamaah</span>
                    <h4 class="fw-bold text-warning mb-0 mt-1">Rp {{ number_format($totalReceivable, 0, ',', '.') }}</h4>
                    <span class="text-muted small">Sisa tagihan belum lunas</span>
                </div>
                <div class="stat-icon bg-warning-subtle text-warning p-3 rounded-3">
                    <i class="bi bi-hourglass-split fs-4"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Transactions Tables -->
<div class="row g-4">
    <!-- Kas Masuk Terakhir -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-success"><i class="bi bi-cash-stack me-1"></i> Pembayaran Masuk Terbaru</h6>
                <a href="{{ route('finance.payments') }}" class="btn btn-sm btn-link text-decoration-none">Lihat Semua</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>No Kwitansi</th>
                                <th>Jamaah</th>
                                <th>Metode</th>
                                <th class="text-end">Nominal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentPayments as $pay)
                                <tr>
                                    <td>
                                        <a href="{{ route('finance.receipt', $pay) }}" target="_blank" class="fw-bold text-dark text-decoration-none">
                                            {{ $pay->receipt_number }}
                                        </a>
                                        <span class="text-muted small d-block">{{ $pay->payment_date ? $pay->payment_date->format('d/m/Y') : '-' }}</span>
                                    </td>
                                    <td>
                                        <span class="fw-semibold">{{ $pay->invoice->registration->jamaah->name ?? '-' }}</span>
                                        <span class="text-muted small d-block text-capitalize">{{ $pay->payment_type }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border text-uppercase">{{ $pay->payment_method }}</span>
                                    </td>
                                    <td class="text-end fw-bold text-success">
                                        +Rp {{ number_format($pay->amount, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center py-4 text-muted">Belum ada catatan pembayaran.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Kas Keluar Terakhir -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-danger"><i class="bi bi-wallet2 me-1"></i> Beban Pengeluaran Terbaru</h6>
                <a href="{{ route('finance.expenses') }}" class="btn btn-sm btn-link text-decoration-none">Lihat Semua</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>No / Kategori</th>
                                <th>Keterangan</th>
                                <th>Alokasi</th>
                                <th class="text-end">Nominal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentExpenses as $exp)
                                <tr>
                                    <td>
                                        <span class="fw-bold text-dark">{{ $exp->expense_number }}</span>
                                        <span class="badge bg-light text-secondary border d-block mt-1">{{ $exp->category->name ?? 'Operasional' }}</span>
                                    </td>
                                    <td>
                                        <span class="fw-semibold">{{ $exp->title }}</span>
                                        <span class="text-muted small d-block">{{ $exp->expense_date ? $exp->expense_date->format('d/m/Y') : '-' }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary-subtle text-secondary">{{ $exp->departure->code ?? 'Travel Umum' }}</span>
                                    </td>
                                    <td class="text-end fw-bold text-danger">
                                        -Rp {{ number_format($exp->amount, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center py-4 text-muted">Belum ada catatan pengeluaran.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
