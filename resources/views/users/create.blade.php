@extends('layouts.app')

@section('title', 'Tambah Pengguna Baru - Al Ghifari Haramain')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <a href="{{ route('users.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Pengguna
                </a>
                <h4 class="fw-bold mb-0 text-dark">Tambah Pengguna / Staf Baru</h4>
                <p class="text-muted small mb-0">Buat kredensial akun dan tetapkan hak akses role sistem</p>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-4">
                <form action="{{ route('users.store') }}" method="POST">
                    @csrf

                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-semibold small">Nama Lengkap Pengguna <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                value="{{ old('name') }}" required placeholder="Contoh: Muhammad Fikri, S.Kom">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Alamat Email Login <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                                value="{{ old('email') }}" required placeholder="nama@alghifari.com">
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Nomor Telepon / WhatsApp</label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" 
                                value="{{ old('phone') }}" placeholder="081234567890">
                            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-semibold small">Peran / Hak Akses (Role) <span class="text-danger">*</span></label>
                            <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                                <option value="">-- Pilih Hak Akses --</option>
                                @foreach($roles as $r)
                                    <option value="{{ $r->name }}" {{ old('role') == $r->name ? 'selected' : '' }}>
                                        {{ $r->name }} 
                                        @if($r->name == 'Super Admin') (Akses Penuh Seluruh Sistem)
                                        @elseif($r->name == 'Admin') (Operasional, Jamaah, Paket, Tiket)
                                        @elseif($r->name == 'Finance') (Invoicing, Pembayaran, Kwitansi, Pengeluaran)
                                        @elseif($r->name == 'Manager') (Monitoring Dashboard, Analitik, Approval)
                                        @elseif($r->name == 'Staff') (Pendaftaran Jamaah, Berkas, Checklist) @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Kata Sandi (Password) <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required placeholder="Minimal 8 karakter">
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Konfirmasi Kata Sandi <span class="text-danger">*</span></label>
                            <input type="password" name="password_confirmation" class="form-control" required placeholder="Ulangi kata sandi">
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <a href="{{ route('users.index') }}" class="btn btn-light px-4">Batal</a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-save me-1"></i> Simpan Pengguna
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
