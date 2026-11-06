@extends('layouts.app')

@section('title', 'Detail Invoice - ' . $invoice->invoice_number)

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <a href="{{ route('finance.invoices') }}" class="btn btn-sm btn-outline-secondary mb-2">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Invoice
        </a>
        <div class="d-flex align-items-center gap-2">
            <h4 class="fw-bold mb-0 text-dark">{{ $invoice->invoice_number }}</h4>
            <span class="badge 
                @if($invoice->status == 'lunas') bg-success
                @elseif($invoice->status == 'sebagian') bg-warning text-dark
                @elseif($invoice->status == 'belum_bayar') bg-danger
                @else bg-secondary @endif rounded-pill px-3 py-1 text-uppercase">
                {{ str_replace('_', ' ', $invoice->status) }}
            </span>
        </div>
        <p class="text-muted small mb-0">
            Diterbitkan: {{ $invoice->invoice_date ? $invoice->invoice_date->format('d F Y') : '-' }} | 
            Jatuh Tempo: {{ $invoice->due_date ? $invoice->due_date->format('d F Y') : '-' }}
        </p>
    </div>
    <div class="d-flex gap-2">
        @if($invoice->remaining_amount > 0 && $invoice->status != 'dibatalkan')
            <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#paymentModal">
                <i class="bi bi-cash-coin me-1"></i> Catat Pembayaran Baru
            </button>
        @endif
    </div>
</div>

