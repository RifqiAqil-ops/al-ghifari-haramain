@extends('layouts.app')

@section('title', 'Manajemen Pengembalian Dana (Refund) - Al Ghifari Haramain')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Manajemen Refund / Pengembalian Dana</h4>
        <p class="text-muted small mb-0">Kelola pengajuan pengembalian dana pembatalan paket jamaah dengan audit ketat</p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#newRefundModal">
            <i class="bi bi-plus-circle me-1"></i> Ajukan Refund Baru
        </button>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No Refund</th>
                        <th>Jamaah & Paket</th>
                        <th>Alasan Refund</th>
                        <th>Rekening Tujuan</th>
                        <th>Tanggal</th>
                        <th class="text-end">Nominal (Rp)</th>
                        <th class="text-center">Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($refunds as $ref)
                        <tr>
                            <td>
                                <span class="fw-bold text-dark">{{ $ref->refund_number }}</span>
                                <span class="text-muted small d-block">Invoice: {{ $ref->invoice->invoice_number ?? '-' }}</span>
                            </td>
                            <td>
                                <strong class="text-dark d-block">{{ $ref->registration->jamaah->name ?? '-' }}</strong>
                                <span class="text-muted small">{{ $ref->registration->package->name ?? '-' }}</span>
                            </td>
                            <td>
                                <span class="text-dark small">{{ Str::limit($ref->reason, 40) }}</span>
                            </td>
                            <td>
                                <span class="fw-semibold text-dark">{{ $ref->bank_name ?? 'Tunai' }}</span>
                                @if($ref->account_number)
                                    <span class="text-muted small d-block">{{ $ref->account_number }} (a.n {{ $ref->account_name }})</span>
                                @endif
                            </td>
                            <td>
                                {{ $ref->refund_date ? $ref->refund_date->format('d/m/Y') : '-' }}
                            </td>
                            <td class="text-end fw-bold text-danger">
                                Rp {{ number_format($ref->amount, 0, ',', '.') }}
                            </td>
                            <td class="text-center">
                                @if($ref->status == 'selesai')
                                    <span class="badge bg-success-subtle text-success">Selesai Dicairkan</span>
                                @elseif($ref->status == 'disetujui')
                                    <span class="badge bg-primary-subtle text-primary">Disetujui</span>
                                @elseif($ref->status == 'ditolak')
                                    <span class="badge bg-danger-subtle text-danger">Ditolak</span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning">Diajukan</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#actionModal{{ $ref->id }}">
                                    <i class="bi bi-gear me-1"></i> Proses
                                </button>

                                <!-- Modal Approval / Status -->
                                <div class="modal fade text-start" id="actionModal{{ $ref->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('finance.refunds.approve', $ref) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold">Proses Refund {{ $ref->refund_number }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="bg-light p-3 rounded-3 mb-3 small">
                                                        <div>Jamaah: <strong>{{ $ref->registration->jamaah->name ?? '-' }}</strong></div>
                                                        <div>Nominal: <strong class="text-danger">Rp {{ number_format($ref->amount, 0, ',', '.') }}</strong></div>
                                                        <div>Alasan: <em>{{ $ref->reason }}</em></div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold small">Perbarui Status</label>
                                                        <select name="status" class="form-select" required>
                                                            <option value="diajukan" {{ $ref->status == 'diajukan' ? 'selected' : '' }}>Diajukan (Review)</option>
                                                            <option value="disetujui" {{ $ref->status == 'disetujui' ? 'selected' : '' }}>Disetujui (Menunggu Pencairan)</option>
                                                            <option value="selesai" {{ $ref->status == 'selesai' ? 'selected' : '' }}>Selesai (Sudah Ditransfer ke Jamaah)</option>
                                                            <option value="ditolak" {{ $ref->status == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                                                        </select>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold small">Catatan Persetujuan / Alasan Penolakan</label>
                                                        <textarea name="notes" class="form-control" rows="3" placeholder="Catatan internal manajemen...">{{ $ref->notes }}</textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                                                    <button type="submit" class="btn btn-primary">Simpan Status</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-arrow-counterclockwise display-4 mb-3 d-block"></i>
                                <h6 class="fw-semibold">Belum ada pengajuan pengembalian dana (refund).</h6>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($refunds->hasPages())
        <div class="card-footer bg-white border-top py-3">
            {{ $refunds->links() }}
        </div>
    @endif
</div>

<!-- Modal Ajukan Refund Baru -->
<div class="modal fade" id="newRefundModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('finance.refunds.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-dark"><i class="bi bi-arrow-counterclockwise text-primary me-1"></i> Form Pengajuan Refund Jamaah</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Pilih Pendaftaran Jamaah <span class="text-danger">*</span></label>
                            <select name="registration_id" class="form-select" required>
                                <option value="">-- Pilih Jamaah Terdaftar --</option>
                                @foreach($registrations as $reg)
                                    <option value="{{ $reg->id }}">
                                        {{ $reg->registration_number }} - {{ $reg->jamaah->name ?? '-' }} (Paket: {{ $reg->package->name ?? '-' }}, Terbayar: Rp {{ number_format($reg->invoice->paid_amount ?? 0, 0, ',', '.') }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Nominal Pengembalian (Rp) <span class="text-danger">*</span></label>
                            <input type="number" name="amount" class="form-control" min="1000" required placeholder="Contoh: 5000000">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Tanggal Pengajuan <span class="text-danger">*</span></label>
                            <input type="date" name="refund_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Metode Pengembalian <span class="text-danger">*</span></label>
                            <select name="payment_method" class="form-select" required>
                                <option value="transfer">Transfer Bank Rekening Jamaah</option>
                                <option value="tunai">Tunai di Kantor Travel</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Nama Bank Jamaah</label>
                            <input type="text" name="bank_name" class="form-control" placeholder="Contoh: BCA / Mandiri / BSI">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Nomor Rekening</label>
                            <input type="text" name="account_number" class="form-control" placeholder="Nomor rekening tujuan">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Atas Nama Rekening</label>
                            <input type="text" name="account_name" class="form-control" placeholder="Nama pemilik rekening">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold small">Alasan Pengembalian / Pembatalan <span class="text-danger">*</span></label>
                            <textarea name="reason" class="form-control" rows="3" required placeholder="Jelaskan alasan pengajuan refund secara lengkap..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-save me-1"></i> Simpan Pengajuan Refund
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
