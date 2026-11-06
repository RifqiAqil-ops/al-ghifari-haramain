@extends('layouts.app')

@section('title', 'Edit Data Jamaah - ' . $jamaah->name)
@section('header_title', 'Perbarui Data Jamaah')

@section('content')
<div class="card card-custom p-4">
    <div class="d-flex align-items-center justify-content-between border-bottom pb-3 mb-4">
        <div>
            <h5 class="fw-bold text-forest-green mb-1">
                <i class="bi bi-pencil-square text-royal-gold me-2"></i> Edit Data Jamaah: {{ $jamaah->name }}
            </h5>
            <div class="text-muted small">No. Registrasi: <span class="font-monospace text-forest-green fw-bold">{{ $jamaah->registration_number }}</span></div>
        </div>
        <a href="{{ route('jamaah.show', $jamaah) }}" class="btn btn-sm btn-light border">
            <i class="bi bi-arrow-left me-1"></i> Batal &amp; Kembali
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger py-2 small mb-4">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('jamaah.update', $jamaah) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <!-- Left Column: Identitas -->
            <div class="col-lg-6">
                <h6 class="fw-bold text-forest-green mb-3 border-bottom pb-2">
                    <i class="bi bi-person-vcard text-royal-gold me-1"></i> Data Identitas Kependudukan
                </h6>

                <div class="mb-3">
                    <label for="name" class="form-label small fw-semibold">Nama Lengkap Sesuai KTP / Paspor <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $jamaah->name) }}" required>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="nik" class="form-label small fw-semibold">NIK (16 Digit) <span class="text-danger">*</span></label>
                        <input type="text" name="nik" id="nik" maxlength="16" class="form-control" value="{{ old('nik', $jamaah->nik) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label for="family_card_number" class="form-label small fw-semibold">Nomor Kartu Keluarga (KK)</label>
                        <input type="text" name="family_card_number" id="family_card_number" maxlength="16" class="form-control" value="{{ old('family_card_number', $jamaah->family_card_number) }}">
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="gender" class="form-label small fw-semibold">Jenis Kelamin <span class="text-danger">*</span></label>
                        <select name="gender" id="gender" class="form-select" required>
                            <option value="L" {{ old('gender', $jamaah->gender) === 'L' ? 'selected' : '' }}>Laki-Laki</option>
                            <option value="P" {{ old('gender', $jamaah->gender) === 'P' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="birth_date" class="form-label small fw-semibold">Tanggal Lahir</label>
                        <input type="date" name="birth_date" id="birth_date" class="form-control" value="{{ old('birth_date', $jamaah->birth_date?->format('Y-m-d')) }}">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="birth_place" class="form-label small fw-semibold">Tempat Lahir</label>
                    <input type="text" name="birth_place" id="birth_place" class="form-control" value="{{ old('birth_place', $jamaah->birth_place) }}">
                </div>

                <div class="mb-3">
                    <label for="address" class="form-label small fw-semibold">Alamat Domisili</label>
                    <textarea name="address" id="address" rows="3" class="form-control">{{ old('address', $jamaah->address) }}</textarea>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-5">
                        <label for="city" class="form-label small fw-semibold">Kota / Kabupaten</label>
                        <input type="text" name="city" id="city" class="form-control" value="{{ old('city', $jamaah->city) }}">
                    </div>
                    <div class="col-md-4">
                        <label for="province" class="form-label small fw-semibold">Provinsi</label>
                        <input type="text" name="province" id="province" class="form-control" value="{{ old('province', $jamaah->province) }}">
                    </div>
                    <div class="col-md-3">
                        <label for="postal_code" class="form-label small fw-semibold">Kode Pos</label>
                        <input type="text" name="postal_code" id="postal_code" class="form-control" value="{{ old('postal_code', $jamaah->postal_code) }}">
                    </div>
                </div>
            </div>

            <!-- Right Column: Paspor, Kontak & Status -->
            <div class="col-lg-6">
                <h6 class="fw-bold text-forest-green mb-3 border-bottom pb-2">
                    <i class="bi bi-passport text-royal-gold me-1"></i> Data Paspor &amp; Kontak
                </h6>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="passport_number" class="form-label small fw-semibold">Nomor Paspor</label>
                        <input type="text" name="passport_number" id="passport_number" class="form-control" value="{{ old('passport_number', $jamaah->passport_number) }}">
                    </div>
                    <div class="col-md-6">
                        <label for="passport_expiry_date" class="form-label small fw-semibold">Masa Berlaku Paspor</label>
                        <input type="date" name="passport_expiry_date" id="passport_expiry_date" class="form-control" value="{{ old('passport_expiry_date', $jamaah->passport_expiry_date?->format('Y-m-d')) }}">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="passport_issued_place" class="form-label small fw-semibold">Kantor Imigrasi Penerbit</label>
                    <input type="text" name="passport_issued_place" id="passport_issued_place" class="form-control" value="{{ old('passport_issued_place', $jamaah->passport_issued_place) }}">
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="phone" class="form-label small fw-semibold">Nomor WhatsApp <span class="text-danger">*</span></label>
                        <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone', $jamaah->phone) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label small fw-semibold">Email</label>
                        <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $jamaah->email) }}">
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="emergency_contact_name" class="form-label small fw-semibold">Kontak Darurat</label>
                        <input type="text" name="emergency_contact_name" id="emergency_contact_name" class="form-control" value="{{ old('emergency_contact_name', $jamaah->emergency_contact_name) }}">
                    </div>
                    <div class="col-md-6">
                        <label for="emergency_contact_phone" class="form-label small fw-semibold">No. HP Darurat</label>
                        <input type="text" name="emergency_contact_phone" id="emergency_contact_phone" class="form-control" value="{{ old('emergency_contact_phone', $jamaah->emergency_contact_phone) }}">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="status" class="form-label small fw-semibold">Status Keanggotaan <span class="text-danger">*</span></label>
                    <select name="status" id="status" class="form-select" required>
                        <option value="aktif" {{ old('status', $jamaah->status) === 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="nonaktif" {{ old('status', $jamaah->status) === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                        <option value="arsip" {{ old('status', $jamaah->status) === 'arsip' ? 'selected' : '' }}>Arsip</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="special_needs" class="form-label small fw-semibold">Kebutuhan Khusus / Medis Perjalanan</label>
                    <textarea name="special_needs" id="special_needs" rows="2" class="form-control">{{ old('special_needs', $jamaah->special_needs) }}</textarea>
                </div>

                <div class="mb-4">
                    <label for="photo" class="form-label small fw-semibold">Perbarui Pas Foto (Opsional)</label>
                    <input type="file" name="photo" id="photo" class="form-control" accept="image/*">
                </div>
            </div>
        </div>

        <div class="border-top pt-3 d-flex justify-content-end gap-2">
            <a href="{{ route('jamaah.show', $jamaah) }}" class="btn btn-light border px-4">Batal</a>
            <button type="submit" class="btn btn-forest-green px-4">
                <i class="bi bi-check2-circle me-1"></i> Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection
