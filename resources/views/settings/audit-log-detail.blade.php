@extends('layouts.app')

@section('title', 'Detail Audit Log #' . $log->id . ' - Al Ghifari Haramain')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <a href="{{ route('audit-logs.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Log
                </a>
                <h4 class="fw-bold mb-0 text-dark">Detail Audit Log #{{ $log->id }}</h4>
                <p class="text-muted small mb-0">{{ $log->created_at ? $log->created_at->format('d F Y H:i:s') : '-' }} WIB</p>
            </div>
            <div>
                <span class="badge 
                    @if(str_contains(strtoupper($log->action), 'CREATE')) bg-success
                    @elseif(str_contains(strtoupper($log->action), 'DELETE') || str_contains(strtoupper($log->action), 'CANCEL')) bg-danger
                    @elseif(str_contains(strtoupper($log->action), 'UPDATE')) bg-primary
                    @else bg-secondary @endif px-3 py-2 text-uppercase fs-6">
                    {{ $log->action }}
                </span>
            </div>
        </div>

        <!-- Meta Info Card -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-info-circle text-primary me-2"></i> Informasi Eksekusi Aksi</h6>
            </div>
            <div class="card-body p-4">
                <div class="row g-3 small">
                    <div class="col-md-4">
                        <span class="text-muted d-block">Pengguna / Operator:</span>
                        <strong class="text-dark fs-6">{{ $log->user_name ?? 'Sistem Otomatis' }}</strong>
                        @if($log->user)
                            <span class="text-muted d-block">{{ $log->user->email }}</span>
                        @endif
                    </div>
                    <div class="col-md-4">
                        <span class="text-muted d-block">Modul Terkait:</span>
                        <strong class="text-dark fs-6">{{ $log->module }}</strong>
                    </div>
                    <div class="col-md-4">
                        <span class="text-muted d-block">Alamat IP & Jaringan:</span>
                        <code class="text-dark fs-6">{{ $log->ip_address ?? '127.0.0.1' }}</code>
                    </div>
                    <div class="col-12"><hr class="my-1"></div>
                    <div class="col-12">
                        <span class="text-muted d-block">Deskripsi Lengkap Aktivitas:</span>
                        <p class="fs-6 fw-semibold text-dark mb-2">{{ $log->description }}</p>
                    </div>
                    <div class="col-12">
                        <span class="text-muted d-block">User Agent / Browser Klien:</span>
                        <code class="text-secondary small">{{ $log->user_agent ?? 'Mozilla/5.0...' }}</code>
                    </div>
                </div>
            </div>
        </div>

        <!-- Side-by-side Diff of Data -->
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-header bg-light border-bottom py-3">
                        <h6 class="fw-bold mb-0 text-danger"><i class="bi bi-dash-circle me-1"></i> Data Sebelum Perubahan (Old Values)</h6>
                    </div>
                    <div class="card-body p-3">
                        @if($log->old_values && count($log->old_values) > 0)
                            <pre class="bg-dark text-light p-3 rounded-3 small mb-0" style="max-height: 400px; overflow-y: auto;"><code>{{ json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</code></pre>
                        @else
                            <div class="text-center py-4 text-muted small">Tidak ada data sebelum perubahan (data baru / tidak tercatat).</div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-header bg-light border-bottom py-3">
                        <h6 class="fw-bold mb-0 text-success"><i class="bi bi-plus-circle me-1"></i> Data Sesudah Perubahan (New Values)</h6>
                    </div>
                    <div class="card-body p-3">
                        @if($log->new_values && count($log->new_values) > 0)
                            <pre class="bg-dark text-success-emphasis p-3 rounded-3 small mb-0" style="max-height: 400px; overflow-y: auto;"><code>{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</code></pre>
                        @else
                            <div class="text-center py-4 text-muted small">Tidak ada data sesudah perubahan (aksi hapus).</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
