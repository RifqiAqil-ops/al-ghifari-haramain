@extends('layouts.app')

@section('title', 'Detail Jamaah - ' . $jamaah->name)
@section('header_title', 'Profil & Riwayat Jamaah')

@section('content')
<div class="row g-4 mb-4">
    <!-- Left Column: Profile Card -->
    <div class="col-lg-4">
        <div class="card card-custom p-4 text-center mb-4">
            <div class="mb-3">
                @if($jamaah->photo)
                    <img src="{{ asset('storage/' . $jamaah->photo) }}" alt="Foto {{ $jamaah->name }}" class="rounded-circle shadow" style="width: 100px; height: 100px; object-fit: cover; border: 3px solid #C9A45B;">
                @else
                    <div class="rounded-circle bg-forest-green text-royal-gold fw-bold mx-auto d-flex align-items-center justify-content-center shadow" style="width: 100px; height: 100px; font-size: 2.5rem; border: 3px solid #C9A45B;">
                        {{ strtoupper(substr($jamaah->name, 0, 1)) }}
                    </div>
                @endif
            </div>

            <h5 class="fw-bold text-dark mb-1">{{ $jamaah->name }}</h5>
            <div class="font-monospace text-forest-green fw-bold small mb-2">{{ $jamaah->registration_number }}</div>

            <div class="d-flex justify-content-center gap-2 mb-3">
                <span class="badge {{ $jamaah->status === 'aktif' ? 'badge-subtle-success' : 'badge-subtle-danger' }} px-3 py-1">
                    Status: {{ ucfirst($jamaah->status) }}
                </span>
                <span class="badge bg-light text-dark border px-2 py-1">
                    {{ $jamaah->gender_label }}
                </span>
            </div>

            <div class="d-grid gap-2 mb-3">
                <a href="{{ route('registrations.create', ['jamaah_id' => $jamaah->id]) }}" class="btn btn-sm btn-royal-gold">
                    <i class="bi bi-box-arrow-in-up-right me-1"></i> Daftarkan ke Paket Baru
                </a>
                <div class="btn-group btn-group-sm">
                    <a href="{{ route('jamaah.edit', $jamaah) }}" class="btn btn-light border text-primary">
                        <i class="bi bi-pencil me-1"></i> Edit Data
                    </a>
                    <a href="{{ route('jamaah.print-form', $jamaah) }}" target="_blank" class="btn btn-light border text-dark">
                        <i class="bi bi-printer me-1"></i> Cetak Formulir
                    </a>
                </div>
                @can('jamaah.delete')
                <form action="{{ route('jamaah.destroy', $jamaah) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data jamaah {{ $jamaah->name }}?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger w-100">
                        <i class="bi bi-trash me-1"></i> Hapus Data Jamaah
                    </button>
                </form>
                @endcan
            </div>

            <hr class="my-3 opacity-25">

            <div class="text-start small">
                <div class="mb-2">
                    <span class="text-muted d-block" style="font-size: 0.72rem;">NIK:</span>
                    <strong class="text-dark">{{ $jamaah->nik }}</strong>
                </div>
                <div class="mb-2">
                    <span class="text-muted d-block" style="font-size: 0.72rem;">Nomor Paspor:</span>
                    <strong class="text-dark">{{ $jamaah->passport_number ?? 'Belum Diisi' }}</strong>
                    @if($jamaah->passport_expiry_date)
                        <span class="text-muted d-block" style="font-size: 0.72rem;">Berlaku s/d: {{ $jamaah->passport_expiry_date->format('d/m/Y') }}</span>
                    @endif
                </div>
                <div class="mb-2">
                    <span class="text-muted d-block" style="font-size: 0.72rem;">Tempat, Tanggal Lahir:</span>
                    <strong class="text-dark">{{ $jamaah->birth_place ?? '-' }}, {{ $jamaah->birth_date ? $jamaah->birth_date->translatedFormat('d F Y') : '-' }}</strong>
                </div>
                <div class="mb-2">
                    <span class="text-muted d-block" style="font-size: 0.72rem;">Nomor WhatsApp:</span>
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $jamaah->phone) }}" target="_blank" class="text-success fw-bold text-decoration-none">
                        <i class="bi bi-whatsapp me-1"></i>{{ $jamaah->phone }}
                    </a>
                </div>
                <div class="mb-2">
                    <span class="text-muted d-block" style="font-size: 0.72rem;">Email:</span>
                    <span class="text-dark">{{ $jamaah->email ?? '-' }}</span>
                </div>
                <div class="mb-0">
                    <span class="text-muted d-block" style="font-size: 0.72rem;">Alamat:</span>
                    <span class="text-dark">{{ $jamaah->address ?? '-' }}, {{ $jamaah->city ?? '' }} {{ $jamaah->province ?? '' }}</span>
                </div>
            </div>

            @if($jamaah->special_needs)
                <div class="alert alert-warning text-start small mt-3 mb-0 py-2">
                    <div class="fw-bold"><i class="bi bi-heart-pulse-fill me-1 text-danger"></i> Kebutuhan Khusus:</div>
                    {{ $jamaah->special_needs }}
                </div>
            @endif
        </div>
    </div>

    <!-- Right Column: Tabs (Dokumen, Registrasi Paket, Kontak, WhatsApp) -->
    <div class="col-lg-8">
        <div class="card card-custom p-4">
            <ul class="nav nav-tabs nav-fill mb-4" id="jamaahDetailTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active fw-semibold text-forest-green" id="docs-tab" data-bs-toggle="tab" data-bs-target="#docs-pane" type="button">
                        <i class="bi bi-folder-check me-1 text-royal-gold"></i> Berkas Digital ({{ $jamaah->documents->count() }})
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link fw-semibold text-forest-green" id="regs-tab" data-bs-toggle="tab" data-bs-target="#regs-pane" type="button">
                        <i class="bi bi-briefcase me-1 text-royal-gold"></i> Riwayat Paket ({{ $jamaah->registrations->count() }})
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link fw-semibold text-forest-green" id="contacts-tab" data-bs-toggle="tab" data-bs-target="#contacts-pane" type="button">
                        <i class="bi bi-telephone-inbound me-1 text-royal-gold"></i> Kontak Darurat
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link fw-semibold text-forest-green" id="wa-tab" data-bs-toggle="tab" data-bs-target="#wa-pane" type="button">
                        <i class="bi bi-whatsapp me-1 text-success"></i> Pesan WA
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="jamaahTabContent">
                <!-- Tab 1: Dokumen Digital -->
                <div class="tab-pane fade show active" id="docs-pane" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold text-dark mb-0">Daftar Dokumen Digital Jamaah</h6>
                        <button class="btn btn-sm btn-forest-green" data-bs-toggle="modal" data-bs-target="#uploadDocModal">
                            <i class="bi bi-upload me-1"></i> Unggah Dokumen
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-custom align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Jenis Dokumen</th>
                                    <th>Nama Berkas</th>
                                    <th>Status Verifikasi</th>
                                    <th>Tanggal Unggah</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($jamaah->documents as $doc)
                                    <tr>
                                        <td>
                                            <span class="badge bg-light text-forest-green border fw-semibold">
                                                {{ $doc->document_type_label }}
                                            </span>
                                        </td>
                                        <td class="small fw-semibold text-dark">
                                            {{ $doc->document_name }}
                                        </td>
                                        <td>
                                            @php
                                                $vBadge = match($doc->status) {
                                                    'diverifikasi' => 'badge-subtle-success',
                                                    'ditolak' => 'badge-subtle-danger',
                                                    default => 'badge-subtle-warning',
                                                };
                                            @endphp
                                            <span class="badge {{ $vBadge }} px-2 py-1">
                                                {{ ucfirst(str_replace('_', ' ', $doc->status)) }}
                                            </span>
                                        </td>
                                        <td class="small text-muted">
                                            {{ $doc->created_at->format('d/m/Y') }}
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('jamaah.documents.download', $doc) }}" class="btn btn-light border text-forest-green" title="Unduh Berkas">
                                                    <i class="bi bi-download"></i>
                                                </a>
                                                <button class="btn btn-light border text-primary" data-bs-toggle="modal" data-bs-target="#verifyDocModal{{ $doc->id }}" title="Verifikasi">
                                                    <i class="bi bi-check-circle"></i>
                                                </button>
                                            </div>

                                            <!-- Modal Verifikasi -->
                                            <div class="modal fade" id="verifyDocModal{{ $doc->id }}" tabindex="-1">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <form action="{{ route('jamaah.documents.verify', $doc) }}" method="POST">
                                                            @csrf
                                                            <div class="modal-header">
                                                                <h6 class="modal-title fw-bold">Verifikasi Dokumen: {{ $doc->document_name }}</h6>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body text-start">
                                                                <div class="mb-3">
                                                                    <label class="form-label small fw-semibold">Status Validasi</label>
                                                                    <select name="status" class="form-select form-select-sm">
                                                                        <option value="diverifikasi" {{ $doc->status === 'diverifikasi' ? 'selected' : '' }}>Diverifikasi &amp; Sah</option>
                                                                        <option value="ditolak" {{ $doc->status === 'ditolak' ? 'selected' : '' }}>Ditolak (Berkas Buram / Tidak Sesuai)</option>
                                                                        <option value="menunggu_verifikasi" {{ $doc->status === 'menunggu_verifikasi' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                                                                    </select>
                                                                </div>
                                                                <div class="mb-2">
                                                                    <label class="form-label small fw-semibold">Catatan Verifikasi</label>
                                                                    <textarea name="verification_note" rows="2" class="form-control form-control-sm" placeholder="Opsional...">{{ $doc->verification_note }}</textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Batal</button>
                                                                <button type="submit" class="btn btn-sm btn-forest-green">Simpan Verifikasi</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted small">
                                            Belum ada dokumen yang diunggah untuk jamaah ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab 2: Riwayat Pendaftaran Paket -->
                <div class="tab-pane fade" id="regs-pane" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold text-dark mb-0">Riwayat Pendaftaran Paket Haji &amp; Umrah</h6>
                        <a href="{{ route('registrations.create', ['jamaah_id' => $jamaah->id]) }}" class="btn btn-sm btn-forest-green">
                            <i class="bi bi-plus-circle me-1"></i> Daftar Paket
                        </a>
                    </div>

                    @forelse($jamaah->registrations as $reg)
                        <div class="card p-3 border mb-3 rounded-3 shadow-none">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <span class="badge bg-forest-green text-royal-gold font-monospace me-1">{{ $reg->registration_number }}</span>
                                    <h6 class="fw-bold text-dark mb-0 mt-1">{{ $reg->package->name }}</h6>
                                </div>
                                <span class="badge {{ $reg->status === 'siap_berangkat' ? 'badge-subtle-success' : 'badge-subtle-info' }} px-2 py-1">
                                    {{ $reg->status_label }}
                                </span>
                            </div>
                            <div class="row g-2 text-muted small mb-2">
                                <div class="col-sm-6">
                                    <i class="bi bi-calendar-event me-1 text-royal-gold"></i> Berangkat: <strong>{{ $reg->departure->departure_date->translatedFormat('d M Y') }}</strong>
                                </div>
                                <div class="col-sm-6">
                                    <i class="bi bi-door-closed me-1 text-royal-gold"></i> Tipe Kamar: <strong>{{ $reg->room_type_label }}</strong>
                                </div>
                                <div class="col-sm-6">
                                    <i class="bi bi-tag me-1 text-royal-gold"></i> Biaya Paket: <strong>Rp {{ number_format($reg->final_price, 0, ',', '.') }}</strong>
                                </div>
                                <div class="col-sm-6">
                                    <i class="bi bi-cash-stack me-1 text-royal-gold"></i> Terbayar: <strong class="text-success">Rp {{ number_format($reg->invoice->paid_amount ?? 0, 0, ',', '.') }}</strong>
                                </div>
                            </div>

                            @if($reg->invoice)
                                <div class="p-2 rounded bg-light d-flex justify-content-between align-items-center">
                                    <span class="small">
                                        Status Tagihan: 
                                        <strong class="{{ $reg->invoice->status === 'lunas' ? 'text-success' : 'text-danger' }}">
                                            {{ $reg->invoice->status_label }}
                                        </strong>
                                        (Sisa: Rp {{ number_format($reg->invoice->remaining_amount, 0, ',', '.') }})
                                    </span>
                                    <a href="{{ route('finance.invoices.show', $reg->invoice) }}" class="btn btn-xs btn-outline-secondary px-3 py-1">
                                        Rincian Tagihan &rarr;
                                    </a>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted small">
                            Jamaah ini belum memiliki riwayat pendaftaran paket aktif.
                        </div>
                    @endforelse
                </div>

                <!-- Tab 3: Kontak Darurat -->
                <div class="tab-pane fade" id="contacts-pane" role="tabpanel">
                    <h6 class="fw-bold text-dark mb-3">Kontak Keluarga / Pendamping Darurat</h6>
                    @forelse($jamaah->contacts as $contact)
                        <div class="p-3 mb-2 rounded bg-light border d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold text-dark mb-1">{{ $contact->name }}</h6>
                                <span class="badge bg-secondary text-white small me-2">{{ ucfirst($contact->relationship) }}</span>
                                <span class="text-muted small"><i class="bi bi-telephone me-1"></i>{{ $contact->phone }}</span>
                            </div>
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $contact->phone) }}" target="_blank" class="btn btn-sm btn-outline-success">
                                <i class="bi bi-whatsapp me-1"></i> Hubungi
                            </a>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted small">
                            Belum ada kontak darurat yang didaftarkan.
                        </div>
                    @endforelse
                </div>

                <!-- Tab 4: Pesan WhatsApp -->
                <div class="tab-pane fade" id="wa-pane" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold text-dark mb-0">Riwayat Komunikasi WhatsApp</h6>
                        <a href="{{ route('whatsapp.index') }}" class="btn btn-sm btn-forest-green">
                            <i class="bi bi-chat-dots me-1"></i> Kirim Pesan Baru
                        </a>
                    </div>
                    @forelse($jamaah->whatsappMessages as $msg)
                        <div class="p-3 mb-2 rounded bg-light border">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="badge bg-forest-green text-white small">{{ $msg->message_type }}</span>
                                <span class="small text-muted">{{ $msg->created_at->translatedFormat('d M Y, H:i') }}</span>
                            </div>
                            <p class="small mb-1 text-dark" style="white-space: pre-line;">{{ $msg->content }}</p>
                            <span class="badge {{ $msg->status === 'sent' || $msg->status === 'simulated' ? 'badge-subtle-success' : 'badge-subtle-danger' }}" style="font-size: 0.65rem;">
                                Status: {{ ucfirst($msg->status) }}
                            </span>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted small">
                            Belum ada log pesan WhatsApp untuk jamaah ini.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Upload Dokumen -->
