@extends('layouts.app')

@section('title', 'Smart Alert Center')

@section('content')
<div class="container-fluid py-3">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h4 mb-1 fw-bold text-dark">
                <i class="bi bi-bell-fill text-danger me-2"></i>Smart Alert Center
            </h2>
            <p class="text-muted small mb-0">Deteksi Dini Kendala Tagihan, Dokumen Kurang, Anomali Biaya, dan Kesiapan Kloter</p>
        </div>
        <form action="{{ route('intelligence.alerts.scan') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="bi bi-arrow-repeat me-1"></i> Jalankan Pindai Ulang
            </button>
        </form>
    </div>

    <!-- Counters -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3">
                <span class="text-muted small">Alert Aktif</span>
                <h3 class="fw-bold mb-0 text-dark">{{ $counts['active'] }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3">
                <span class="text-muted small">Tingkat Kritis</span>
                <h3 class="fw-bold mb-0 text-danger">{{ $counts['critical'] }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3">
                <span class="text-muted small">Tingkat Waspada</span>
                <h3 class="fw-bold mb-0 text-warning">{{ $counts['warning'] }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3">
                <span class="text-muted small">Telah Diselesaikan</span>
                <h3 class="fw-bold mb-0 text-success">{{ $counts['resolved'] }}</h3>
            </div>
        </div>
    </div>

    <!-- Alert List & Filter -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-0 py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="d-flex flex-wrap gap-1">
                <a href="{{ route('intelligence.alerts.index', ['status' => 'active']) }}" class="btn btn-sm {{ request('status', 'active') === 'active' ? 'btn-dark' : 'btn-light border' }}">
                    Aktif ({{ $counts['active'] }})
                </a>
                <a href="{{ route('intelligence.alerts.index', ['status' => 'resolved']) }}" class="btn btn-sm {{ request('status') === 'resolved' ? 'btn-dark' : 'btn-light border' }}">
                    Diselesaikan ({{ $counts['resolved'] }})
                </a>
                <a href="{{ route('intelligence.alerts.index', ['level' => 'critical', 'status' => request('status', 'active')]) }}" class="btn btn-sm {{ request('level') === 'critical' ? 'btn-danger' : 'btn-light border' }}">
                    Kritis
                </a>
                <a href="{{ route('intelligence.alerts.index', ['category' => 'financial', 'status' => request('status', 'active')]) }}" class="btn btn-sm {{ request('category') === 'financial' ? 'btn-primary' : 'btn-light border' }}">
                    Finansial
                </a>
                <a href="{{ route('intelligence.alerts.index', ['category' => 'departure', 'status' => request('status', 'active')]) }}" class="btn btn-sm {{ request('category') === 'departure' ? 'btn-primary' : 'btn-light border' }}">
                    Keberangkatan
                </a>
                <a href="{{ route('intelligence.alerts.index', ['category' => 'equipment', 'status' => request('status', 'active')]) }}" class="btn btn-sm {{ request('category') === 'equipment' ? 'btn-primary' : 'btn-light border' }}">
                    Logistik
                </a>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="list-group list-group-flush">
                @forelse($alerts as $alert)
                <div class="list-group-item p-3 border-light">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1 me-3">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="badge bg-{{ $alert->level === 'critical' ? 'danger' : ($alert->level === 'warning' ? 'warning text-dark' : 'info') }}">
                                    {{ strtoupper($alert->level) }}
                                </span>
                                <span class="badge bg-light text-secondary border">{{ ucfirst($alert->category) }}</span>
                                <span class="text-muted small ms-auto">{{ $alert->created_at->diffForHumans() }}</span>
                            </div>
                            <h6 class="fw-bold mb-1 text-dark">{{ $alert->title }}</h6>
                            <p class="mb-2 text-muted small">{{ $alert->summary }}</p>

                            @if($alert->status === 'resolved')
                            <div class="small text-success">
                                <i class="bi bi-check-circle me-1"></i> Diselesaikan oleh <strong>{{ $alert->resolver?->name ?? 'Staf' }}</strong> pada {{ $alert->resolved_at?->format('d M Y H:i') }}: <em>{{ $alert->resolution_note }}</em>
                            </div>
                            @endif
                        </div>

                        <div class="text-end d-flex flex-column gap-1">
                            @if($alert->action_url)
                            <a href="{{ $alert->action_url }}" class="btn btn-outline-primary btn-sm py-1 px-2" style="font-size: 12px;">
                                {{ $alert->action_text ?? 'Lihat Data' }}
                            </a>
                            @endif

                            @if($alert->status === 'active')
                            <form action="{{ route('intelligence.alerts.resolve', $alert->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-success btn-sm py-1 px-2 w-100" style="font-size: 12px;">
                                    <i class="bi bi-check-lg"></i> Selesai
                                </button>
                            </form>

                            <form action="{{ route('intelligence.alerts.dismiss', $alert->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-light text-muted btn-sm py-1 px-2 w-100 border" style="font-size: 11px;">
                                    Abaikan
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <div class="p-4 text-center text-muted">
                    <i class="bi bi-shield-check text-success display-6 mb-2 d-block"></i>
                    Tidak ada peringatan aktif yang sesuai filter.
                </div>
                @endforelse
            </div>
            @if($alerts->hasPages())
            <div class="p-3 border-top">
                {{ $alerts->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
