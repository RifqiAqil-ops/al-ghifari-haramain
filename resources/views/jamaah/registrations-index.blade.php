@extends('layouts.app')

@section('title', 'Data Registrasi Paket')
@section('header_title', 'Daftar Registrasi Paket Jamaah')

@section('content')
<div class="card card-custom p-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h5 class="fw-bold text-forest-green mb-1">
                <i class="bi bi-card-checklist text-royal-gold me-2"></i> Riwayat Pendaftaran Paket
            </h5>
            <p class="text-muted small mb-0">Seluruh data jamaah yang terdaftar dalam paket Haji dan Umrah beserta status keberangkatan &amp; tagihan</p>
        </div>
        <a href="{{ route('registrations.create') }}" class="btn btn-sm btn-forest-green">
            <i class="bi bi-plus-circle me-1"></i> Daftarkan Jamaah ke Paket
        </a>
    </div>

    <!-- Search & Filter -->
    <form action="{{ route('registrations.index') }}" method="GET" class="row g-2 mb-3">
        <div class="col-md-6">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Cari nomor registrasi, nama jamaah, NIK..." value="{{ $search }}">
            </div>
        </div>
        <div class="col-md-4">
            <select name="status" class="form-select form-select-sm">
                <option value="">Semua Status Pendaftaran</option>
                <option value="terdaftar" {{ $status === 'terdaftar' ? 'selected' : '' }}>Terdaftar</option>
                <option value="proses" {{ $status === 'proses' ? 'selected' : '' }}>Proses Kelengkapan</option>
                <option value="siap_berangkat" {{ $status === 'siap_berangkat' ? 'selected' : '' }}>Siap Berangkat</option>
                <option value="berangkat" {{ $status === 'berangkat' ? 'selected' : '' }}>Berangkat</option>
                <option value="selesai" {{ $status === 'selesai' ? 'selected' : '' }}>Selesai</option>
                <option value="batal" {{ $status === 'batal' ? 'selected' : '' }}>Dibatalkan</option>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-1">
            <button type="submit" class="btn btn-sm btn-forest-green flex-grow-1">Filter</button>
            @if($search || $status)
                <a href="{{ route('registrations.index') }}" class="btn btn-sm btn-light border">Reset</a>
            @endif
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-custom table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>No. Registrasi</th>
                    <th>Nama Jamaah</th>
                    <th>Paket Perjalanan</th>
                    <th>Jadwal Keberangkatan</th>
                    <th>Tipe Kamar</th>
                    <th>Biaya Akhir</th>
                    <th>Tagihan / Sisa</th>
                    <th>Status</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($registrations as $reg)
                    <tr>
                        <td class="fw-bold font-monospace text-forest-green small">
                            {{ $reg->registration_number }}
                        </td>
                        <td>
                            <a href="{{ route('jamaah.show', $reg->jamaah_id) }}" class="fw-bold text-dark text-decoration-none">
                                {{ $reg->jamaah->name ?? 'Jamaah' }}
                            </a>
                            <small class="text-muted d-block" style="font-size: 0.72rem;">NIK: {{ $reg->jamaah->nik ?? '-' }}</small>
                        </td>
                        <td>
                            <span class="fw-semibold text-dark small d-block">{{ $reg->package->name ?? '-' }}</span>
                            <span class="badge bg-light text-muted border" style="font-size: 0.65rem;">
                                {{ ucfirst($reg->package->type ?? '') }}
                            </span>
                        </td>
                        <td class="small">
                            <div><i class="bi bi-calendar-check me-1 text-royal-gold"></i>{{ $reg->departure->departure_date ? $reg->departure->departure_date->translatedFormat('d M Y') : '-' }}</div>
                            <small class="text-muted">{{ $reg->departure->code ?? '' }} ({{ $reg->departure->airline ?? '' }})</small>
                        </td>
                        <td class="small">
                            {{ $reg->room_type_label }}
                        </td>
                        <td class="small fw-bold text-dark">
                            Rp {{ number_format($reg->final_price, 0, ',', '.') }}
                        </td>
                        <td class="small">
                            @if($reg->invoice)
                                <a href="{{ route('finance.invoices.show', $reg->invoice) }}" class="text-decoration-none">
                                    <span class="badge {{ $reg->invoice->status === 'lunas' ? 'badge-subtle-success' : 'badge-subtle-danger' }}">
                                        {{ $reg->invoice->status_label }}
                                    </span>
                                </a>
                                <small class="text-muted d-block" style="font-size: 0.72rem;">
                                    Sisa: Rp {{ number_format($reg->invoice->remaining_amount, 0, ',', '.') }}
                                </small>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $badgeColor = match($reg->status) {
                                    'siap_berangkat' => 'badge-subtle-success',
                                    'terdaftar', 'proses' => 'badge-subtle-info',
                                    'berangkat', 'selesai' => 'badge-subtle-success',
                                    default => 'badge-subtle-danger',
                                };
                            @endphp
                            <span class="badge {{ $badgeColor }} px-2 py-1">
                                {{ $reg->status_label }}
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('registrations.show', $reg) }}" class="btn btn-light border text-forest-green" title="Detail Registrasi">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if($reg->status !== 'batal')
                                    <button class="btn btn-light border text-danger" data-bs-toggle="modal" data-bs-target="#cancelRegModal{{ $reg->id }}" title="Batalkan Pendaftaran">
                                        <i class="bi bi-x-circle"></i>
                                    </button>
                                @endif
                            </div>

                            <!-- Modal Batalkan -->
                            @if($reg->status !== 'batal')
                                <div class="modal fade" id="cancelRegModal{{ $reg->id }}" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content text-start">
                                            <form action="{{ route('registrations.cancel', $reg) }}" method="POST">
                                                @csrf
                                                <div class="modal-header">
                                                    <h6 class="modal-title fw-bold text-danger">Batalkan Pendaftaran: {{ $reg->registration_number }}</h6>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p class="small text-muted mb-3">
                                                        Pendaftaran untuk jamaah <strong>{{ $reg->jamaah->name }}</strong> akan dibatalkan dan status invoice akan disesuaikan.
                                                    </p>
                                                    <label for="reason{{ $reg->id }}" class="form-label small fw-semibold">Alasan Pembatalan <span class="text-danger">*</span></label>
                                                    <textarea name="cancellation_reason" id="reason{{ $reg->id }}" rows="3" class="form-control form-control-sm" placeholder="Contoh: Pengunduran diri karena kondisi kesehatan, mutasi tugas" required></textarea>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Tutup</button>
                                                    <button type="submit" class="btn btn-sm btn-danger">Konfirmasi Batalkan</button>
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
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="bi bi-card-checklist fs-1 d-block mb-2 text-muted"></i>
                            Belum ada riwayat registrasi paket yang ditemukan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $registrations->links() }}
    </div>
</div>
@endsection