<div class="modal fade" id="uploadDocModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('jamaah.documents.upload', $jamaah) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h6 class="modal-title fw-bold">Unggah Dokumen Jamaah</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="doc_type" class="form-label small fw-semibold">Jenis Dokumen <span class="text-danger">*</span></label>
                        <select name="document_type" id="doc_type" class="form-select form-select-sm" required>
                            <option value="ktp">KTP Elektronik</option>
                            <option value="kk">Kartu Keluarga (KK)</option>
                            <option value="paspor">Paspor Asli</option>
                            <option value="pas_foto">Pas Foto 4x6 Background Putih</option>
                            <option value="buku_nikah">Buku Nikah / Akta Lahir</option>
                            <option value="dokumen_pendukung">Dokumen Pendukung Lainnya</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="document_name" class="form-label small fw-semibold">Keterangan / Nama File</label>
                        <input type="text" name="document_name" id="document_name" class="form-control form-control-sm" placeholder="Contoh: Paspor H. Ahmad Subardjo (Opsional)">
                    </div>

                    <div class="mb-3">
                        <label for="document_file" class="form-label small fw-semibold">Pilih Berkas <span class="text-danger">*</span></label>
                        <input type="file" name="document_file" id="document_file" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png" required>
                        <small class="text-muted" style="font-size: 0.72rem;">Format: PDF, JPG, JPEG, PNG (Maksimal 5MB)</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-forest-green">Unggah Sekarang</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
