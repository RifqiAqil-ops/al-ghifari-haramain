@extends('layouts.app')

@section('title', 'WhatsApp Business Service - Al Ghifari Haramain')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">WhatsApp Business Cloud API</h4>
        <p class="text-muted small mb-0">Integrasi pesan resmi, pengingat jatuh tempo, konfirmasi berkas, dan info keberangkatan</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('whatsapp.templates') }}" class="btn btn-outline-secondary">
            <i class="bi bi-file-earmark-text me-1"></i> Kelola Template
        </a>
        <button type="button" class="btn btn-success shadow-sm" data-bs-toggle="modal" data-bs-target="#sendMessageModal">
            <i class="bi bi-send me-1"></i> Kirim Pesan WhatsApp
        </button>
    </div>
</div>

<!-- API Status Banner -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon p-3 rounded-circle {{ $isConnected ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }}">
                    <i class="bi bi-whatsapp fs-3"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <h6 class="fw-bold mb-0 text-dark">Status Koneksi Meta Cloud API:</h6>
                        @if($isConnected)
                            <span class="badge bg-success rounded-pill px-3 py-1">TERHUBUNG (LIVE API)</span>
                        @else
                            <span class="badge bg-warning text-dark rounded-pill px-3 py-1">
                                <i class="bi bi-cone-striped me-1"></i> SIMULATION / SANDBOX MODE
                            </span>
                        @endif
                    </div>
                    <p class="text-muted small mb-0 mt-1">
                        @if($isConnected)
                            API Key resmi terkonfigurasi pada file lingkungan. Pesan akan dikirim langsung ke nomor WhatsApp jamaah.
                        @else
                            Credential <code>WHATSAPP_ACCESS_TOKEN</code> dan <code>WHATSAPP_PHONE_NUMBER_ID</code> belum diisi di <code>.env</code>. 
                            Sistem beroperasi dalam mode simulasi aman (pesan tercatat di database tanpa biaya API).
                        @endif
                    </p>
                </div>
            </div>
            <div>
                <a href="{{ route('settings.index') }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-sliders me-1"></i> Konfigurasi API
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Search & Filter Card -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
        <form action="{{ route('whatsapp.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control form-control-sm border-start-0" 
                        value="{{ $search }}" placeholder="Cari No Telepon, Nama Jamaah, atau Isi Pesan...">
                </div>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm">
                    <option value="">-- Semua Status Pengiriman --</option>
                    <option value="sent" {{ $status == 'sent' ? 'selected' : '' }}>Terkirim</option>
                    <option value="delivered" {{ $status == 'delivered' ? 'selected' : '' }}>Diterima (Delivered)</option>
                    <option value="read" {{ $status == 'read' ? 'selected' : '' }}>Dibaca (Read)</option>
                    <option value="failed" {{ $status == 'failed' ? 'selected' : '' }}>Gagal (Failed)</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary btn-sm px-3">Filter</button>
                @if($search || $status)
                    <a href="{{ route('whatsapp.index') }}" class="btn btn-light btn-sm text-secondary">Reset</a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Table Messages -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 40px;">No</th>
                        <th>Tujuan / Jamaah</th>
                        <th>Kategori & Tipe</th>
                        <th>Isi Pesan WhatsApp</th>
                        <th>Waktu Kirim</th>
                        <th class="text-center">Status</th>
                        <th>Pengirim</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($messages as $idx => $msg)
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td>
                                @if($msg->jamaah)
                                    <a href="{{ route('jamaahs.show', $msg->jamaah_id) }}" class="fw-bold text-dark text-decoration-none">
                                        {{ $msg->jamaah->name }}
                                    </a>
                                @else
                                    <strong class="text-dark">Umum</strong>
                                @endif
                                <span class="text-muted small d-block">
                                    <i class="bi bi-whatsapp text-success me-1"></i>{{ $msg->phone_number }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border text-uppercase">{{ $msg->category ?? 'Umum' }}</span>
                                <span class="text-muted small d-block">{{ strtoupper($msg->message_type) }}</span>
                            </td>
                            <td style="max-width: 320px;">
                                <div class="p-2 bg-light rounded-3 small text-dark" style="white-space: pre-line; max-height: 80px; overflow-y: auto;">
                                    {{ $msg->content }}
                                </div>
                            </td>
                            <td>
                                <span class="small text-muted">{{ $msg->created_at ? $msg->created_at->format('d/m/Y H:i') : '-' }}</span>
                            </td>
                            <td class="text-center">
                                @if($msg->status == 'sent' || $msg->status == 'delivered' || $msg->status == 'read')
                                    <span class="badge bg-success-subtle text-success">
                                        <i class="bi bi-check2-all me-1"></i> {{ strtoupper($msg->status) }}
                                    </span>
                                @elseif($msg->status == 'failed')
                                    <span class="badge bg-danger-subtle text-danger" title="{{ $msg->error_message }}">
                                        <i class="bi bi-exclamation-circle me-1"></i> FAILED
                                    </span>
                                @else
                                    <span class="badge bg-secondary">{{ strtoupper($msg->status) }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="small text-secondary">{{ $msg->sender->name ?? 'System' }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-chat-left-dots display-4 mb-3 d-block text-muted"></i>
                                <h6 class="fw-semibold">Belum ada riwayat pesan WhatsApp.</h6>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($messages->hasPages())
        <div class="card-footer bg-white border-top py-3">
            {{ $messages->links() }}
        </div>
    @endif
</div>

<!-- Modal Kirim Pesan -->
<div class="modal fade" id="sendMessageModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('whatsapp.send') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-dark"><i class="bi bi-whatsapp text-success me-1"></i> Kirim Pesan WhatsApp</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Pilih Jamaah Terdaftar (Opsional)</label>
                            <select name="jamaah_id" id="jamaahSelect" class="form-select" onchange="autoFillPhone(this)">
                                <option value="">-- Ketik Nomor Bebas / Manual --</option>
                                @foreach($jamaahs as $j)
                                    <option value="{{ $j->id }}" data-phone="{{ $j->phone }}">{{ $j->name }} ({{ $j->phone }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Nomor WhatsApp Tujuan <span class="text-danger">*</span></label>
                            <input type="text" name="phone_number" id="targetPhone" class="form-control" required placeholder="Contoh: 08123456789 atau 628123456789">
                            <div class="form-text small">Awalan 0 atau 62 akan otomatis dinormalisasi oleh sistem.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Tipe Pesan <span class="text-danger">*</span></label>
                            <select name="message_type" id="messageType" class="form-select" onchange="toggleMessageType(this.value)" required>
                                <option value="manual">Tulis Pesan Manual Bebas</option>
                                <option value="template">Gunakan Template Pesan Resmi</option>
                            </select>
                        </div>

                        <div class="col-md-6" id="templateSelectionGroup" style="display: none;">
                            <label class="form-label fw-semibold small">Pilih Template Pesan <span class="text-danger">*</span></label>
                            <select name="template_code" class="form-select">
                                <option value="">-- Pilih Template --</option>
                                @foreach($templates as $tmpl)
                                    <option value="{{ $tmpl->code }}">[{{ strtoupper($tmpl->category) }}] {{ $tmpl->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12" id="manualTextGroup">
                            <label class="form-label fw-semibold small">Isi Pesan WhatsApp <span class="text-danger">*</span></label>
                            <textarea name="content" class="form-control" rows="5" placeholder="Tuliskan pesan kepada jamaah..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success px-4">
                        <i class="bi bi-send me-1"></i> Kirim Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function autoFillPhone(select) {
    const selectedOption = select.options[select.selectedIndex];
    const phone = selectedOption.getAttribute('data-phone');
    if (phone) {
        document.getElementById('targetPhone').value = phone;
    }
}

function toggleMessageType(type) {
    if (type === 'template') {
        document.getElementById('templateSelectionGroup').style.display = 'block';
        document.getElementById('manualTextGroup').style.display = 'none';
    } else {
        document.getElementById('templateSelectionGroup').style.display = 'none';
        document.getElementById('manualTextGroup').style.display = 'block';
    }
}
</script>
@endsection
