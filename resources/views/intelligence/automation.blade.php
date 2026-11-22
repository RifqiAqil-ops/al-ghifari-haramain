@extends('layouts.app')

@section('title', 'Automation Engine')

@section('content')
<div class="container-fluid py-3">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h4 mb-1 fw-bold text-dark">
                <i class="bi bi-robot text-primary me-2"></i>Automation Engine &amp; Controlled Execution
            </h2>
            <p class="text-muted small mb-0">Aturan Otomasi Terjadwal, Peringatan Otomatis, dan Riwayat Audit Eksekusi Berulang</p>
        </div>
        <div class="badge bg-light text-dark border px-3 py-2">
            <i class="bi bi-shield-lock text-success me-1"></i> Idempotent Guard &bull; Human Authorization Safe
        </div>
    </div>

    <!-- Active Rules List -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="card-title mb-0 fw-bold fs-6">
                <i class="bi bi-gear-wide-connected text-primary me-2"></i>Daftar Aturan Otomasi Aktif ({{ $rules->count() }})
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                    <thead class="table-light">
                        <tr>
                            <th>Nama Aturan Otomasi</th>
                            <th>Kategori</th>
                            <th>Tipe Pemicu (Trigger)</th>
                            <th>Mode Eksekusi</th>
                            <th>Terakhir Dijalankan</th>
                            <th>Status</th>
                            <th class="text-end">Aksi Kontrol</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rules as $rule)
                        <tr>
                            <td>
                                <div class="fw-bold text-dark">{{ $rule->name }}</div>
                                <code class="small text-muted">{{ $rule->code }}</code>
                            </td>
                            <td><span class="badge bg-light text-dark border">{{ ucfirst($rule->category) }}</span></td>
                            <td>{{ $rule->trigger_type }}</td>
                            <td>
                                @if($rule->is_autonomous)
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">Otonom (Risiko Rendah)</span>
                                @else
                                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25">Perlu Otorisasi Staf</span>
                                @endif
                            </td>
                            <td>{{ $rule->last_run_at ? $rule->last_run_at->diffForHumans() : 'Belum pernah' }}</td>
                            <td>
                                <span class="badge bg-{{ $rule->is_active ? 'success' : 'secondary' }}">
                                    {{ $rule->is_active ? 'AKTIF' : 'NONAKTIF' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <form action="{{ route('intelligence.automation.run', $rule->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-primary btn-sm py-1 px-2" style="font-size: 12px;">
                                            <i class="bi bi-play-fill me-1"></i> Trigger
                                        </button>
                                    </form>

                                    <form action="{{ route('intelligence.automation.toggle', $rule->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-{{ $rule->is_active ? 'outline-secondary' : 'outline-success' }} btn-sm py-1 px-2" style="font-size: 12px;">
                                            {{ $rule->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Belum ada aturan otomasi terdaftar.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Execution Logs -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="card-title mb-0 fw-bold fs-6">
                <i class="bi bi-journal-text text-secondary me-2"></i>Log Riwayat Eksekusi Otomasi (Execution History)
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0" style="font-size: 12.5px;">
                    <thead class="table-light">
                        <tr>
                            <th>Waktu</th>
                            <th>Aturan / Tindakan</th>
                            <th>Sumber</th>
                            <th>Status</th>
                            <th>Idempotency Key</th>
                            <th>Dieksekusi Oleh</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentLogs as $log)
                        <tr>
                            <td>{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                            <td>
                                <div class="fw-semibold">{{ $log->action_taken }}</div>
                                @if($log->error_message)
                                <small class="text-danger">{{ $log->error_message }}</small>
                                @endif
                            </td>
                            <td><span class="badge bg-light text-dark border">{{ $log->trigger_source }}</span></td>
                            <td>
                                <span class="badge bg-{{ $log->status === 'success' ? 'success' : ($log->status === 'skipped' ? 'secondary' : 'danger') }}">
                                    {{ strtoupper($log->status) }}
                                </span>
                            </td>
                            <td><code style="font-size: 11px;">{{ $log->idempotency_key }}</code></td>
                            <td>{{ $log->executor?->name ?? 'Scheduler System' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3">Belum ada riwayat eksekusi tercatat.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($recentLogs->hasPages())
            <div class="p-3 border-top">
                {{ $recentLogs->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
