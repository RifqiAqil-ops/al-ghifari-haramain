@extends('layouts.app')

@section('title', 'Edit Paket - ' . $package->name)
@section('header_title', 'Perbarui Paket Perjalanan')

@section('content')
<div class="card card-custom p-4">
    <div class="d-flex align-items-center justify-content-between border-bottom pb-3 mb-4">
        <div>
            <h5 class="fw-bold text-forest-green mb-1">
                <i class="bi bi-pencil-square text-royal-gold me-2"></i> Edit Paket: {{ $package->name }}
            </h5>
            <div class="text-muted small">Kode Paket: <span class="font-monospace text-forest-green fw-bold">{{ $package->code }}</span></div>
        </div>
        <a href="{{ route('packages.show', $package) }}" class="btn btn-sm btn-light border">
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

    <form action="{{ route('packages.update', $package) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <!-- Left Column: Informasi Inti & Harga -->
            <div class="col-lg-6">
                <h6 class="fw-bold text-forest-green mb-3 border-bottom pb-2">
                    <i class="bi bi-info-circle text-royal-gold me-1"></i> Informasi Dasar Paket
                </h6>

                <div class="row g-3 mb-3">
                    <div class="col-md-5">
                        <label for="code" class="form-label small fw-semibold">Kode Paket <span class="text-danger">*</span></label>
                        <input type="text" name="code" id="code" class="form-control" value="{{ old('code', $package->code) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label for="type" class="form-label small fw-semibold">Jenis Program <span class="text-danger">*</span></label>
                        <select name="type" id="type" class="form-select" required onchange="toggleHajiYear(this.value)">
                            <option value="umrah" {{ old('type', $package->type) === 'umrah' ? 'selected' : '' }}>Umrah</option>
                            <option value="haji" {{ old('type', $package->type) === 'haji' ? 'selected' : '' }}>Haji</option>
                        </select>
                    </div>
                    <div class="col-md-3 {{ old('type', $package->type) === 'haji' ? '' : 'd-none' }}" id="haji-year-group">
                        <label for="haji_year" class="form-label small fw-semibold">Tahun Haji</label>
                        <input type="number" name="haji_year" id="haji_year" class="form-control" value="{{ old('haji_year', $package->haji_year ?? date('Y')) }}">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="name" class="form-label small fw-semibold">Nama Paket Perjalanan <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $package->name) }}" required>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="program_category" class="form-label small fw-semibold">Kategori Layanan</label>
                        <input type="text" name="program_category" id="program_category" class="form-control" value="{{ old('program_category', $package->program_category) }}">
                    </div>
                    <div class="col-md-3">
                        <label for="duration_days" class="form-label small fw-semibold">Durasi (Hari) <span class="text-danger">*</span></label>
                        <input type="number" name="duration_days" id="duration_days" class="form-control" value="{{ old('duration_days', $package->duration_days) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label for="quota" class="form-label small fw-semibold">Target Kuota <span class="text-danger">*</span></label>
                        <input type="number" name="quota" id="quota" class="form-control" value="{{ old('quota', $package->quota) }}" required>
                    </div>
                </div>

                <h6 class="fw-bold text-forest-green mt-4 mb-3 border-bottom pb-2">
                    <i class="bi bi-cash-stack text-royal-gold me-1"></i> Konfigurasi Harga Paket (IDR)
                </h6>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="price" class="form-label small fw-semibold">Harga Dasar / Quad (Ber-4) <span class="text-danger">*</span></label>
                        <input type="number" name="price" id="price" class="form-control fw-bold text-forest-green" value="{{ old('price', (int)$package->price) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label for="triple_price" class="form-label small fw-semibold">Harga Kamar Triple (Ber-3)</label>
                        <input type="number" name="triple_price" id="triple_price" class="form-control" value="{{ old('triple_price', (int)$package->triple_price) }}">
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="double_price" class="form-label small fw-semibold">Harga Kamar Double (Ber-2)</label>
                        <input type="number" name="double_price" id="double_price" class="form-control" value="{{ old('double_price', (int)$package->double_price) }}">
                    </div>
                    <div class="col-md-6">
                        <label for="status" class="form-label small fw-semibold">Status Paket <span class="text-danger">*</span></label>
                        <select name="status" id="status" class="form-select" required>
                            <option value="aktif" {{ old('status', $package->status) === 'aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="draft" {{ old('status', $package->status) === 'draft' ? 'selected' : '' }}>Draf</option>
                            <option value="penuh" {{ old('status', $package->status) === 'penuh' ? 'selected' : '' }}>Penuh</option>
                            <option value="selesai" {{ old('status', $package->status) === 'selesai' ? 'selected' : '' }}>Selesai</option>
                        </select>
                    </div>
                </div>

                <div class="p-3 bg-light rounded-3 border mb-3">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="is_promo" id="is_promo" value="1" {{ old('is_promo', $package->is_promo) ? 'checked' : '' }} onchange="togglePromoInput(this.checked)">
                        <label class="form-check-label small fw-bold text-dark" for="is_promo">
                            Aktifkan Sebagai Program Promo Diskon
                        </label>
                    </div>
                    <div id="promo-input-group" class="{{ old('is_promo', $package->is_promo) ? '' : 'd-none' }}">
                        <label for="discount_amount" class="form-label small fw-semibold">Nominal Potongan Diskon (IDR)</label>
                        <input type="number" name="discount_amount" id="discount_amount" class="form-control" value="{{ old('discount_amount', (int)$package->discount_amount) }}">
                    </div>
                </div>
            </div>

            <!-- Right Column: Maskapai & Fasilitas -->
            <div class="col-lg-6">
                <h6 class="fw-bold text-forest-green mb-3 border-bottom pb-2">
                    <i class="bi bi-airplane-engines text-royal-gold me-1"></i> Maskapai &amp; Akomodasi Hotel
                </h6>

                <div class="mb-3">
                    <label for="airline" class="form-label small fw-semibold">Maskapai Penerbangan</label>
                    <input type="text" name="airline" id="airline" class="form-control" value="{{ old('airline', $package->airline) }}">
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-8">
                        <label for="hotel_makkah" class="form-label small fw-semibold">Hotel Makkah</label>
                        <input type="text" name="hotel_makkah" id="hotel_makkah" class="form-control" value="{{ old('hotel_makkah', $package->hotel_makkah) }}">
                    </div>
                    <div class="col-md-4">
                        <label for="hotel_makkah_rating" class="form-label small fw-semibold">Bintang Hotel</label>
                        <select name="hotel_makkah_rating" id="hotel_makkah_rating" class="form-select">
                            <option value="5" {{ old('hotel_makkah_rating', $package->hotel_makkah_rating) == 5 ? 'selected' : '' }}>⭐⭐⭐⭐⭐ (Bintang 5)</option>
                            <option value="4" {{ old('hotel_makkah_rating', $package->hotel_makkah_rating) == 4 ? 'selected' : '' }}>⭐⭐⭐⭐ (Bintang 4)</option>
                            <option value="3" {{ old('hotel_makkah_rating', $package->hotel_makkah_rating) == 3 ? 'selected' : '' }}>⭐⭐⭐ (Bintang 3)</option>
                        </select>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-8">
                        <label for="hotel_madinah" class="form-label small fw-semibold">Hotel Madinah</label>
                        <input type="text" name="hotel_madinah" id="hotel_madinah" class="form-control" value="{{ old('hotel_madinah', $package->hotel_madinah) }}">
                    </div>
                    <div class="col-md-4">
                        <label for="hotel_madinah_rating" class="form-label small fw-semibold">Bintang Hotel</label>
                        <select name="hotel_madinah_rating" id="hotel_madinah_rating" class="form-select">
                            <option value="5" {{ old('hotel_madinah_rating', $package->hotel_madinah_rating) == 5 ? 'selected' : '' }}>⭐⭐⭐⭐⭐ (Bintang 5)</option>
                            <option value="4" {{ old('hotel_madinah_rating', $package->hotel_madinah_rating) == 4 ? 'selected' : '' }}>⭐⭐⭐⭐ (Bintang 4)</option>
                            <option value="3" {{ old('hotel_madinah_rating', $package->hotel_madinah_rating) == 3 ? 'selected' : '' }}>⭐⭐⭐ (Bintang 3)</option>
                        </select>
                    </div>
                </div>

                <h6 class="fw-bold text-forest-green mt-4 mb-3 border-bottom pb-2">
                    <i class="bi bi-check-all text-royal-gold me-1"></i> Fasilitas Paket
                </h6>

                <div id="facilities-wrapper">
                    @forelse($package->facilities as $fac)
                        <div class="input-group input-group-sm mb-2">
                            <input type="text" name="facilities[]" class="form-control" value="{{ $fac->name }}">
                        </div>
                    @empty
                        <div class="input-group input-group-sm mb-2">
                            <input type="text" name="facilities[]" class="form-control" value="Tiket Pesawat PP Internasional">
                        </div>
                    @endforelse
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label small fw-semibold">Deskripsi &amp; Rincian Tambahan</label>
                    <textarea name="description" id="description" rows="3" class="form-control">{{ old('description', $package->description) }}</textarea>
                </div>
            </div>
        </div>

        <div class="border-top pt-3 d-flex justify-content-end gap-2 mt-4">
            <a href="{{ route('packages.show', $package) }}" class="btn btn-light border px-4">Batal</a>
            <button type="submit" class="btn btn-forest-green px-4">
                <i class="bi bi-check2-circle me-1"></i> Simpan Perubahan Paket
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
