@extends('layouts.app')

@section('title', 'Laporan Kas Pembayaran - Al Ghifari Haramain')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <a href="{{ route('reports.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Pusat Laporan
        </a>
        <h4 class="fw-bold mb-1 text-dark">Laporan Penerimaan Kas Pembayaran</h4>
        <p class="text-muted small mb-0">Periode: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('reports.payments', ['start_date' => $startDate, 'end_date' => $endDate, 'method' => $method, 'export' => 'excel']) }}" class="btn btn-success shadow-sm">
            <i class="bi bi-file-earmark-excel me-1"></i> Unduh Excel
        </a>
        <a href="{{ route('reports.payments', ['start_date' => $startDate, 'end_date' => $endDate, 'method' => $method, 'export' => 'pdf']) }}" class="btn btn-danger shadow-sm">
            <i class="bi bi-file-earmark-pdf me-1"></i> Unduh PDF
        </a>
    </div>
</div>

<!-- Filter Box -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
        <form action="{{ route('reports.payments') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Mulai Tanggal</label>
                <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Metode Bayar</label>
                <select name="method" class="form-select form-select-sm">
                    <option value="">-- Semua Metode --</option>
                    <option value="transfer" {{ $method == 'transfer' ? 'selected' : '' }}>Transfer Bank</option>
                    <option value="tunai" {{ $method == 'tunai' ? 'selected' : '' }}>Tunai / Cash</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary btn-sm w-100">
                    <i class="bi bi-filter me-1"></i> Tampilkan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Total Card -->
<div class="card border-0 shadow-sm rounded-3 mb-4 bg-success-subtle border-start border-4 border-success p-3">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <span class="text-success small fw-semibold">Total Kas Diterima pada Periode Ini:</span>
            <h3 class="fw-bold text-success mb-0 mt-1">Rp {{ number_format($totalAmount, 0, ',', '.') }}</h3>
        </div>
        <span class="badge bg-success rounded-pill px-3 py-2 fs-6">{{ $payments->count() }} Transaksi</span>
    </div>
</div>

<!-- Table -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 40px;">No</th>
                        <th>No Kwitansi</th>
                        <th>Tanggal</th>
                        <th>Nama Jamaah</th>
                        <th>Paket</th>
                        <th>Tipe</th>
                        <th>Metode</th>
                        <th class="text-end">Nominal (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $index => $pay)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <a href="{{ route('finance.receipt', $pay) }}" target="_blank" class="fw-bold text-dark text-decoration-none">
                                    {{ $pay->receipt_number }}
                                </a>
                            </td>
                            <td>{{ $pay->payment_date ? $pay->payment_date->format('d/m/Y') : '-' }}</td>
                            <td>
                                <span class="fw-semibold text-dark">{{ $pay->invoice->registration->jamaah->name ?? '-' }}</span>
                            </td>
                            <td>{{ $pay->invoice->registration->package->name ?? '-' }}</td>
                            <td><span class="badge bg-light text-dark border text-uppercase">{{ $pay->payment_type }}</span></td>
                            <td>{{ strtoupper($pay->payment_method) }}</td>
                            <td class="text-end fw-bold text-success">Rp {{ number_format($pay->amount, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                Tidak ada data penerimaan kas pada rentang tanggal yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
