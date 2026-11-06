@extends('layouts.app')

@section('title', 'Edit Pengguna - ' . $user->name)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <a href="{{ route('users.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Pengguna
                </a>
                <h4 class="fw-bold mb-0 text-dark">Edit Data Pengguna</h4>
                <p class="text-muted small mb-0">{{ $user->email }}</p>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-4">
                <form action="{{ route('users.update', $user) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-semibold small">Nama Lengkap Pengguna <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                value="{{ old('name', $user->name) }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Alamat Email Login <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                                value="{{ old('email', $user->email) }}" required>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Nomor Telepon / WhatsApp</label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" 
                                value="{{ old('phone', $user->phone) }}">
                            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-semibold small">Peran / Hak Akses (Role) <span class="text-danger">*</span></label>
                            @php $currentRole = $user->roles->first()?->name; @endphp
                            <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                                @foreach($roles as $r)
                                    <option value="{{ $r->name }}" {{ old('role', $currentRole) == $r->name ? 'selected' : '' }}>
                                        {{ $r->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12"><hr class="my-2"></div>
                        <div class="col-12">
                            <h6 class="fw-bold text-secondary small mb-1">Ubah Kata Sandi (Kosongkan jika tidak ingin mengubah)</h6>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Kata Sandi Baru</label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Minimal 8 karakter">
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Konfirmasi Kata Sandi Baru</label>
                            <input type="password" name="password_confirmation" class="form-control" placeholder="Ulangi kata sandi baru">
                        </div>

                        @if($user->id !== Auth::id())
                            <div class="col-12 mt-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActiveSwitch" {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label small fw-semibold" for="isActiveSwitch">Akun Pengguna Aktif (Dapat Login)</label>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <a href="{{ route('users.index') }}" class="btn btn-light px-4">Batal</a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-save me-1"></i> Perbarui Pengguna
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
