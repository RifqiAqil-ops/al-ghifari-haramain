<?php

namespace App\Http\Controllers;

use App\Models\Jamaah;
use App\Models\WhatsAppMessage;
use App\Models\WhatsAppTemplate;
use App\Services\ActivityLogger;
use App\Services\WhatsAppService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WhatsAppController extends Controller
{
    protected WhatsAppService $waService;

    public function __construct(WhatsAppService $waService)
    {
        $this->waService = $waService;
    }

    public function index(Request $request): View
    {
        $search = $request->input('search');
        $status = $request->input('status');

        $query = WhatsAppMessage::with(['jamaah', 'sender']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('phone_number', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%")
                  ->orWhereHas('jamaah', fn($jq) => $jq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        $messages = $query->latest()->paginate(15)->withQueryString();
        $templates = WhatsAppTemplate::where('is_active', true)->get();
        $jamaahs = Jamaah::where('status', 'aktif')->orderBy('name')->get();

        $isConnected = $this->waService->isConnected();
        $isSimulation = $this->waService->isSimulationMode();

        return view('whatsapp.index', compact('messages', 'templates', 'jamaahs', 'isConnected', 'isSimulation', 'search', 'status'));
    }

    public function sendMessage(Request $request): RedirectResponse
    {
        $request->validate([
            'phone_number' => ['required', 'string'],
            'message_type' => ['required', 'in:manual,template'],
            'content' => ['required_if:message_type,manual', 'nullable', 'string'],
            'template_code' => ['required_if:message_type,template', 'nullable', 'string'],
            'jamaah_id' => ['nullable', 'exists:jamaahs,id'],
        ], [
            'phone_number.required' => 'Nomor WhatsApp tujuan wajib diisi.',
            'content.required_if' => 'Isi pesan wajib diisi untuk pesan manual.',
            'template_code.required_if' => 'Template wajib dipilih jika menggunakan tipe template.',
        ]);

        $phone = $request->phone_number;
        $jamaahId = $request->jamaah_id;

        if ($request->message_type === 'manual') {
            $result = $this->waService->sendTextMessage($phone, $request->content, 'manual', $jamaahId);
        } else {
            $jamaah = $jamaahId ? Jamaah::find($jamaahId) : null;
            $variables = [
                'nama' => $jamaah?->name ?? 'Jamaah',
                'no_registrasi' => $jamaah?->registration_number ?? '-',
                'sisa_tagihan' => 'Rp 0',
                'daftar_dokumen' => "• Paspor Asli\n• Pas Foto 4x6 Background Putih",
                'batas_dokumen' => now()->addDays(7)->translatedFormat('d F Y'),
                'nama_paket' => 'Paket Umrah Al Ghifari',
                'tanggal_berangkat' => now()->addDays(15)->translatedFormat('d F Y'),
                'maskapai_flight' => 'Saudia Airlines SV819',
                'waktu_kumpul' => '07:00 WIB',
                'tour_leader' => 'Ustadz Abdullah',
                'kontak_tl' => '081299887766',
            ];

            $result = $this->waService->sendTemplateMessage($phone, $request->template_code, $variables, $jamaahId);
        }

        ActivityLogger::log('WHATSAPP', 'WhatsApp', "Mengirim pesan WhatsApp ke {$phone} (Status: {$result['status']})");

        if ($result['success']) {
            return back()->with('toast_success', $result['message']);
        }

        return back()->with('toast_danger', 'Gagal mengirim pesan: ' . $result['message']);
    }

    public function templates(): View
    {
        $templates = WhatsAppTemplate::latest()->paginate(10);
        return view('whatsapp.templates', compact('templates'));
    }

    public function storeTemplate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:whatsapp_templates,code'],
            'category' => ['required', 'in:pembayaran,dokumen,keberangkatan,umum'],
            'body' => ['required', 'string'],
        ]);

        $validated['is_active'] = true;
        $template = WhatsAppTemplate::create($validated);

        ActivityLogger::log('CREATE_TEMPLATE', 'WhatsApp', "Membuat template pesan baru: {$template->name} ({$template->code})");

        return back()->with('toast_success', 'Template pesan WhatsApp berhasil dibuat.');
    }

    public function updateTemplate(Request $request, WhatsAppTemplate $template): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'in:pembayaran,dokumen,keberangkatan,umum'],
            'body' => ['required', 'string'],
            'is_active' => ['boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $template->update($validated);

        ActivityLogger::log('UPDATE_TEMPLATE', 'WhatsApp', "Memperbarui template pesan: {$template->name}");

        return back()->with('toast_success', 'Template pesan berhasil diperbarui.');
    }
}
