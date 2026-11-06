@extends('layouts.app')

@section('title', 'Tambah Jadwal Keberangkatan - Al Ghifari Haramain')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <a href="{{ route('departures.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
                </a>
                <h4 class="fw-bold mb-0 text-dark">Buat Jadwal Keberangkatan Baru</h4>
                <p class="text-muted small mb-0">Rencanakan grup rombongan, penerbangan, dan kuota jamaah</p>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-4">
                <form action="{{ route('departures.store') }}" method="POST">
                    @csrf

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary small">Kode Keberangkatan <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" 
                                value="{{ old('code', 'DEP-' . date('Ym') . '-' . strtoupper(Str::random(3))) }}" required placeholder="Contoh: DEP-202611-VIP">
                            @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <div class="form-text small">Kode unik identifikasi rombongan / kloter.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary small">Paket Perjalanan <span class="text-danger">*</span></label>
                            <select name="package_id" class="form-select @error('package_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Paket --</option>
                                @foreach($packages as $pkg)
                                    <option value="{{ $pkg->id }}" {{ old('package_id') == $pkg->id ? 'selected' : '' }}>
                                        [{{ strtoupper($pkg->type) }}] {{ $pkg->name }} (Rp {{ number_format($pkg->price_quad, 0, ',', '.') }})
                                    </option>
                                @endforeach
                            </select>
                            @error('package_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary small">Tanggal Berangkat <span class="text-danger">*</span></label>
                            <input type="date" name="departure_date" class="form-control @error('departure_date') is-invalid @enderror" 
                                value="{{ old('departure_date') }}" required>
                            @error('departure_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary small">Tanggal Kepulangan <span class="text-danger">*</span></label>
                            <input type="date" name="return_date" class="form-control @error('return_date') is-invalid @enderror" 
                                value="{{ old('return_date') }}" required>
                            @error('return_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12"><hr class="text-muted my-2"></div>
                        <div class="col-12"><h6 class="fw-bold text-success mb-2"><i class="bi bi-airplane me-1"></i> Rute & Detail Penerbangan</h6></div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary small">Bandara Asal / Keberangkatan</label>
                            <input type="text" name="origin_airport" class="form-control @error('origin_airport') is-invalid @enderror" 
                                value="{{ old('origin_airport', 'Soekarno-Hatta (CGK) Jakarta') }}" placeholder="Bandara Asal">
                            @error('origin_airport') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary small">Bandara Tujuan</label>
                            <input type="text" name="destination_airport" class="form-control @error('destination_airport') is-invalid @enderror" 
                                value="{{ old('destination_airport', 'King Abdulaziz (JED) Jeddah') }}" placeholder="Bandara Tujuan">
                            @error('destination_airport') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-secondary small">Maskapai Penerbangan</label>
                            <input type="text" name="airline" class="form-control @error('airline') is-invalid @enderror" 
                                value="{{ old('airline', 'Saudia Airlines') }}" placeholder="Contoh: Saudia Airlines, Garuda Indonesia">
                            @error('airline') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-secondary small">Nomor Penerbangan</label>
                            <input type="text" name="flight_number" class="form-control @error('flight_number') is-invalid @enderror" 
                                value="{{ old('flight_number', 'SV-819') }}" placeholder="Contoh: SV-819">
                            @error('flight_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-semibold text-secondary small">Jam Berangkat</label>
                            <input type="time" name="departure_time" class="form-control @error('departure_time') is-invalid @enderror" 
                                value="{{ old('departure_time', '11:00') }}">
                            @error('departure_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-semibold text-secondary small">Jam Tiba</label>
                            <input type="time" name="arrival_time" class="form-control @error('arrival_time') is-invalid @enderror" 
                                value="{{ old('arrival_time', '17:30') }}">
                            @error('arrival_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12"><hr class="text-muted my-2"></div>
                        <div class="col-12"><h6 class="fw-bold text-success mb-2"><i class="bi bi-people me-1"></i> Pembimbing & Kapasitas Kuota</h6></div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-secondary small">Kuota Seat Rombongan <span class="text-danger">*</span></label>
                            <input type="number" name="quota" class="form-control @error('quota') is-invalid @enderror" 
                                value="{{ old('quota', 45) }}" min="1" required>
                            @error('quota') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-secondary small">Nama Tour Leader / Muthawif</label>
                            <input type="text" name="leader_name" class="form-control @error('leader_name') is-invalid @enderror" 
                                value="{{ old('leader_name') }}" placeholder="Nama Ustadz / Pembimbing">
                            @error('leader_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-secondary small">Nomor WhatsApp Leader</label>
                            <input type="text" name="leader_phone" class="form-control @error('leader_phone') is-invalid @enderror" 
                                value="{{ old('leader_phone') }}" placeholder="Contoh: 081234567890">
                            @error('leader_phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary small">Status Jadwal <span class="text-danger">*</span></label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="persiapan" {{ old('status') == 'persiapan' ? 'selected' : '' }}>Persiapan</option>
                                <option value="siap_berangkat" {{ old('status') == 'siap_berangkat' ? 'selected' : '' }}>Siap Berangkat</option>
                                <option value="berangkat" {{ old('status') == 'berangkat' ? 'selected' : '' }}>Berangkat</option>
                                <option value="dalam_perjalanan" {{ old('status') == 'dalam_perjalanan' ? 'selected' : '' }}>Dalam Perjalanan</option>
                                <option value="pulang" {{ old('status') == 'pulang' ? 'selected' : '' }}>Pulang</option>
                                <option value="selesai" {{ old('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                                <option value="dibatalkan" {{ old('status') == 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                            </select>
                            @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary small">Catatan Tambahan</label>
                            <input type="text" name="notes" class="form-control @error('notes') is-invalid @enderror" 
                                value="{{ old('notes') }}" placeholder="Catatan transit, briefing bandara, dsb.">
                            @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <a href="{{ route('departures.index') }}" class="btn btn-light px-4">Batal</a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-save me-1"></i> Simpan Jadwal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
