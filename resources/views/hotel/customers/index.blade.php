@extends('layouts.app')

@section('title', 'Daftar Pelanggan Hotel')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark"><i class="bi bi-people text-primary me-2"></i>Pelanggan &amp; Travel Partner</h4>
            <p class="text-muted small mb-0">Kelola Data Agen, Tour Operator, dan Korporat Pembeli Kamar Hotel</p>
        </div>
        <button type="button" class="btn btn-primary shadow-sm btn-sm" data-bs-toggle="modal" data-bs-target="#customerModal">
            <i class="bi bi-plus-circle me-1"></i> Tambah Pelanggan Baru
        </button>
    </div>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 13.5px;">
                    <thead class="table-light">
                        <tr>
                            <th>Kode</th>
                            <th>Nama / Perusahaan</th>
                            <th>Tipe Pelanggan</th>
                            <th>Kontak</th>
                            <th>Term Pembayaran</th>
                            <th class="text-center">Total Reservasi</th>
                            <th class="text-center">Total Invoice</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $c)
                        <tr>
                            <td><span class="badge bg-light text-dark border">{{ $c->customer_code }}</span></td>
                            <td>
                                <strong>{{ $c->name }}</strong>
                                <span class="d-block text-muted small">{{ $c->company_name ?? 'Individual' }}</span>
                            </td>
                            <td><span class="badge bg-info-subtle text-info border">{{ ucfirst(str_replace('_', ' ', $c->customer_type)) }}</span></td>
                            <td>
                                <div>{{ $c->phone ?? '-' }}</div>
                                <small class="text-muted">{{ $c->email ?? '-' }}</small>
                            </td>
                            <td>{{ $c->payment_terms_days }} Hari</td>
                            <td class="text-center fw-bold">{{ $c->reservations_count }}</td>
                            <td class="text-center fw-bold">{{ $c->invoices_count }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">Belum ada data pelanggan hotel.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($customers->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $customers->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Modal Create Customer -->
<div class="modal fade" id="customerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('hotel.customers.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-dark">Tambah Pelanggan Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Nama Kontak / Penanggung Jawab <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="Contoh: H. Ahmad Fauzi" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Nama Perusahaan / Travel Agent</label>
                        <input type="text" name="company_name" class="form-control" placeholder="Contoh: Syahadah Travel">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Tipe Pelanggan <span class="text-danger">*</span></label>
                        <select name="customer_type" class="form-select" required>
                            <option value="travel_agency" selected>Travel Agency</option>
                            <option value="tour_operator">Tour Operator</option>
                            <option value="corporate">Corporate</option>
                            <option value="individual">Individual</option>
                            <option value="partner">Partner</option>
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Telepon / WhatsApp</label>
                            <input type="text" name="phone" class="form-control" placeholder="0812...">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Syarat Bayar (Hari) <span class="text-danger">*</span></label>
                            <input type="number" name="payment_terms_days" class="form-control" value="14" min="0" required>
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold small">Email</label>
                        <input type="email" name="email" class="form-control" placeholder="agency@example.com">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-1"></i> Simpan Pelanggan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
