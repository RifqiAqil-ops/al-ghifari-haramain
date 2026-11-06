@extends('layouts.app')

@section('title', 'Daftar Invoice & Tagihan - Al Ghifari Haramain')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Daftar Tagihan & Invoice Jamaah</h4>
        <p class="text-muted small mb-0">Kelola penagihan, status pelunasan paket, dan pencatatan pembayaran</p>
    </div>
    <a href="{{ route('finance.summary') }}" class="btn btn-outline-secondary">
        <i class="bi bi-pie-chart me-1"></i> Ringkasan Kas
    </a>
</div>

<!-- Search & Filter Card -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
        <form action="{{ route('finance.invoices') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control form-control-sm border-start-0" 
                        value="{{ $search }}" placeholder="Cari No Invoice, No Registrasi, atau Nama Jamaah...">
                </div>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm">
                    <option value="">-- Semua Status Tagihan --</option>
                    <option value="belum_bayar" {{ $status == 'belum_bayar' ? 'selected' : '' }}>Belum Bayar</option>
                    <option value="sebagian" {{ $status == 'sebagian' ? 'selected' : '' }}>Sebagian (DP/Cicilan)</option>
                    <option value="lunas" {{ $status == 'lunas' ? 'selected' : '' }}>Lunas</option>
                    <option value="dibatalkan" {{ $status == 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary btn-sm px-3">Filter</button>
                @if($search || $status)
                    <a href="{{ route('finance.invoices') }}" class="btn btn-light btn-sm text-secondary">Reset</a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Table Card -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No Invoice</th>
                        <th>Nama Jamaah</th>
                        <th>Paket / Kloter</th>
                        <th class="text-end">Total Tagihan</th>
                        <th class="text-end">Terbayar</th>
                        <th class="text-end">Sisa Tagihan</th>
                        <th class="text-center">Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $inv)
                        <tr>
                            <td>
                                <a href="{{ route('finance.invoices.show', $inv) }}" class="fw-bold text-dark text-decoration-none">
                                    {{ $inv->invoice_number }}
                                </a>
                                <span class="text-muted small d-block">
                                    {{ $inv->invoice_date ? $inv->invoice_date->format('d/m/Y') : '-' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('jamaahs.show', $inv->registration->jamaah_id ?? '#') }}" class="fw-semibold text-dark text-decoration-none">
                                    {{ $inv->registration->jamaah->name ?? '-' }}
                                </a>
                                <span class="text-muted small d-block">
                                    {{ $inv->registration->jamaah->phone ?? '-' }}
                                </span>
                            </td>
                            <td>
                                <span class="fw-semibold text-dark">{{ $inv->registration->package->name ?? '-' }}</span>
                                <span class="text-muted small d-block">
                                    Kloter: {{ $inv->registration->departure->code ?? 'Belum ditentukan' }}
                                </span>
                            </td>
                            <td class="text-end fw-bold text-dark">
                                Rp {{ number_format($inv->total_amount, 0, ',', '.') }}
                            </td>
                            <td class="text-end fw-bold text-success">
                                Rp {{ number_format($inv->paid_amount, 0, ',', '.') }}
                            </td>
                            <td class="text-end fw-bold {{ $inv->remaining_amount > 0 ? 'text-danger' : 'text-muted' }}">
                                Rp {{ number_format($inv->remaining_amount, 0, ',', '.') }}
                            </td>
                            <td class="text-center">
                                @if($inv->status == 'lunas')
                                    <span class="badge bg-success-subtle text-success border border-success px-2 py-1">LUNAS</span>
                                @elseif($inv->status == 'sebagian')
                                    <span class="badge bg-warning-subtle text-warning border border-warning px-2 py-1">SEBAGIAN</span>
                                @elseif($inv->status == 'belum_bayar')
                                    <span class="badge bg-danger-subtle text-danger border border-danger px-2 py-1">BELUM BAYAR</span>
                                @else
                                    <span class="badge bg-secondary px-2 py-1">BATAL</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('finance.invoices.show', $inv) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye me-1"></i> Detail & Bayar
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="bi bi-receipt text-muted display-4 mb-3 d-block"></i>
                                <h6 class="fw-semibold text-secondary">Tidak ada data invoice ditemukan.</h6>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($invoices->hasPages())
        <div class="card-footer bg-white border-top py-3">
            {{ $invoices->links() }}
        </div>
    @endif
</div>
@endsection
