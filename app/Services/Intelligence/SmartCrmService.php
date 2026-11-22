<?php

namespace App\Services\Intelligence;

use App\Models\EquipmentDistribution;
use App\Models\Invoice;
use App\Models\Jamaah;
use App\Models\Registration;
use App\Models\WhatsAppMessage;
use App\Services\WhatsAppService;
use Carbon\Carbon;

class SmartCrmService
{
    protected WhatsAppService $whatsAppService;

    public function __construct(WhatsAppService $whatsAppService)
    {
        $this->whatsAppService = $whatsAppService;
    }

    /**
     * Get Jamaah 360 Full Profile view.
     */
    public function getJamaah360Profile(Jamaah $jamaah): array
    {
        $jamaah->load(['contacts', 'documents', 'registrations.package', 'registrations.departure', 'registrations.invoice.payments']);

        $totalRegistrations = $jamaah->registrations->count();
        $activeRegistration = $jamaah->registrations->where('status', '!=', 'batal')->first();

        // Calculate lifetime spending
        $lifetimeSpent = 0;
        $totalOutstanding = 0;
        foreach ($jamaah->registrations as $reg) {
            if ($reg->invoice) {
                $lifetimeSpent += (float) $reg->invoice->paid_amount;
                if ($reg->status !== 'batal') {
                    $totalOutstanding += (float) $reg->invoice->remaining_amount;
                }
            }
        }

        // WhatsApp history
        $waMessages = WhatsAppMessage::where('jamaah_id', $jamaah->id)
            ->orWhere('phone_number', WhatsAppService::formatPhoneNumber($jamaah->phone ?? ''))
            ->latest()
            ->take(10)
            ->get();

        // Equipment history
        $equipmentDistributions = EquipmentDistribution::with(['items.equipment', 'departure'])
            ->where('jamaah_id', $jamaah->id)
            ->latest()
            ->get();

        // Determine Segment
        $segment = $this->determineSegment($jamaah, $totalRegistrations, $lifetimeSpent, $totalOutstanding, $activeRegistration);

        return [
            'jamaah' => $jamaah,
            'segment' => $segment,
            'metrics' => [
                'total_trips' => $totalRegistrations,
                'lifetime_spent' => $lifetimeSpent,
                'current_outstanding' => $totalOutstanding,
                'active_departure' => $activeRegistration?->departure?->code ?? 'Tidak ada',
                'departure_date' => $activeRegistration?->departure?->departure_date ?? null,
            ],
            'whatsapp_history' => $waMessages,
            'equipment_history' => $equipmentDistributions,
        ];
    }

    /**
     * Determine Smart Segment for a Jamaah.
     */
    public function determineSegment(Jamaah $jamaah, int $trips, float $lifetimeSpent, float $outstanding, ?Registration $activeRegistration): array
    {
        if ($trips >= 2) {
            return [
                'name' => 'Jamaah Loyal (Repeat Customer)',
                'badge' => 'primary',
                'description' => 'Telah mengikuti lebih dari 1 kali program perjalanan ibadah bersama Al Ghifari.',
            ];
        }

        if ($lifetimeSpent >= 50000000) {
            return [
                'name' => 'VIP / High Value',
                'badge' => 'warning',
                'description' => 'Kontribusi finansial bernilai tinggi (Paket VIP/Plus/Furoda).',
            ];
        }

        if ($outstanding > 5000000 && $activeRegistration) {
            return [
                'name' => 'Perhatian Khusus (Piutang Tertagih)',
                'badge' => 'danger',
                'description' => 'Memiliki sisa tagihan aktif yang perlu ditindaklanjuti sebelum batas waktu.',
            ];
        }

        if ($activeRegistration && $activeRegistration->status === 'siap_berangkat') {
            return [
                'name' => 'Siap Berangkat',
                'badge' => 'success',
                'description' => 'Seluruh persyaratan administratif dan keuangan telah lengkap.',
            ];
        }

        return [
            'name' => 'Jamaah Reguler',
            'badge' => 'secondary',
            'description' => 'Jamaah aktif dalam siklus layanan normal.',
        ];
    }

    /**
     * Generate dynamic follow-up queue for customer service and finance staff.
     */
    public function generateFollowUpQueue(): array
    {
        $queue = [];
        $today = Carbon::today();

        // 1. Follow-up Tagihan Jatuh Tempo (H-7)
        $dueInvoices = Invoice::with(['registration.jamaah', 'registration.departure'])
            ->whereIn('status', ['belum_bayar', 'sebagian'])
            ->where('remaining_amount', '>', 0)
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [$today, $today->copy()->addDays(7)])
            ->get();

        foreach ($dueInvoices as $inv) {
            $queue[] = [
                'type' => 'billing',
                'priority' => 'Tinggi',
                'badge' => 'danger',
                'jamaah_id' => $inv->registration?->jamaah_id,
                'jamaah_name' => $inv->registration?->jamaah?->name ?? 'Jamaah',
                'phone' => $inv->registration?->jamaah?->phone ?? '',
                'title' => 'Tagihan Jatuh Tempo dalam ' . $today->diffInDays(Carbon::parse($inv->due_date)) . ' hari',
                'amount' => (float) $inv->remaining_amount,
                'target_date' => $inv->due_date,
                'suggested_action' => 'Kirim Pengingat Pembayaran WhatsApp',
            ];
        }

        // 2. Follow-up Dokumen Belum Lengkap (H-30 keberangkatan)
        $upcomingRegistrations = Registration::with(['jamaah.documents', 'departure'])
            ->where('status', '!=', 'batal')
            ->whereHas('departure', function ($q) use ($today) {
                $q->whereBetween('departure_date', [$today, $today->copy()->addDays(30)]);
            })
            ->get();

        foreach ($upcomingRegistrations as $reg) {
            $hasPaspor = $reg->jamaah?->documents->where('document_type', 'paspor')->where('status', 'diverifikasi')->isNotEmpty();
            if (!$hasPaspor) {
                $queue[] = [
                    'type' => 'document',
                    'priority' => 'Kritis',
                    'badge' => 'warning',
                    'jamaah_id' => $reg->jamaah_id,
                    'jamaah_name' => $reg->jamaah?->name ?? 'Jamaah',
                    'phone' => $reg->jamaah?->phone ?? '',
                    'title' => 'Paspor Belum Lengkap (Kloter ' . ($reg->departure?->code ?? '') . ')',
                    'amount' => 0,
                    'target_date' => $reg->departure?->departure_date,
                    'suggested_action' => 'Kirim Pengingat Berkas Paspor WhatsApp',
                ];
            }
        }

        return $queue;
    }

    /**
     * Dispatch WhatsApp follow-up using existing WhatsAppService.
     */
    public function sendWhatsAppFollowUp(int $jamaahId, string $phone, string $message, string $category = 'crm_followup'): array
    {
        return $this->whatsAppService->sendTextMessage($phone, $message, $category, $jamaahId);
    }
}