<div class="row g-4">
    <!-- Kolom Kiri: Rincian Tagihan & Profil Jamaah -->
    <div class="col-lg-4">
        <!-- Card Profil Jamaah -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-person-circle me-1 text-primary"></i> Data Jamaah</h6>
            </div>
            <div class="card-body p-3">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="avatar-circle bg-primary-subtle text-primary fw-bold fs-5" style="width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        {{ strtoupper(substr($invoice->registration->jamaah->name ?? 'J', 0, 1)) }}
                    </div>
                    <div>
                        <h6 class="fw-bold text-dark mb-0">
                            <a href="{{ route('jamaahs.show', $invoice->registration->jamaah_id) }}" class="text-dark text-decoration-none">
                                {{ $invoice->registration->jamaah->name ?? '-' }}
                            </a>
                        </h6>
                        <span class="text-muted small">Reg: {{ $invoice->registration->registration_number }}</span>
                    </div>
                </div>

                <div class="bg-light p-3 rounded-3 small">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">No Telepon/WA:</span>
                        <a href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $invoice->registration->jamaah->phone ?? '')) }}" target="_blank" class="text-success text-decoration-none fw-semibold">
                            <i class="bi bi-whatsapp me-1"></i>{{ $invoice->registration->jamaah->phone ?? '-' }}
                        </a>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">NIK:</span>
                        <span class="text-dark fw-mono">{{ $invoice->registration->jamaah->nik ?? '-' }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">No Paspor:</span>
                        <span class="text-dark fw-bold">{{ $invoice->registration->jamaah->passport_number ?? 'Belum ada' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Paket & Rombongan -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-box-seam me-1 text-primary"></i> Rincian Paket</h6>
            </div>
            <div class="card-body p-3 small">
                <div class="mb-2">
                    <span class="text-muted d-block">Nama Paket:</span>
                    <strong class="text-dark fs-6">{{ $invoice->registration->package->name ?? '-' }}</strong>
                    <span class="badge bg-secondary-subtle text-secondary">{{ strtoupper($invoice->registration->package->type ?? 'UMRAH') }}</span>
                </div>
                <div class="mb-2">
                    <span class="text-muted d-block">Rombongan / Kloter:</span>
                    <strong class="text-dark">{{ $invoice->registration->departure->code ?? 'Belum ditentukan' }}</strong>
                    <span class="text-muted d-block">Berangkat: {{ $invoice->registration->departure->departure_date ? $invoice->registration->departure->departure_date->format('d M Y') : '-' }}</span>
                </div>
                <div>
                    <span class="text-muted d-block">Tipe Kamar Pilihan:</span>
                    <span class="badge bg-light text-dark border text-uppercase">{{ $invoice->registration->room_type }}</span>
                </div>
            </div>
        </div>

        <!-- Rekening Pembayaran Resmi -->
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-bold mb-0 text-success"><i class="bi bi-bank me-1"></i> Rekening Resmi Travel</h6>
            </div>
            <div class="card-body p-3 small">
                @forelse($bankAccounts as $bank)
                    <div class="p-2 border rounded-3 mb-2 bg-light">
                        <strong class="text-dark d-block">{{ $bank->bank_name }}</strong>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-mono fw-bold fs-6 text-primary">{{ $bank->account_number }}</span>
                            <span class="text-muted small">a.n {{ $bank->account_name }}</span>
                        </div>
                    </div>
                @empty
                    <p class="text-muted mb-0">Belum ada rekening aktif yang terdaftar.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Perhitungan Keuangan & Riwayat Pembayaran -->
    <div class="col-lg-8">
        <!-- Breakdown Card -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-calculator me-1 text-primary"></i> Rincian Nilai Tagihan</h6>
            </div>
            <div class="card-body p-4">
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded-3 text-center">
                            <span class="text-muted small d-block">Total Nilai Tagihan</span>
                            <h4 class="fw-bold text-dark mb-0 mt-1">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</h4>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-success-subtle rounded-3 text-center">
                            <span class="text-success small d-block">Total Pembayaran Valid</span>
                            <h4 class="fw-bold text-success mb-0 mt-1">Rp {{ number_format($invoice->paid_amount, 0, ',', '.') }}</h4>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-danger-subtle rounded-3 text-center">
                            <span class="text-danger small d-block">Sisa Tagihan Belum Lunas</span>
                            <h4 class="fw-bold text-danger mb-0 mt-1">Rp {{ number_format($invoice->remaining_amount, 0, ',', '.') }}</h4>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-borderless mb-0">
                        <tbody>
                            <tr>
                                <td class="text-muted">Biaya Paket Dasar</td>
                                <td class="text-end fw-semibold">Rp {{ number_format($invoice->registration->package_price ?? 0, 0, ',', '.') }}</td>
                            </tr>
                            @if(($invoice->registration->room_surcharge ?? 0) > 0)
                                <tr>
                                    <td class="text-muted">Upgrade Tipe Kamar ({{ strtoupper($invoice->registration->room_type) }})</td>
                                    <td class="text-end fw-semibold">+Rp {{ number_format($invoice->registration->room_surcharge, 0, ',', '.') }}</td>
                                </tr>
                            @endif
                            @if(($invoice->discount_amount ?? 0) > 0)
                                <tr>
                                    <td class="text-muted">Potongan Diskon Khusus</td>
                                    <td class="text-end fw-semibold text-danger">-Rp {{ number_format($invoice->discount_amount, 0, ',', '.') }}</td>
                                </tr>
                            @endif
                            <tr class="border-top">
                                <td class="fw-bold fs-6 pt-2">Total Kewajiban Tagihan</td>
                                <td class="text-end fw-bold fs-6 pt-2 text-dark">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Riwayat Pembayaran Masuk -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-success"><i class="bi bi-journal-check me-1"></i> Riwayat Pembayaran Masuk</h6>
                @if($invoice->remaining_amount > 0 && $invoice->status != 'dibatalkan')
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#paymentModal">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Pembayaran
                    </button>
                @endif
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>No Kwitansi</th>
                                <th>Tipe & Metode</th>
                                <th>Tanggal Bayar</th>
                                <th class="text-end">Nominal</th>
                                <th class="text-center">Status</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($invoice->payments as $pay)
                                <tr>
                                    <td>
                                        <span class="fw-bold text-dark">{{ $pay->receipt_number }}</span>
                                        <span class="text-muted small d-block">Oleh: {{ $pay->sender_name ?? '-' }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border text-uppercase">{{ $pay->payment_type }}</span>
                                        <span class="text-muted small d-block">{{ strtoupper($pay->payment_method) }} ({{ $pay->bank_name ?? 'Tunai' }})</span>
                                    </td>
                                    <td>
                                        {{ $pay->payment_date ? $pay->payment_date->format('d/m/Y') : '-' }}
                                        <span class="text-muted small d-block">Penerima: {{ $pay->receiver->name ?? '-' }}</span>
                                    </td>
                                    <td class="text-end fw-bold {{ $pay->status == 'valid' ? 'text-success' : 'text-decoration-line-through text-muted' }}">
                                        Rp {{ number_format($pay->amount, 0, ',', '.') }}
                                    </td>
                                    <td class="text-center">
                                        @if($pay->status == 'valid')
                                            <span class="badge bg-success-subtle text-success">Valid</span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger" title="{{ $pay->cancellation_reason }}">Dibatalkan</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-1">
                                            @if($pay->status == 'valid')
                                                <a href="{{ route('finance.receipt', $pay) }}" target="_blank" class="btn btn-sm btn-outline-danger" title="Cetak Kwitansi">
                                                    <i class="bi bi-printer"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#cancelPayModal{{ $pay->id }}" title="Batalkan Pembayaran">
                                                    <i class="bi bi-x-circle"></i>
                                                </button>
                                            @endif
                                        </div>

                                        <!-- Modal Pembatalan Pembayaran -->
                                        @if($pay->status == 'valid')
                                            <div class="modal fade text-start" id="cancelPayModal{{ $pay->id }}" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <form action="{{ route('finance.payments.cancel', $pay) }}" method="POST">
                                                            @csrf
                                                            @method('PATCH')
                                                            <div class="modal-header">
                                                                <h5 class="modal-title text-danger fw-bold"><i class="bi bi-exclamation-triangle me-1"></i> Batalkan Pembayaran</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p class="small text-muted">
                                                                    Anda akan membatalkan pembayaran senilai <strong>Rp {{ number_format($pay->amount, 0, ',', '.') }}</strong> dengan kwitansi <strong>{{ $pay->receipt_number }}</strong>.
                                                                    Tindakan ini akan mengembalikan sisa tagihan invoice dan dicatat ke Audit Log.
                                                                </p>
                                                                <div class="mb-3">
                                                                    <label class="form-label fw-semibold small">Alasan Pembatalan <span class="text-danger">*</span></label>
                                                                    <textarea name="cancellation_reason" class="form-control" rows="3" required placeholder="Contoh: Salah input nominal / transfer duplikat"></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                                                                <button type="submit" class="btn btn-danger">Batalkan Pembayaran</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">Belum ada pembayaran yang tercatat.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Catat Pembayaran Baru -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('finance.payments.store', $invoice) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-dark"><i class="bi bi-cash-stack text-success me-1"></i> Catat Pembayaran Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info py-2 small mb-4">
                        Sisa tagihan yang harus dibayar: <strong>Rp {{ number_format($invoice->remaining_amount, 0, ',', '.') }}</strong>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Nominal Pembayaran (Rp) <span class="text-danger">*</span></label>
                            <input type="number" name="amount" class="form-control" 
                                value="{{ old('amount', $invoice->remaining_amount) }}" max="{{ $invoice->remaining_amount }}" min="1000" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Tanggal Pembayaran <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" class="form-control" value="{{ old('payment_date', date('Y-m-d')) }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Tipe Pembayaran <span class="text-danger">*</span></label>
                            <select name="payment_type" class="form-select" required>
                                <option value="dp" {{ $invoice->paid_amount == 0 ? 'selected' : '' }}>Uang Muka (DP)</option>
                                <option value="cicilan" {{ $invoice->paid_amount > 0 ? 'selected' : '' }}>Cicilan / Angsuran</option>
                                <option value="pelunasan">Pelunasan</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Metode Pembayaran <span class="text-danger">*</span></label>
                            <select name="payment_method" class="form-select" required>
                                <option value="transfer">Transfer Bank</option>
                                <option value="tunai">Tunai / Cash</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Nama Bank Tujuan / Rekening</label>
                            <input type="text" name="bank_name" class="form-control" placeholder="Contoh: Bank Syariah Indonesia (BSI)">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Nama Pengirim / Rekening Asal</label>
                            <input type="text" name="sender_name" class="form-control" value="{{ $invoice->registration->jamaah->name ?? '' }}" placeholder="Nama penyetor">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Upload Bukti Transfer / Slip Pembayaran</label>
                            <input type="file" name="proof_file" class="form-control" accept="image/*,.pdf">
                            <div class="form-text small">Format JPG, PNG, atau PDF (Max: 3MB)</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Catatan Tambahan</label>
                            <input type="text" name="notes" class="form-control" placeholder="Keterangan transaksi...">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-save me-1"></i> Simpan Pembayaran & Terbitkan Kwitansi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
