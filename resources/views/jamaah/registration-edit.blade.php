@extends('layouts.app')

@section('title', 'Edit Registrasi - ' . $registration->registration_number)
@section('header_title', 'Edit Data Registrasi')

@section('content')
<div class="card card-custom p-4" style="max-width: 900px; margin: 0 auto;">
    <div class="d-flex align-items-center justify-content-between border-bottom pb-3 mb-4">
        <div>
            <h5 class="fw-bold text-forest-green mb-1">
                <i class="bi bi-pencil-square text-royal-gold me-2"></i> Edit Registrasi Paket
            </h5>
            <p class="text-muted small mb-0">
                No. Registrasi: <span class="fw-semibold font-monospace">{{ $registration->registration_number }}</span>
            </p>
        </div>
        <a href="{{ route('registrations.show', $registration) }}" class="btn btn-sm btn-light border">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Detail
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

    {{-- Info Jamaah (read-only) --}}
    <div class="p-3 rounded-3 border bg-light mb-4">
        <div class="d-flex align-items-center gap-2 text-forest-green fw-semibold mb-2 small">
            <i class="bi bi-person-circle text-royal-gold"></i> Identitas Jamaah (tidak dapat diubah)
        </div>
        <div class="row g-2 small">
            <div class="col-md-4">
                <span class="text-muted d-block">Nama Jamaah</span>
                <span class="fw-bold">{{ $registration->jamaah?->name ?? 'Jamaah telah dihapus' }}</span>
            </div>
            <div class="col-md-4">
                <span class="text-muted d-block">NIK</span>
                <span class="fw-semibold font-monospace">{{ $registration->jamaah?->nik ?? '-' }}</span>
            </div>
            <div class="col-md-4">
                <span class="text-muted d-block">No. Registrasi</span>
                <span class="fw-semibold font-monospace text-forest-green">{{ $registration->registration_number }}</span>
            </div>
        </div>
    </div>

    {{-- Financial impact warning --}}
    @if($registration->invoice && $registration->invoice->paid_amount > 0)
        <div class="alert alert-warning py-2 small mb-4">
            <i class="bi bi-exclamation-triangle-fill me-1"></i>
            <strong>Perhatian:</strong> Jamaah ini sudah membayar
            <strong>Rp {{ number_format($registration->invoice->paid_amount, 0, ',', '.') }}</strong>.
            Mengubah biaya akan merekalkulas sisa tagihan secara otomatis.
            @if((float) $registration->invoice->paid_amount >= (float) $registration->final_price)
                <span class="text-danger d-block mt-1">
                    <i class="bi bi-info-circle me-1"></i>
                    Pembayaran sudah mencapai atau melebihi biaya saat ini. Periksa kembali biaya baru yang diinput.
                </span>
            @endif
        </div>
    @endif

    <form action="{{ route('registrations.update', $registration) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row g-3 mb-4">
            {{-- Pilih Paket --}}
            <div class="col-md-6">
                <label for="package_id" class="form-label small fw-semibold">
                    Pilih Paket Perjalanan <span class="text-danger">*</span>
                </label>
                <select name="package_id" id="package_id"
                    class="form-select @error('package_id') is-invalid @enderror"
                    required onchange="updatePriceOptions(this)">
                    <option value="">-- Pilih Paket --</option>
                    @foreach($packages as $pkg)
                        <option value="{{ $pkg->id }}"
                            data-price="{{ $pkg->price }}"
                            data-quad="{{ $pkg->quad_price ?? $pkg->price }}"
                            data-triple="{{ $pkg->triple_price ?? $pkg->price }}"
                            data-double="{{ $pkg->double_price ?? $pkg->price }}"
                            {{ old('package_id', $registration->package_id) == $pkg->id ? 'selected' : '' }}>
                            {{ $pkg->name }} ({{ ucfirst($pkg->type) }} - Rp {{ number_format($pkg->price, 0, ',', '.') }})
                        </option>
                    @endforeach
                </select>
                @error('package_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Pilih Keberangkatan --}}
            <div class="col-md-6">
                <label for="departure_id" class="form-label small fw-semibold">
                    Jadwal Keberangkatan <span class="text-danger">*</span>
                </label>
                <select name="departure_id" id="departure_id"
                    class="form-select @error('departure_id') is-invalid @enderror" required>
                    <option value="">-- Pilih Kloter / Jadwal --</option>
                    @foreach($departures as $dep)
                        <option value="{{ $dep->id }}"
                            {{ old('departure_id', $registration->departure_id) == $dep->id ? 'selected' : '' }}>
                            {{ $dep->code }} &bull; {{ $dep->departure_date ? \Carbon\Carbon::parse($dep->departure_date)->translatedFormat('d M Y') : '-' }}
                            {{ $dep->airline ? '(' . $dep->airline . ')' : '' }}
                        </option>
                    @endforeach
                </select>
                @error('departure_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="row g-3 mb-4">
            {{-- Tanggal Pendaftaran --}}
            <div class="col-md-4">
                <label for="registration_date" class="form-label small fw-semibold">
                    Tanggal Pendaftaran <span class="text-danger">*</span>
                </label>
                <input type="date" name="registration_date" id="registration_date"
                    class="form-control @error('registration_date') is-invalid @enderror"
                    value="{{ old('registration_date', $registration->registration_date instanceof \Carbon\Carbon ? $registration->registration_date->format('Y-m-d') : $registration->registration_date) }}"
                    required>
                @error('registration_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Tipe Kamar --}}
            <div class="col-md-4">
                <label for="room_type" class="form-label small fw-semibold">
                    Tipe Kamar <span class="text-danger">*</span>
                </label>
                <select name="room_type" id="room_type"
                    class="form-select @error('room_type') is-invalid @enderror"
                    required onchange="applyRoomPrice(this.value)">
                    <option value="quad"   {{ old('room_type', $registration->room_type) === 'quad'   ? 'selected' : '' }}>Quad (Ber-4)</option>
                    <option value="triple" {{ old('room_type', $registration->room_type) === 'triple' ? 'selected' : '' }}>Triple (Ber-3)</option>
                    <option value="double" {{ old('room_type', $registration->room_type) === 'double' ? 'selected' : '' }}>Double (Ber-2)</option>
                    <option value="single" {{ old('room_type', $registration->room_type) === 'single' ? 'selected' : '' }}>Single (Ber-1)</option>
                </select>
                @error('room_type')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Status --}}
            <div class="col-md-4">
                <label for="status" class="form-label small fw-semibold">
                    Status Registrasi <span class="text-danger">*</span>
                </label>
                <select name="status" id="status"
                    class="form-select @error('status') is-invalid @enderror" required>
                    <option value="terdaftar"     {{ old('status', $registration->status) === 'terdaftar'     ? 'selected' : '' }}>Terdaftar</option>
                    <option value="proses"        {{ old('status', $registration->status) === 'proses'        ? 'selected' : '' }}>Proses Kelengkapan</option>
                    <option value="siap_berangkat" {{ old('status', $registration->status) === 'siap_berangkat' ? 'selected' : '' }}>Siap Berangkat</option>
                    <option value="berangkat"     {{ old('status', $registration->status) === 'berangkat'     ? 'selected' : '' }}>Berangkat</option>
                </select>
                @error('status')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="row g-3 mb-4">
            {{-- Harga Kesepakatan --}}
            <div class="col-md-4">
                <label for="agreed_price" class="form-label small fw-semibold">
                    Harga Kesepakatan (IDR) <span class="text-danger">*</span>
                </label>
                <input type="number" name="agreed_price" id="agreed_price"
                    class="form-control fw-bold text-forest-green @error('agreed_price') is-invalid @enderror"
                    value="{{ old('agreed_price', $registration->agreed_price) }}"
                    placeholder="Contoh: 29500000" min="0" required>
                @error('agreed_price')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Diskon --}}
            <div class="col-md-4">
                <label for="discount" class="form-label small fw-semibold">
                    Potongan / Diskon (IDR)
                </label>
                <input type="number" name="discount" id="discount"
                    class="form-control @error('discount') is-invalid @enderror"
                    value="{{ old('discount', $registration->discount ?? 0) }}"
                    min="0">
                <small class="text-muted">Masukkan 0 jika tidak ada diskon</small>
                @error('discount')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Catatan --}}
            <div class="col-md-4">
                <label for="special_notes" class="form-label small fw-semibold">
                    Catatan Khusus
                </label>
                <input type="text" name="special_notes" id="special_notes"
                    class="form-control @error('special_notes') is-invalid @enderror"
                    value="{{ old('special_notes', $registration->special_notes) }}"
                    placeholder="Contoh: Permintaan kamar lantai rendah">
                @error('special_notes')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- Kalkulasi biaya --}}
        <div class="p-3 rounded-3 bg-light border mb-4">
            <div class="d-flex align-items-center gap-2 text-forest-green fw-semibold mb-1 small">
                <i class="bi bi-calculator text-royal-gold"></i> Kalkulasi Biaya Akhir
            </div>
            <p class="small text-muted mb-0">
                Biaya akhir = Harga Kesepakatan &minus; Diskon.
                Jika biaya berubah dari
                <strong>Rp {{ number_format($registration->final_price, 0, ',', '.') }}</strong>,
                sistem akan memperbarui total invoice dan menghitung ulang sisa tagihan secara otomatis.
                Riwayat pembayaran yang sudah ada tidak akan dihapus.
            </p>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('registrations.show', $registration) }}" class="btn btn-light border px-4">
                Batal
            </a>
            <button type="submit" class="btn btn-forest-green px-4">
                <i class="bi bi-check2-circle me-1"></i> Simpan Perubahan
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
                quad:    parseFloat(option.dataset.quad)   || parseFloat(option.dataset.price) || 0,
                triple:  parseFloat(option.dataset.triple) || parseFloat(option.dataset.price) || 0,
                double:  parseFloat(option.dataset.double) || parseFloat(option.dataset.price) || 0,
            };
            const roomType = document.getElementById('room_type').value;
            applyRoomPrice(roomType);
        }
    }

    function applyRoomPrice(room) {
        const inputPrice = document.getElementById('agreed_price');
        if (room === 'quad')        inputPrice.value = currentPrices.quad   || currentPrices.default;
        else if (room === 'triple') inputPrice.value = currentPrices.triple || currentPrices.default;
        else if (room === 'double') inputPrice.value = currentPrices.double || currentPrices.default;
        else inputPrice.value = currentPrices.double ? (currentPrices.double * 1.25) : currentPrices.default;
    }
</script>
@endpush
