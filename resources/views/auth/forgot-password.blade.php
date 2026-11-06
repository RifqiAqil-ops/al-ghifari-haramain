@extends('layouts.auth')

@section('title', 'Lupa Kata Sandi')

@section('content')
<div class="text-center mb-4">
    <h5 class="fw-bold text-dark mb-1">Reset Kata Sandi</h5>
    <p class="text-muted small mb-0">Masukkan email Anda untuk menerima tautan penyetelan ulang kata sandi</p>
</div>

@if (session('status'))
    <div class="alert alert-success py-2 small mb-3">
        {{ session('status') }}
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger py-2 small mb-3">
        @foreach ($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

<form action="{{ route('password.email') }}" method="POST">
    @csrf

    <div class="mb-4">
        <label for="email" class="form-label small fw-semibold text-secondary">Alamat Email Terdaftar</label>
        <div class="input-group">
            <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-envelope"></i></span>
            <input type="email" name="email" id="email" class="form-control border-start-0 ps-0" value="{{ old('email') }}" placeholder="nama@alghifari.com" required autofocus>
        </div>
    </div>

    <button type="submit" class="btn btn-forest-green w-100 py-2 fw-semibold rounded-pill mb-3 shadow-sm">
        <i class="bi bi-send me-1"></i> Kirim Tautan Reset
    </button>

    <div class="text-center">
        <a href="{{ route('login') }}" class="small text-decoration-none text-muted">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Halaman Masuk
        </a>
    </div>
</form>
@endsection
