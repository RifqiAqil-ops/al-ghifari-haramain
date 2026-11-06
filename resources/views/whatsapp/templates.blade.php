@extends('layouts.app')

@section('title', 'Template Pesan WhatsApp - Al Ghifari Haramain')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <a href="{{ route('whatsapp.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Riwayat Pesan
        </a>
        <h4 class="fw-bold mb-1 text-dark">Template Pesan WhatsApp</h4>
        <p class="text-muted small mb-0">Standardisasi template notifikasi otomatis dan manual kepada jamaah</p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#newTemplateModal">
            <i class="bi bi-plus-lg me-1"></i> Buat Template Baru
        </button>
    </div>
</div>

<!-- Helper Parameter Guide -->
<div class="card border-0 shadow-sm rounded-3 mb-4 bg-light">
    <div class="card-body p-3">
        <div class="d-flex align-items-center gap-2 mb-2">
            <i class="bi bi-info-circle text-primary"></i>
            <h6 class="fw-bold mb-0 text-dark">Daftar Variabel Placeholder yang Didukung:</h6>
        </div>
        <div class="d-flex flex-wrap gap-2 small">
            <code class="bg-white p-1 rounded border">{nama}</code>
            <code class="bg-white p-1 rounded border">{no_registrasi}</code>
            <code class="bg-white p-1 rounded border">{sisa_tagihan}</code>
            <code class="bg-white p-1 rounded border">{daftar_dokumen}</code>
            <code class="bg-white p-1 rounded border">{batas_dokumen}</code>
            <code class="bg-white p-1 rounded border">{nama_paket}</code>
            <code class="bg-white p-1 rounded border">{tanggal_berangkat}</code>
            <code class="bg-white p-1 rounded border">{maskapai_flight}</code>
            <code class="bg-white p-1 rounded border">{tour_leader}</code>
        </div>
    </div>
</div>

<!-- Table Card -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 40px;">No</th>
                        <th>Nama Template</th>
                        <th>Kode Template</th>
                        <th>Kategori</th>
                        <th>Format Pesan</th>
                        <th class="text-center">Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($templates as $idx => $tmpl)
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td><strong class="text-dark">{{ $tmpl->name }}</strong></td>
                            <td><code class="text-primary">{{ $tmpl->code }}</code></td>
                            <td><span class="badge bg-light text-dark border text-uppercase">{{ $tmpl->category }}</span></td>
                            <td style="max-width: 380px;">
                                <div class="small p-2 bg-light rounded-3 text-secondary" style="white-space: pre-line; max-height: 80px; overflow-y: auto;">
                                    {{ $tmpl->body }}
                                </div>
                            </td>
                            <td class="text-center">
                                @if($tmpl->is_active)
                                    <span class="badge bg-success-subtle text-success">Aktif</span>
                                @else
                                    <span class="badge bg-secondary">Nonaktif</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal{{ $tmpl->id }}">
                                    <i class="bi bi-pencil me-1"></i> Edit
                                </button>

                                <!-- Modal Edit Template -->
                                <div class="modal fade text-start" id="editModal{{ $tmpl->id }}" tabindex="-1">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <form action="{{ route('whatsapp.templates.update', $tmpl) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold">Edit Template Pesan</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body p-4">
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-semibold small">Nama Template</label>
                                                            <input type="text" name="name" class="form-control" value="{{ $tmpl->name }}" required>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-semibold small">Kategori</label>
                                                            <select name="category" class="form-select" required>
                                                                <option value="pembayaran" {{ $tmpl->category == 'pembayaran' ? 'selected' : '' }}>Pembayaran</option>
                                                                <option value="dokumen" {{ $tmpl->category == 'dokumen' ? 'selected' : '' }}>Dokumen & Visa</option>
                                                                <option value="keberangkatan" {{ $tmpl->category == 'keberangkatan' ? 'selected' : '' }}>Keberangkatan & Manasik</option>
                                                                <option value="umum" {{ $tmpl->category == 'umum' ? 'selected' : '' }}>Umum</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-12">
                                                            <label class="form-label fw-semibold small">Isi Teks Template</label>
                                                            <textarea name="body" class="form-control" rows="6" required>{{ $tmpl->body }}</textarea>
                                                        </div>
                                                        <div class="col-12">
                                                            <div class="form-check form-switch">
                                                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="act{{ $tmpl->id }}" {{ $tmpl->is_active ? 'checked' : '' }}>
                                                                <label class="form-check-label small" for="act{{ $tmpl->id }}">Aktifkan Template Ini</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">Belum ada template pesan yang dibuat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($templates->hasPages())
        <div class="card-footer bg-white border-top py-3">
            {{ $templates->links() }}
        </div>
    @endif
</div>

<!-- Modal Buat Template Baru -->
<div class="modal fade" id="newTemplateModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('whatsapp.templates.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-dark"><i class="bi bi-file-earmark-plus me-1 text-primary"></i> Buat Template Pesan Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Nama Template <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required placeholder="Contoh: Pengingat Pelunasan H-14">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Kode Unik Template <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control" required placeholder="Contoh: reminder_pelunasan_h14">
                            <div class="form-text small">Hanya huruf kecil, angka, dan underscore (_).</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Kategori <span class="text-danger">*</span></label>
                            <select name="category" class="form-select" required>
                                <option value="pembayaran">Pembayaran</option>
                                <option value="dokumen">Dokumen & Visa</option>
                                <option value="keberangkatan">Keberangkatan & Manasik</option>
                                <option value="umum">Umum</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Isi Teks Template <span class="text-danger">*</span></label>
                            <textarea name="body" class="form-control" rows="6" required placeholder="Gunakan placeholder seperti {nama}, {sisa_tagihan}, dsb."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4">Simpan Template</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
