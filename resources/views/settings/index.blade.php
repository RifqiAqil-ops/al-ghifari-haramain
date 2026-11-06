@extends('layouts.app')

@section('title', 'Pengaturan Aplikasi & Perusahaan - Al Ghifari Haramain')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Pengaturan Sistem & Profil Perusahaan</h4>
        <p class="text-muted small mb-0">Kelola identitas resmi Al Ghifari Haramain, format penomoran invoice, dan rekening bank</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('audit-logs.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-clock-history me-1"></i> Audit Log
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Kolom Kiri: Profil Perusahaan & Format Dokumen -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-building text-primary me-2"></i> Identitas & Profil Perusahaan</h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('settings.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Nama Resmi Perusahaan <span class="text-danger">*</span></label>
                            <input type="text" name="company_name" class="form-control" value="{{ old('company_name', $setting->company_name) }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Tagline Brand Resmi <span class="text-danger">*</span></label>
                            <input type="text" name="brand_tagline" class="form-control" value="{{ old('brand_tagline', $setting->brand_tagline) }}" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Email Resmi</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $setting->email) }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Nomor Telepon Kantor</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $setting->phone) }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Nomor WhatsApp CS</label>
                            <input type="text" name="whatsapp" class="form-control" value="{{ old('whatsapp', $setting->whatsapp) }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold small">Alamat Kantor Pusat</label>
                            <textarea name="address" class="form-control" rows="2">{{ old('address', $setting->address) }}</textarea>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-semibold small">Situs Web Resmi</label>
                            <input type="text" name="website" class="form-control" value="{{ old('website', $setting->website) }}">
                        </div>

                        <div class="col-12"><hr class="my-2"></div>
                        <div class="col-12">
                            <h6 class="fw-bold text-dark mb-1"><i class="bi bi-file-earmark-text text-success me-1"></i> Format Penomoran Dokumen Otomatis</h6>
                            <span class="text-muted small">Prefix kode awalan dokumen sistem (Contoh: JAM-202610-0001)</span>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Prefix No Registrasi <span class="text-danger">*</span></label>
                            <input type="text" name="registration_prefix" class="form-control text-uppercase" value="{{ old('registration_prefix', $setting->registration_prefix) }}" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Prefix No Invoice <span class="text-danger">*</span></label>
                            <input type="text" name="invoice_prefix" class="form-control text-uppercase" value="{{ old('invoice_prefix', $setting->invoice_prefix) }}" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Prefix No Kwitansi <span class="text-danger">*</span></label>
                            <input type="text" name="receipt_prefix" class="form-control text-uppercase" value="{{ old('receipt_prefix', $setting->receipt_prefix) }}" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold small">Catatan Kaki Kwitansi (Footer Note)</label>
                            <textarea name="receipt_footer_note" class="form-control" rows="2">{{ old('receipt_footer_note', $setting->receipt_footer_note) }}</textarea>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4 pt-3 border-top">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-save me-1"></i> Simpan Pengaturan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Logo Resmi & Rekening Bank -->
    <div class="col-lg-4">
        <!-- Logo Card -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-shield-lock text-primary me-1"></i> Logo Brand Resmi</h6>
            </div>
            <div class="card-body p-4 text-center">
                <div class="p-3 bg-light rounded-3 mb-3 d-inline-block border">
                    <img src="{{ asset('assets/brand/logo.png') }}" alt="Logo Al Ghifari" style="max-height: 80px; max-width: 100%; object-fit: contain;">
                </div>
                <h6 class="fw-bold text-dark mb-1">Al Ghifari Haramain</h6>
                <p class="text-muted small mb-0">UMROH & HAJI SPECIALIST</p>
                <div class="alert alert-secondary py-1 px-2 mt-3 small text-muted">
                    File: <code>public/assets/brand/logo.png</code> (Logo resmi terproteksi)
                </div>
            </div>
        </div>

        <!-- Rekening Bank Card -->
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-bank text-success me-1"></i> Rekening Resmi</h6>
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#newBankModal">
                    <i class="bi bi-plus-lg me-1"></i> Tambah
                </button>
            </div>
            <div class="card-body p-3">
                @forelse($bankAccounts as $b)
                    <div class="p-3 border rounded-3 mb-2 bg-light">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong class="text-dark d-block">{{ $b->bank_name }}</strong>
                                <span class="fw-mono fw-bold fs-6 text-primary d-block">{{ $b->account_number }}</span>
                                <span class="text-muted small d-block">a.n {{ $b->account_name }}</span>
                                @if($b->branch)
                                    <span class="text-muted small">Cabang: {{ $b->branch }}</span>
                                @endif
                            </div>
                            <div class="d-flex gap-1">
                                <button type="button" class="btn btn-sm btn-light border text-primary" data-bs-toggle="modal" data-bs-target="#editBankModal{{ $b->id }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form action="{{ route('settings.banks.delete', $b) }}" method="POST" onsubmit="return confirm('Hapus rekening {{ $b->bank_name }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-light border text-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Edit Rekening -->
                    <div class="modal fade text-start" id="editBankModal{{ $b->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form action="{{ route('settings.banks.update', $b) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-header">
                                        <h5 class="modal-title fw-bold">Edit Rekening Bank</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold">Nama Bank</label>
                                            <input type="text" name="bank_name" class="form-control" value="{{ $b->bank_name }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold">Nomor Rekening</label>
                                            <input type="text" name="account_number" class="form-control" value="{{ $b->account_number }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold">Atas Nama Rekening</label>
                                            <input type="text" name="account_name" class="form-control" value="{{ $b->account_name }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold">Kantor Cabang</label>
                                            <input type="text" name="branch" class="form-control" value="{{ $b->branch }}">
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="bkAct{{ $b->id }}" {{ $b->is_active ? 'checked' : '' }}>
                                            <label class="form-check-label small" for="bkAct{{ $b->id }}">Tampilkan di Invoice</label>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-primary">Simpan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-4 text-muted small">Belum ada rekening bank yang terdaftar.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Rekening -->
<div class="modal fade" id="newBankModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('settings.banks.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Tambah Rekening Bank Resmi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Bank <span class="text-danger">*</span></label>
                        <input type="text" name="bank_name" class="form-control" required placeholder="Contoh: Bank Syariah Indonesia (BSI)">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nomor Rekening <span class="text-danger">*</span></label>
                        <input type="text" name="account_number" class="form-control" required placeholder="Contoh: 7123456789">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Atas Nama Rekening <span class="text-danger">*</span></label>
                        <input type="text" name="account_name" class="form-control" required placeholder="Contoh: PT Al Ghifari Haramain Tour">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Kantor Cabang (Opsional)</label>
                        <input type="text" name="branch" class="form-control" placeholder="Contoh: KC Jakarta Rawamangun">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Tambah Rekening</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
