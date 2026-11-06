@extends('layouts.app')

@section('title', 'Beban Pengeluaran Operasional - Al Ghifari Haramain')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Beban Pengeluaran Operasional</h4>
        <p class="text-muted small mb-0">Catat dan pantau seluruh pengeluaran tiket, hotel, visa, muthawif, dan logistik</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('finance.summary') }}" class="btn btn-outline-secondary">
            <i class="bi bi-pie-chart me-1"></i> Ringkasan Kas
        </a>
        <button type="button" class="btn btn-danger shadow-sm" data-bs-toggle="modal" data-bs-target="#newExpenseModal">
            <i class="bi bi-dash-circle me-1"></i> Catat Pengeluaran Baru
        </button>
    </div>
</div>

<!-- Search & Filter Card -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
        <form action="{{ route('finance.expenses') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control form-control-sm border-start-0" 
                        value="{{ $search }}" placeholder="Cari No Pengeluaran atau Judul...">
                </div>
            </div>
            <div class="col-md-4">
                <select name="category_id" class="form-select form-select-sm">
                    <option value="">-- Semua Kategori Beban --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary btn-sm px-3">Filter</button>
                @if($search || $categoryId)
                    <a href="{{ route('finance.expenses') }}" class="btn btn-light btn-sm text-secondary">Reset</a>
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
                        <th>No Transaksi</th>
                        <th>Keterangan Pengeluaran</th>
                        <th>Kategori</th>
                        <th>Alokasi Kloter</th>
                        <th>Tanggal</th>
                        <th>Metode</th>
                        <th class="text-end">Nominal (Rp)</th>
                        <th>Bukti</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expenses as $exp)
                        <tr>
                            <td>
                                <span class="fw-bold text-dark">{{ $exp->expense_number }}</span>
                                <span class="text-muted small d-block">Oleh: {{ $exp->recorder->name ?? '-' }}</span>
                            </td>
                            <td>
                                <span class="fw-semibold text-dark">{{ $exp->title }}</span>
                                @if($exp->notes)
                                    <span class="text-muted small d-block">{{ $exp->notes }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-danger-subtle text-danger">{{ $exp->category->name ?? 'Operasional' }}</span>
                            </td>
                            <td>
                                @if($exp->departure)
                                    <span class="badge bg-light text-dark border">{{ $exp->departure->code }}</span>
                                @else
                                    <span class="text-muted small">Travel Umum</span>
                                @endif
                            </td>
                            <td>
                                {{ $exp->expense_date ? $exp->expense_date->format('d/m/Y') : '-' }}
                            </td>
                            <td>
                                <span class="badge bg-light text-secondary border text-uppercase">{{ $exp->payment_method }}</span>
                            </td>
                            <td class="text-end fw-bold text-danger">
                                -Rp {{ number_format($exp->amount, 0, ',', '.') }}
                            </td>
                            <td>
                                @if($exp->proof_file)
                                    <a href="{{ asset('storage/' . $exp->proof_file) }}" target="_blank" class="btn btn-sm btn-outline-secondary" title="Lihat Bukti Nota">
                                        <i class="bi bi-file-earmark-image"></i>
                                    </a>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-wallet2 display-4 mb-3 d-block"></i>
                                <h6 class="fw-semibold">Belum ada catatan pengeluaran.</h6>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($expenses->hasPages())
        <div class="card-footer bg-white border-top py-3">
            {{ $expenses->links() }}
        </div>
    @endif
</div>

<!-- Modal Catat Pengeluaran Baru -->
<div class="modal fade" id="newExpenseModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('finance.expenses.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-dark"><i class="bi bi-dash-circle text-danger me-1"></i> Catat Pengeluaran Operasional Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold small">Nama / Judul Pengeluaran <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" required placeholder="Contoh: Booking Tiket Saudia Group PNR Kloter November">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Nominal Pengeluaran (Rp) <span class="text-danger">*</span></label>
                            <input type="number" name="amount" class="form-control" min="1000" required placeholder="Contoh: 15000000">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Kategori Beban <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select" required>
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Alokasi Rombongan / Kloter (Opsional)</label>
                            <select name="departure_id" class="form-select">
                                <option value="">-- Operasional Umum Travel --</option>
                                @foreach($departures as $dep)
                                    <option value="{{ $dep->id }}">{{ $dep->code }} - {{ $dep->package->name ?? '-' }} ({{ $dep->departure_date ? $dep->departure_date->format('d/m/Y') : '' }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Tanggal Transaksi <span class="text-danger">*</span></label>
                            <input type="date" name="expense_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Metode Pembayaran <span class="text-danger">*</span></label>
                            <select name="payment_method" class="form-select" required>
                                <option value="transfer">Transfer Bank</option>
                                <option value="tunai">Kas Tunai / Cash</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Upload Bukti Nota / Kuitansi</label>
                            <input type="file" name="proof_file" class="form-control" accept="image/*,.pdf">
                            <div class="form-text small">Format JPG, PNG, atau PDF (Max: 3MB)</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Catatan Tambahan</label>
                            <input type="text" name="notes" class="form-control" placeholder="Keterangan vendor, invoice supplier, dsb.">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger px-4">
                        <i class="bi bi-save me-1"></i> Simpan Pengeluaran
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
