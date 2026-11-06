@extends('layouts.app')

@section('title', 'Tambah Paket Baru')
@section('header_title', 'Buat Paket Haji & Umrah Baru')

@section('content')
<div class="card card-custom p-4">
    <div class="d-flex align-items-center justify-content-between border-bottom pb-3 mb-4">
        <div>
            <h5 class="fw-bold text-forest-green mb-1">
                <i class="bi bi-box-seam text-royal-gold me-2"></i> Tambah Paket Perjalanan Ibadah
            </h5>
            <p class="text-muted small mb-0">Rancang paket penawaran lengkap beserta rincian fasilitas dan akomodasi</p>
        </div>
        <a href="{{ route('packages.index') }}" class="btn btn-sm btn-light border">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
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

    <form action="{{ route('packages.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="row g-4">
            <!-- Left Column: Informasi Inti & Harga -->
            <div class="col-lg-6">
                <h6 class="fw-bold text-forest-green mb-3 border-bottom pb-2">
                    <i class="bi bi-info-circle text-royal-gold me-1"></i> Informasi Dasar Paket
                </h6>

                <div class="row g-3 mb-3">
                    <div class="col-md-5">
                        <label for="code" class="form-label small fw-semibold">Kode Paket <span class="text-danger">*</span></label>
                        <input type="text" name="code" id="code" class="form-control" value="{{ old('code', 'PKG-' . strtoupper(substr(uniqid(), -5))) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label for="type" class="form-label small fw-semibold">Jenis Program <span class="text-danger">*</span></label>
                        <select name="type" id="type" class="form-select" required onchange="toggleHajiYear(this.value)">
                            <option value="umrah" {{ old('type') === 'umrah' ? 'selected' : '' }}>Umrah</option>
                            <option value="haji" {{ old('type') === 'haji' ? 'selected' : '' }}>Haji</option>
                        </select>
                    </div>
                    <div class="col-md-3 {{ old('type') === 'haji' ? '' : 'd-none' }}" id="haji-year-group">
                        <label for="haji_year" class="form-label small fw-semibold">Tahun Haji</label>
                        <input type="number" name="haji_year" id="haji_year" class="form-control" value="{{ old('haji_year', date('Y')) }}">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="name" class="form-label small fw-semibold">Nama Paket Perjalanan <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" placeholder="Contoh: Umrah Reguler Awwalussanah Bintang 5" required>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="program_category" class="form-label small fw-semibold">Kategori Layanan</label>
                        <input type="text" name="program_category" id="program_category" class="form-control" value="{{ old('program_category', 'Reguler') }}" placeholder="Reguler / VIP / Plus Turki / Furoda">
                    </div>
                    <div class="col-md-3">
                        <label for="duration_days" class="form-label small fw-semibold">Durasi (Hari) <span class="text-danger">*</span></label>
                        <input type="number" name="duration_days" id="duration_days" class="form-control" value="{{ old('duration_days', 9) }}" min="1" required>
                    </div>
                    <div class="col-md-3">
                        <label for="quota" class="form-label small fw-semibold">Target Kuota <span class="text-danger">*</span></label>
                        <input type="number" name="quota" id="quota" class="form-control" value="{{ old('quota', 45) }}" min="1" required>
                    </div>
                </div>

                <h6 class="fw-bold text-forest-green mt-4 mb-3 border-bottom pb-2">
                    <i class="bi bi-cash-stack text-royal-gold me-1"></i> Konfigurasi Harga Paket (IDR)
                </h6>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="price" class="form-label small fw-semibold">Harga Dasar / Quad (Ber-4) <span class="text-danger">*</span></label>
                        <input type="number" name="price" id="price" class="form-control fw-bold text-forest-green" value="{{ old('price') }}" placeholder="29500000" required>
                    </div>
                    <div class="col-md-6">
                        <label for="triple_price" class="form-label small fw-semibold">Harga Kamar Triple (Ber-3)</label>
                        <input type="number" name="triple_price" id="triple_price" class="form-control" value="{{ old('triple_price') }}" placeholder="31500000">
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="double_price" class="form-label small fw-semibold">Harga Kamar Double (Ber-2)</label>
                        <input type="number" name="double_price" id="double_price" class="form-control" value="{{ old('double_price') }}" placeholder="34000000">
                    </div>
                    <div class="col-md-6">
                        <label for="status" class="form-label small fw-semibold">Status Paket <span class="text-danger">*</span></label>
                        <select name="status" id="status" class="form-select" required>
                            <option value="aktif">Aktif</option>
                            <option value="draft">Draf</option>
                            <option value="penuh">Penuh</option>
                        </select>
                    </div>
                </div>

                <div class="p-3 bg-light rounded-3 border mb-3">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="is_promo" id="is_promo" value="1" {{ old('is_promo') ? 'checked' : '' }} onchange="togglePromoInput(this.checked)">
                        <label class="form-check-label small fw-bold text-dark" for="is_promo">
                            Aktifkan Sebagai Program Promo Diskon
                        </label>
                    </div>
                    <div id="promo-input-group" class="{{ old('is_promo') ? '' : 'd-none' }}">
                        <label for="discount_amount" class="form-label small fw-semibold">Nominal Potongan Diskon (IDR)</label>
                        <input type="number" name="discount_amount" id="discount_amount" class="form-control" value="{{ old('discount_amount', 0) }}" placeholder="Contoh: 1500000">
                    </div>
                </div>
            </div>

            <!-- Right Column: Fasilitas, Maskapai, Hotel -->
            <div class="col-lg-6">
                <h6 class="fw-bold text-forest-green mb-3 border-bottom pb-2">
                    <i class="bi bi-airplane-engines text-royal-gold me-1"></i> Maskapai &amp; Akomodasi Hotel
                </h6>

                <div class="mb-3">
                    <label for="airline" class="form-label small fw-semibold">Maskapai Penerbangan</label>
                    <input type="text" name="airline" id="airline" class="form-control" value="{{ old('airline') }}" placeholder="Contoh: Saudia Airlines Direct Madinah">
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-8">
                        <label for="hotel_makkah" class="form-label small fw-semibold">Hotel Makkah</label>
                        <input type="text" name="hotel_makkah" id="hotel_makkah" class="form-control" value="{{ old('hotel_makkah') }}" placeholder="Contoh: Pullman Zamzam Makkah">
                    </div>
                    <div class="col-md-4">
                        <label for="hotel_makkah_rating" class="form-label small fw-semibold">Bintang Hotel</label>
                        <select name="hotel_makkah_rating" id="hotel_makkah_rating" class="form-select">
                            <option value="5" selected>⭐⭐⭐⭐⭐ (Bintang 5)</option>
                            <option value="4">⭐⭐⭐⭐ (Bintang 4)</option>
                            <option value="3">⭐⭐⭐ (Bintang 3)</option>
                        </select>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-8">
                        <label for="hotel_madinah" class="form-label small fw-semibold">Hotel Madinah</label>
                        <input type="text" name="hotel_madinah" id="hotel_madinah" class="form-control" value="{{ old('hotel_madinah') }}" placeholder="Contoh: Rove Al Madinah">
                    </div>
                    <div class="col-md-4">
                        <label for="hotel_madinah_rating" class="form-label small fw-semibold">Bintang Hotel</label>
                        <select name="hotel_madinah_rating" id="hotel_madinah_rating" class="form-select">
                            <option value="5" selected>⭐⭐⭐⭐⭐ (Bintang 5)</option>
                            <option value="4">⭐⭐⭐⭐ (Bintang 4)</option>
                            <option value="3">⭐⭐⭐ (Bintang 3)</option>
                        </select>
                    </div>
                </div>

                <h6 class="fw-bold text-forest-green mt-4 mb-3 border-bottom pb-2">
                    <i class="bi bi-check-all text-royal-gold me-1"></i> Fasilitas Paket
                </h6>

                <div id="facilities-wrapper">
                    <div class="input-group input-group-sm mb-2">
                        <input type="text" name="facilities[]" class="form-control" value="Tiket Pesawat PP Internasional">
                    </div>
                    <div class="input-group input-group-sm mb-2">
                        <input type="text" name="facilities[]" class="form-control" value="Visa Umrah & Tasreeh Raudhah Resmi">
                    </div>
                    <div class="input-group input-group-sm mb-2">
                        <input type="text" name="facilities[]" class="form-control" value="Hotel Bintang 5 Makkah & Madinah Fullboard">
                    </div>
                    <div class="input-group input-group-sm mb-2">
                        <input type="text" name="facilities[]" class="form-control" value="Bus Eksekutif AC Antar Kota Saudi">
                    </div>
                    <div class="input-group input-group-sm mb-2">
                        <input type="text" name="facilities[]" class="form-control" value="Bimbingan Ibadah Muthawwif Berpengalaman">
                    </div>
                    <div class="input-group input-group-sm mb-2">
                        <input type="text" name="facilities[]" class="form-control" value="Koper & Perlengkapan Eksklusif Al Ghifari">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label small fw-semibold">Deskripsi &amp; Rincian Tambahan</label>
                    <textarea name="description" id="description" rows="3" class="form-control">{{ old('description') }}</textarea>
                </div>
            </div>
        </div>

        <div class="border-top pt-3 d-flex justify-content-end gap-2 mt-4">
            <a href="{{ route('packages.index') }}" class="btn btn-light border px-4">Batal</a>
            <button type="submit" class="btn btn-forest-green px-4">
                <i class="bi bi-check2-circle me-1"></i> Simpan Paket Baru
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    function toggleHajiYear(val) {
        const group = document.getElementById('haji-year-group');
        if (group) {
            if (val === 'haji') group.classList.remove('d-none');
            else group.classList.add('d-none');
        }
    }

    function togglePromoInput(checked) {
        const group = document.getElementById('promo-input-group');
        if (group) {
            if (checked) group.classList.remove('d-none');
            else group.classList.add('d-none');
        }
    }
</script>
@endpush
