@extends('layouts.app')

@section('title', 'Tambah Jamaah Baru')
@section('header_title', 'Formulir Pendaftaran Jamaah Baru')

@section('content')
<div class="card card-custom p-4">
    <div class="d-flex align-items-center justify-content-between border-bottom pb-3 mb-4">
        <div>
            <h5 class="fw-bold text-forest-green mb-1">
                <i class="bi bi-person-plus-fill text-royal-gold me-2"></i> Input Data Jamaah
            </h5>
            <p class="text-muted small mb-0">Nomor registrasi unik sistem akan di-generate secara otomatis</p>
        </div>
        <a href="{{ route('jamaah.index') }}" class="btn btn-sm btn-light border">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger py-2 small mb-4">
            <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Mohon lengkapi data berikut:</div>
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('jamaah.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="row g-4">
            <!-- Left Column: Data Pribadi & Identitas -->
            <div class="col-lg-6">
                <h6 class="fw-bold text-forest-green mb-3 border-bottom pb-2">
                    <i class="bi bi-person-vcard text-royal-gold me-1"></i> Data Identitas Kependudukan
                </h6>

                <div class="mb-3">
                    <label for="name" class="form-label small fw-semibold">Nama Lengkap Sesuai KTP / Paspor <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Contoh: H. Ahmad Subardjo" required>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="nik" class="form-label small fw-semibold">Nomor Induk Kependudukan (NIK) <span class="text-danger">*</span></label>
                        <input type="text" name="nik" id="nik" maxlength="16" class="form-control @error('nik') is-invalid @enderror" value="{{ old('nik') }}" placeholder="16 Digit NIK" required>
                    </div>
                    <div class="col-md-6">
                        <label for="family_card_number" class="form-label small fw-semibold">Nomor Kartu Keluarga (No. KK)</label>
                        <input type="text" name="family_card_number" id="family_card_number" maxlength="16" class="form-control" value="{{ old('family_card_number') }}" placeholder="16 Digit No. KK">
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="gender" class="form-label small fw-semibold">Jenis Kelamin <span class="text-danger">*</span></label>
                        <select name="gender" id="gender" class="form-select @error('gender') is-invalid @enderror" required>
                            <option value="">Pilih Jenis Kelamin</option>
                            <option value="L" {{ old('gender') === 'L' ? 'selected' : '' }}>Laki-Laki</option>
                            <option value="P" {{ old('gender') === 'P' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="birth_date" class="form-label small fw-semibold">Tanggal Lahir</label>
                        <input type="date" name="birth_date" id="birth_date" class="form-control" value="{{ old('birth_date') }}">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="birth_place" class="form-label small fw-semibold">Tempat Lahir</label>
                    <input type="text" name="birth_place" id="birth_place" class="form-control" value="{{ old('birth_place') }}" placeholder="Contoh: Jakarta">
                </div>

                <div class="mb-3">
                    <label for="address" class="form-label small fw-semibold">Alamat Lengkap Domisili</label>
                    <textarea name="address" id="address" rows="3" class="form-control" placeholder="Nama jalan, RT/RW, kelurahan, kecamatan">{{ old('address') }}</textarea>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-5">
                        <label for="city" class="form-label small fw-semibold">Kota / Kabupaten</label>
                        <input type="text" name="city" id="city" class="form-control" value="{{ old('city') }}" placeholder="Contoh: Jakarta Selatan">
                    </div>
                    <div class="col-md-4">
                        <label for="province" class="form-label small fw-semibold">Provinsi</label>
                        <input type="text" name="province" id="province" class="form-control" value="{{ old('province') }}" placeholder="DKI Jakarta">
                    </div>
                    <div class="col-md-3">
                        <label for="postal_code" class="form-label small fw-semibold">Kode Pos</label>
                        <input type="text" name="postal_code" id="postal_code" class="form-control" value="{{ old('postal_code') }}" placeholder="12810">
                    </div>
                </div>
            </div>

            <!-- Right Column: Paspor, Kontak & Dokumen -->
            <div class="col-lg-6">
                <h6 class="fw-bold text-forest-green mb-3 border-bottom pb-2">
                    <i class="bi bi-passport text-royal-gold me-1"></i> Data Paspor &amp; Komunikasi
                </h6>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="passport_number" class="form-label small fw-semibold">Nomor Paspor</label>
                        <input type="text" name="passport_number" id="passport_number" class="form-control" value="{{ old('passport_number') }}" placeholder="Contoh: X1234567">
                    </div>
                    <div class="col-md-6">
                        <label for="passport_expiry_date" class="form-label small fw-semibold">Masa Berlaku Paspor</label>
                        <input type="date" name="passport_expiry_date" id="passport_expiry_date" class="form-control" value="{{ old('passport_expiry_date') }}">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="passport_issued_place" class="form-label small fw-semibold">Kantor Imigrasi Penerbit Paspor</label>
                    <input type="text" name="passport_issued_place" id="passport_issued_place" class="form-control" value="{{ old('passport_issued_place') }}" placeholder="Contoh: Jakarta Selatan">
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="phone" class="form-label small fw-semibold">Nomor WhatsApp Aktif <span class="text-danger">*</span></label>
                        <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}" placeholder="Contoh: 081288990011" required>
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label small fw-semibold">Alamat Email</label>
                        <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" placeholder="jamaah@gmail.com">
                    </div>
                </div>

                <h6 class="fw-bold text-forest-green mt-4 mb-3 border-bottom pb-2">
                    <i class="bi bi-telephone-inbound text-royal-gold me-1"></i> Kontak Darurat Keluarga
                </h6>

                <div class="row g-3 mb-3">
                    <div class="col-md-5">
                        <label for="emergency_contact_name" class="form-label small fw-semibold">Nama Kontak Darurat</label>
                        <input type="text" name="emergency_contact_name" id="emergency_contact_name" class="form-control" value="{{ old('emergency_contact_name') }}" placeholder="Nama keluarga">
                    </div>
                    <div class="col-md-3">
                        <label for="emergency_contact_relation" class="form-label small fw-semibold">Hubungan</label>
                        <select name="emergency_contact_relation" id="emergency_contact_relation" class="form-select">
                            <option value="suami">Suami</option>
                            <option value="istri">Istri</option>
                            <option value="anak">Anak</option>
                            <option value="orang_tua">Orang Tua</option>
                            <option value="saudara">Saudara</option>
                            <option value="kerabat">Kerabat</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="emergency_contact_phone" class="form-label small fw-semibold">No. HP Darurat</label>
                        <input type="text" name="emergency_contact_phone" id="emergency_contact_phone" class="form-control" value="{{ old('emergency_contact_phone') }}" placeholder="0812...">
                    </div>
                </div>

                <h6 class="fw-bold text-forest-green mt-4 mb-3 border-bottom pb-2">
                    <i class="bi bi-file-earmark-medical text-royal-gold me-1"></i> Catatan Khusus &amp; Pas Foto
                </h6>

                <div class="mb-3">
                    <label for="special_needs" class="form-label small fw-semibold">Kebutuhan Khusus / Riwayat Medis Perjalanan</label>
                    <textarea name="special_needs" id="special_needs" rows="2" class="form-control" placeholder="Contoh: Kursi roda saat thawaf, pantangan makanan, alergi obat">{{ old('special_needs') }}</textarea>
                </div>

                <div class="mb-4">
                    <label for="photo" class="form-label small fw-semibold">Pas Foto Jamaah (Latar Belakang Putih)</label>
                    <input type="file" name="photo" id="photo" class="form-control" accept="image/*">
                    <small class="text-muted">Format: JPG, JPEG, PNG (Maksimal 2MB)</small>
                </div>
            </div>
        </div>

        <div class="border-top pt-3 d-flex justify-content-end gap-2">
            <a href="{{ route('jamaah.index') }}" class="btn btn-light border px-4">Batal</a>
            <button type="submit" class="btn btn-forest-green px-4">
                <i class="bi bi-check2-circle me-1"></i> Simpan Data Jamaah
            </button>
        </div>
    </form>
</div>
@endsection
