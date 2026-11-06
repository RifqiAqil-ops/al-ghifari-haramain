@extends('layouts.app')

@section('title', 'Pendaftaran Paket Haji & Umrah')
@section('header_title', 'Registrasi Paket Jamaah')

@section('content')
<div class="card card-custom p-4" style="max-width: 900px; margin: 0 auto;">
    <div class="d-flex align-items-center justify-content-between border-bottom pb-3 mb-4">
        <div>
            <h5 class="fw-bold text-forest-green mb-1">
                <i class="bi bi-box-arrow-in-right text-royal-gold me-2"></i> Formulir Registrasi Paket Perjalanan
            </h5>
            <p class="text-muted small mb-0">Menghubungkan jamaah dengan paket, jadwal keberangkatan, dan otomatis membuat tagihan invoice</p>
        </div>
        <a href="{{ route('registrations.index') }}" class="btn btn-sm btn-light border">
            <i class="bi bi-arrow-left me-1"></i> Kembali
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

    <form action="{{ route('registrations.store') }}" method="POST">
        @csrf

        <!-- 1. Pilih Jamaah -->
        <div class="mb-4">
            <label for="jamaah_id" class="form-label small fw-semibold">Pilih Jamaah <span class="text-danger">*</span></label>
            <select name="jamaah_id" id="jamaah_id" class="form-select @error('jamaah_id') is-invalid @enderror" required>
                <option value="">-- Cari / Pilih Jamaah Terdaftar --</option>
                @foreach($jamaahs as $j)
                    <option value="{{ $j->id }}" {{ (old('jamaah_id') == $j->id || $selectedJamaahId == $j->id) ? 'selected' : '' }}>
                        {{ $j->name }} &bull; NIK: {{ $j->nik }} ({{ $j->registration_number }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="row g-3 mb-4">
            <!-- 2. Pilih Paket -->
            <div class="col-md-6">
                <label for="package_id" class="form-label small fw-semibold">Pilih Paket Perjalanan <span class="text-danger">*</span></label>
                <select name="package_id" id="package_id" class="form-select @error('package_id') is-invalid @enderror" required onchange="updatePriceOptions(this)">
                    <option value="">-- Pilih Paket --</option>
                    @foreach($packages as $pkg)
                        <option value="{{ $pkg->id }}" 
                            data-price="{{ $pkg->price }}" 
                            data-quad="{{ $pkg->quad_price ?? $pkg->price }}" 
                            data-triple="{{ $pkg->triple_price ?? $pkg->price }}" 
                            data-double="{{ $pkg->double_price ?? $pkg->price }}"
                            {{ old('package_id') == $pkg->id ? 'selected' : '' }}>
                            {{ $pkg->name }} ({{ ucfirst($pkg->type) }} - Rp {{ number_format($pkg->price, 0, ',', '.') }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- 3. Pilih Keberangkatan -->
            <div class="col-md-6">
                <label for="departure_id" class="form-label small fw-semibold">Pilih Jadwal Keberangkatan <span class="text-danger">*</span></label>
                <select name="departure_id" id="departure_id" class="form-select @error('departure_id') is-invalid @enderror" required>
                    <option value="">-- Pilih Kloter / Jadwal --</option>
                    @foreach($departures as $dep)
                        <option value="{{ $dep->id }}" {{ old('departure_id') == $dep->id ? 'selected' : '' }}>
                            {{ $dep->code }} &bull; Berangkat: {{ $dep->departure_date->translatedFormat('d F Y') }} ({{ $dep->airline }})
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <label for="registration_date" class="form-label small fw-semibold">Tanggal Pendaftaran <span class="text-danger">*</span></label>
                <input type="date" name="registration_date" id="registration_date" class="form-control" value="{{ old('registration_date', date('Y-m-d')) }}" required>
            </div>

            <div class="col-md-4">
                <label for="room_type" class="form-label small fw-semibold">Pilihan Tipe Kamar <span class="text-danger">*</span></label>
                <select name="room_type" id="room_type" class="form-select" required onchange="applyRoomPrice(this.value)">
                    <option value="quad" {{ old('room_type') === 'quad' ? 'selected' : '' }}>Quad (Ber-4)</option>
                    <option value="triple" {{ old('room_type') === 'triple' ? 'selected' : '' }}>Triple (Ber-3)</option>
                    <option value="double" {{ old('room_type') === 'double' ? 'selected' : '' }}>Double (Ber-2)</option>
                    <option value="single" {{ old('room_type') === 'single' ? 'selected' : '' }}>Single (Ber-1)</option>
                </select>
            </div>

            <div class="col-md-4">
                <label for="agreed_price" class="form-label small fw-semibold">Harga Kesepakatan (IDR) <span class="text-danger">*</span></label>
                <input type="number" name="agreed_price" id="agreed_price" class="form-control fw-bold text-forest-green" value="{{ old('agreed_price') }}" placeholder="Contoh: 29500000" required>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label for="discount" class="form-label small fw-semibold">Potongan Harga / Diskon (IDR)</label>
                <input type="number" name="discount" id="discount" class="form-control" value="{{ old('discount', 0) }}" min="0">
                <small class="text-muted">Masukkan 0 jika tidak ada promo/diskon</small>
            </div>
            <div class="col-md-6">
                <label for="special_notes" class="form-label small fw-semibold">Catatan Khusus Pendaftaran</label>
                <input type="text" name="special_notes" id="special_notes" class="form-control" value="{{ old('special_notes') }}" placeholder="Contoh: Permintaan kamar lantai rendah, satu kloter keluarga">
            </div>
        </div>

        <div class="p-3 rounded-3 bg-light border mb-4">
            <div class="d-flex align-items-center gap-2 text-forest-green fw-semibold mb-1 small">
                <i class="bi bi-info-circle-fill text-royal-gold"></i> Catatan Integrasi Keuangan:
            </div>
            <p class="small text-muted mb-0">
                Setelah pendaftaran disimpan, sistem akan secara otomatis membuat nomor registrasi unik dan dokumen tagihan <strong>Invoice Utama</strong> bagi jamaah ini di modul Keuangan. Pembayaran DP atau cicilan dapat langsung dicatat setelahnya.
            </p>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('registrations.index') }}" class="btn btn-light border px-4">Batal</a>
            <button type="submit" class="btn btn-forest-green px-4">
                <i class="bi bi-check2-circle me-1"></i> Simpan &amp; Buat Tagihan
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    let currentPrices = { quad: 0, triple: 0, double: 0, default: 0 };

    function updatePriceOptions(select) {
        const option = select.options[select.selectedIndex];
        if (option && option.dataset.price) {
            currentPrices = {
                default: parseFloat(option.dataset.price) || 0,
                quad: parseFloat(option.dataset.quad) || parseFloat(option.dataset.price) || 0,
                triple: parseFloat(option.dataset.triple) || parseFloat(option.dataset.price) || 0,
                double: parseFloat(option.dataset.double) || parseFloat(option.dataset.price) || 0,
            };
            const roomType = document.getElementById('room_type').value;
            applyRoomPrice(roomType);
        }
    }

    function applyRoomPrice(room) {
        const inputPrice = document.getElementById('agreed_price');
        if (room === 'quad') inputPrice.value = currentPrices.quad || currentPrices.default;
        else if (room === 'triple') inputPrice.value = currentPrices.triple || currentPrices.default;
        else if (room === 'double') inputPrice.value = currentPrices.double || currentPrices.default;
        else inputPrice.value = currentPrices.double ? (currentPrices.double * 1.25) : currentPrices.default;
    }
</script>
@endpush
