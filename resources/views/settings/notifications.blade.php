@extends('layouts.app')

@section('title', 'Notifikasi Sistem - Al Ghifari Haramain')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Pemberitahuan & Notifikasi Sistem</h4>
        <p class="text-muted small mb-0">Peringatan otomatis jatuh tempo pembayaran, paspor kedaluwarsa, dan jadwal rombongan</p>
    </div>
    <div class="d-flex gap-2">
        <form action="{{ route('notifications.read-all') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-check2-all me-1"></i> Tandai Semua Sudah Dibaca
            </button>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <div class="list-group list-group-flush">
            @forelse($notifications as $notif)
                <div class="list-group-item p-3 {{ $notif->is_read ? 'bg-white' : 'bg-light-subtle border-start border-4 border-primary' }}">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="d-flex gap-3">
                            <div class="stat-icon p-2 rounded-3 
                                @if($notif->type == 'danger') bg-danger-subtle text-danger
                                @elseif($notif->type == 'warning') bg-warning-subtle text-warning
                                @elseif($notif->type == 'success') bg-success-subtle text-success
                                @else bg-primary-subtle text-primary @endif" style="width: 42px; height: 42px; display: flex; align-items: center; justify-content: center;">
                                <i class="bi 
                                    @if($notif->category == 'pembayaran') bi-cash-coin
                                    @elseif($notif->category == 'dokumen') bi-file-earmark-text
                                    @elseif($notif->category == 'keberangkatan') bi-airplane
                                    @else bi-bell @endif fs-5"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1 text-dark">{{ $notif->title }}</h6>
                                <p class="text-secondary small mb-2">{{ $notif->message }}</p>
                                <span class="text-muted" style="font-size: 0.75rem;">
                                    <i class="bi bi-clock me-1"></i>{{ $notif->created_at ? $notif->created_at->diffForHumans() : '-' }}
                                </span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            @if(!$notif->is_read)
                                <span class="badge bg-primary-subtle text-primary rounded-pill px-2 py-1 small">Baru</span>
                            @endif
                            @if($notif->link)
                                <a href="{{ route('notifications.read', $notif) }}" class="btn btn-sm btn-outline-primary">
                                    Lihat <i class="bi bi-arrow-right"></i>
                                </a>
                            @else
                                @if(!$notif->is_read)
                                    <form action="{{ route('notifications.read', $notif) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-light border" title="Tandai Sudah Dibaca">
                                            <i class="bi bi-check2"></i>
                                        </button>
                                    </form>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-bell-slash display-4 mb-3 d-block"></i>
                    <h6 class="fw-semibold">Tidak ada pemberitahuan sistem saat ini.</h6>
                </div>
            @endforelse
        </div>
    </div>

    @if($notifications->hasPages())
        <div class="card-footer bg-white border-top py-3">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
@endsection
