@extends('layouts.app')

@section('title', 'AI Business Assistant')

@section('content')
<div class="container-fluid py-3">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="h4 mb-1 fw-bold text-dark">
                <i class="bi bi-chat-square-dots-fill text-warning me-2"></i>AI Business Assistant
            </h2>
            <p class="text-muted small mb-0">Tanya jawab eksekutif berbasis data aktual perusahaan &bull; Mesin: <span class="badge bg-light text-dark border">{{ strtoupper($activeProvider) }}</span></p>
        </div>
        <button id="btn-new-chat" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-plus-circle me-1"></i> Percakapan Baru
        </button>
    </div>

    <div class="row g-3">
        <!-- History List (Sidebar) -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-0 py-2">
                    <span class="small fw-bold text-muted">Riwayat Percakapan</span>
                </div>
                <div class="list-group list-group-flush overflow-auto" style="max-height: 600px;">
                    @forelse($conversations as $conv)
                    <a href="{{ route('intelligence.assistant.index', ['c' => $conv->id]) }}" class="list-group-item list-group-item-action {{ $activeConversation && $activeConversation->id === $conv->id ? 'active bg-primary text-white border-primary' : '' }} py-2 px-3">
                        <div class="text-truncate small fw-semibold">{{ $conv->title }}</div>
                        <div class="d-flex justify-content-between text-muted" style="font-size: 10px; color: inherit !important;">
                            <span>{{ $conv->created_at->diffForHumans() }}</span>
                        </div>
                    </a>
                    @empty
                    <div class="p-3 text-center text-muted small">Belum ada riwayat.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Chat Area -->
        <div class="col-md-9">
            <div class="card border-0 shadow-sm rounded-3 d-flex flex-column" style="height: 650px;">
                <!-- Messages Box -->
                <div id="chat-messages" class="card-body p-4 overflow-auto flex-grow-1" style="background-color: #f8fafc;">
                    @if(!$activeConversation || $activeConversation->messages->isEmpty())
                    <div class="text-center py-5">
                        <div class="bg-primary bg-opacity-10 text-primary p-4 rounded-circle d-inline-block mb-3">
                            <i class="bi bi-robot fs-1"></i>
                        </div>
                        <h5 class="fw-bold text-dark">Selamat Datang di Al Ghifari AI Assistant</h5>
                        <p class="text-muted small max-w-md mx-auto">
                            Tanyakan apa saja seputar operasional jamaah, sisa piutang, jadwal kloter, atau status stok perlengkapan.
                        </p>

                        <!-- Suggested Prompts -->
                        <div class="d-flex flex-wrap justify-content-center gap-2 mt-4">
                            <button class="btn btn-outline-secondary btn-sm prompt-pill" data-prompt="Berapa sisa tagihan piutang jamaah saat ini?">
                                <i class="bi bi-cash-stack me-1"></i> Sisa tagihan piutang saat ini?
                            </button>
                            <button class="btn btn-outline-secondary btn-sm prompt-pill" data-prompt="Bagaimana status kloter keberangkatan terdekat?">
                                <i class="bi bi-airplane me-1"></i> Jadwal kloter terdekat?
                            </button>
                            <button class="btn btn-outline-secondary btn-sm prompt-pill" data-prompt="Apakah ada stok perlengkapan yang menipis?">
                                <i class="bi bi-box-seam me-1"></i> Stok perlengkapan yang menipis?
                            </button>
                            <button class="btn btn-outline-secondary btn-sm prompt-pill" data-prompt="Tampilkan ringkasan data jamaah aktif saat ini.">
                                <i class="bi bi-people me-1"></i> Ringkasan data jamaah aktif?
                            </button>
                        </div>
                    </div>
                    @else
                    @foreach($activeConversation->messages as $msg)
                    <div class="d-flex mb-3 {{ $msg->role === 'user' ? 'justify-content-end' : 'justify-content-start' }}">
                        <div class="card border-0 shadow-sm {{ $msg->role === 'user' ? 'bg-primary text-white' : 'bg-white text-dark' }}" style="max-width: 80%; border-radius: 14px;">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center mb-1 gap-2">
                                    <i class="bi {{ $msg->role === 'user' ? 'bi-person-fill' : 'bi-stars text-warning' }}"></i>
                                    <span class="fw-bold small">{{ $msg->role === 'user' ? 'Anda' : 'Al Ghifari Intelligence' }}</span>
                                    <span class="ms-auto" style="font-size: 10px; opacity: 0.7;">{{ $msg->created_at->format('H:i') }}</span>
                                </div>
                                <div class="message-content" style="white-space: pre-wrap; font-size: 13.5px; line-height: 1.5;">{!! nl2br(e($msg->content)) !!}</div>

                                @if($msg->grounding_data)
                                <div class="mt-2 pt-2 border-top border-light border-opacity-25" style="font-size: 11px;">
                                    <span class="badge bg-light text-dark border">Sumber Terverifikasi</span>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                    @endif
                </div>

                <!-- Input Footer -->
                <div class="card-footer bg-white border-0 p-3">
                    <form id="chat-form" class="d-flex gap-2">
                        @csrf
                        <input type="hidden" name="conversation_id" id="conversation_id" value="{{ $activeConversation?->id }}">
                        <input type="text" id="user-input" class="form-control" placeholder="Tanyakan kondisi bisnis, kloter, piutang, atau perlengkapan..." autocomplete="off" required>
                        <button type="submit" id="btn-submit" class="btn btn-primary px-4">
                            <i class="bi bi-send-fill"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const chatForm = document.getElementById('chat-form');
    const userInput = document.getElementById('user-input');
    const chatMessages = document.getElementById('chat-messages');
    const convIdInput = document.getElementById('conversation_id');
    const btnNewChat = document.getElementById('btn-new-chat');

    // Scroll to bottom
    chatMessages.scrollTop = chatMessages.scrollHeight;

    // Prompt pills
    document.querySelectorAll('.prompt-pill').forEach(btn => {
        btn.addEventListener('click', function () {
            userInput.value = this.dataset.prompt;
            chatForm.dispatchEvent(new Event('submit'));
        });
    });

    // New conversation
    btnNewChat.addEventListener('click', function () {
        fetch('{{ route("intelligence.assistant.new") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.redirect) window.location.href = data.redirect;
        });
    });

    // Send chat
    chatForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const msg = userInput.value.trim();
        if (!msg) return;

        // Append user message immediately
        const userHtml = `
            <div class="d-flex mb-3 justify-content-end">
                <div class="card border-0 shadow-sm bg-primary text-white" style="max-width: 80%; border-radius: 14px;">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center mb-1 gap-2">
                            <i class="bi bi-person-fill"></i>
                            <span class="fw-bold small">Anda</span>
                            <span class="ms-auto" style="font-size: 10px; opacity: 0.7;">Baru saja</span>
                        </div>
                        <div style="white-space: pre-wrap; font-size: 13.5px;">${msg}</div>
                    </div>
                </div>
            </div>
        `;
        chatMessages.insertAdjacentHTML('beforeend', userHtml);
        userInput.value = '';
        chatMessages.scrollTop = chatMessages.scrollHeight;

        // Add loading indicator
        const loadingId = 'loading-' + Date.now();
        const loadingHtml = `
            <div id="${loadingId}" class="d-flex mb-3 justify-content-start">
                <div class="card border-0 shadow-sm bg-white text-dark" style="max-width: 80%; border-radius: 14px;">
                    <div class="card-body p-3 d-flex align-items-center gap-2">
                        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                        <span class="text-muted small">Al Ghifari Intelligence sedang memproses data...</span>
                    </div>
                </div>
            </div>
        `;
        chatMessages.insertAdjacentHTML('beforeend', loadingHtml);
        chatMessages.scrollTop = chatMessages.scrollHeight;

        fetch('{{ route("intelligence.assistant.chat") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                message: msg,
                conversation_id: convIdInput.value || null
            })
        })
        .then(res => res.json())
        .then(data => {
            document.getElementById(loadingId)?.remove();
            if (data.success) {
                if (!convIdInput.value && data.conversation_id) {
                    convIdInput.value = data.conversation_id;
                }
                const formattedReply = data.reply.replace(/\n/g, '<br>');
                const botHtml = `
                    <div class="d-flex mb-3 justify-content-start">
                        <div class="card border-0 shadow-sm bg-white text-dark" style="max-width: 80%; border-radius: 14px;">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center mb-1 gap-2">
                                    <i class="bi bi-stars text-warning"></i>
                                    <span class="fw-bold small">Al Ghifari Intelligence</span>
                                    <span class="badge bg-light text-dark border ms-2" style="font-size: 10px;">${data.provider}</span>
                                    <span class="ms-auto text-muted" style="font-size: 10px;">Baru saja</span>
                                </div>
                                <div style="font-size: 13.5px; line-height: 1.5;">${formattedReply}</div>
                                <div class="mt-2 pt-2 border-top border-light" style="font-size: 11px;">
                                    <span class="badge bg-light text-secondary border">Sumber Terverifikasi</span>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                chatMessages.insertAdjacentHTML('beforeend', botHtml);
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }
        })
        .catch(err => {
            document.getElementById(loadingId)?.remove();
            alert('Gagal menghubungi AI Service.');
        });
    });
});
</script>
@endsection
