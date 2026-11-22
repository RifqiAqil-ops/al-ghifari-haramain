<?php

namespace App\Services\Intelligence;

use App\Models\Departure;
use App\Models\Equipment;
use App\Models\Invoice;
use App\Models\SmartInsight;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\Intelligence\DepartureIntelligenceService;
use App\Services\Intelligence\FinancialIntelligenceService;
use Carbon\Carbon;

class AlertCenterService
{
    protected FinancialIntelligenceService $financeService;
    protected DepartureIntelligenceService $departureService;

    public function __construct(
        FinancialIntelligenceService $financeService,
        DepartureIntelligenceService $departureService
    ) {
        $this->financeService = $financeService;
        $this->departureService = $departureService;
    }

    /**
     * Scan all system operational indicators and synchronize Smart Alert Center.
     */
    public function scanAndGenerateAlerts(): array
    {
        $generatedCount = 0;
        $today = Carbon::today();

        // 1. Scan Billing: Invoices overdue or due in <= 7 days
        $dueInvoices = Invoice::with(['registration.jamaah', 'registration.departure'])
            ->whereIn('status', ['belum_bayar', 'sebagian'])
            ->where('remaining_amount', '>', 500000)
            ->whereNotNull('due_date')
            ->where('due_date', '<=', $today->copy()->addDays(7))
            ->get();

        foreach ($dueInvoices as $inv) {
            $isOverdue = Carbon::parse($inv->due_date)->isPast();
            $level = $isOverdue ? 'critical' : 'warning';
            $title = $isOverdue ? 'Tagihan Telah Melewati Jatuh Tempo' : 'Tagihan Jatuh Tempo dalam Waktu Dekat';

            $existing = SmartInsight::where('category', 'financial')
                ->where('source_entity_type', 'invoice')
                ->where('source_entity_id', $inv->id)
                ->where('status', 'active')
                ->first();

            if (!$existing) {
                SmartInsight::create([
                    'category' => 'financial',
                    'level' => $level,
                    'title' => "{$title}: {$inv->invoice_number}",
                    'summary' => "Invoice jamaah {$inv->registration?->jamaah?->name} sebesar Rp " . number_format($inv->remaining_amount, 0, ',', '.') . " belum dilunasi. Jatuh tempo: " . Carbon::parse($inv->due_date)->format('d M Y'),
                    'details' => [
                        'invoice_id' => $inv->id,
                        'remaining' => (float) $inv->remaining_amount,
                        'due_date' => $inv->due_date,
                    ],
                    'action_text' => 'Lihat Invoice',
                    'action_url' => route('finance.invoices.show', $inv->id),
                    'source_entity_type' => 'invoice',
                    'source_entity_id' => $inv->id,
                    'status' => 'active',
                ]);
                $generatedCount++;
            }
        }

        // 2. Scan Equipment: Stock <= min_stock
        $lowStock = Equipment::whereColumn('stock', '<=', 'min_stock')->where('is_active', true)->get();
        foreach ($lowStock as $eq) {
            $existing = SmartInsight::where('category', 'equipment')
                ->where('source_entity_type', 'equipment')
                ->where('source_entity_id', $eq->id)
                ->where('status', 'active')
                ->first();

            if (!$existing) {
                SmartInsight::create([
                    'category' => 'equipment',
                    'level' => 'warning',
                    'title' => "Stok Perlengkapan Menipis: {$eq->name}",
                    'summary' => "Sisa stok {$eq->name} tersisa {$eq->stock} {$eq->unit} (ambang batas minimum: {$eq->min_stock} {$eq->unit}). Segera lakukan pemesanan restock.",
                    'details' => [
                        'equipment_id' => $eq->id,
                        'stock' => $eq->stock,
                        'min_stock' => $eq->min_stock,
                    ],
                    'action_text' => 'Mutasi Stok',
                    'action_url' => route('equipment.show', $eq->id),
                    'source_entity_type' => 'equipment',
                    'source_entity_id' => $eq->id,
                    'status' => 'active',
                ]);
                $generatedCount++;
            }
        }

        // 3. Scan Departures: Readiness score < 65% for departures <= 20 days
        $upcomingDepartures = Departure::where('status', '!=', 'dibatalkan')
            ->where('departure_date', '>=', $today)
            ->where('departure_date', '<=', $today->copy()->addDays(20))
            ->get();

        foreach ($upcomingDepartures as $dep) {
            $readiness = $this->departureService->calculateReadinessScore($dep);
            if ($readiness['readiness_score'] < 65) {
                $existing = SmartInsight::where('category', 'departure')
                    ->where('source_entity_type', 'departure')
                    ->where('source_entity_id', $dep->id)
                    ->where('status', 'active')
                    ->first();

                if (!$existing) {
                    SmartInsight::create([
                        'category' => 'departure',
                        'level' => 'critical',
                        'title' => "Kesiapan Kloter Rendah: {$dep->code} ({$readiness['readiness_score']}%)",
                        'summary' => "Kloter {$dep->code} berangkat pada " . Carbon::parse($dep->departure_date)->format('d M Y') . " namun kesiapan baru mencapai {$readiness['readiness_score']}%. Ditemukan " . count($readiness['risks']) . " kendala operasional.",
                        'details' => $readiness,
                        'action_text' => 'Audit Kloter',
                        'action_url' => route('departures.show', $dep->id),
                        'source_entity_type' => 'departure',
                        'source_entity_id' => $dep->id,
                        'status' => 'active',
                    ]);
                    $generatedCount++;
                }
            }
        }

        // 4. Scan Potential Duplicate Payments
        $duplicates = $this->financeService->detectDuplicatePayments();
        foreach ($duplicates as $dup) {
            $existing = SmartInsight::where('category', 'financial')
                ->where('title', 'like', "%Indikasi Pembayaran Ganda%{$dup['invoice_number']}%")
                ->where('status', 'active')
                ->first();

            if (!$existing) {
                SmartInsight::create([
                    'category' => 'financial',
                    'level' => 'critical',
                    'title' => "Indikasi Pembayaran Ganda: Invoice {$dup['invoice_number']}",
                    'summary' => "Terdeteksi 2 transaksi pembayaran dengan nominal Rp " . number_format($dup['amount'], 0, ',', '.') . " pada invoice {$dup['invoice_number']} ({$dup['jamaah_name']}) dalam rentang 48 jam.",
                    'details' => $dup,
                    'action_text' => 'Periksa Pembayaran',
                    'action_url' => route('finance.payments.index'),
                    'source_entity_type' => 'invoice',
                    'source_entity_id' => null,
                    'status' => 'active',
                ]);
                $generatedCount++;
            }
        }

        return [
            'success' => true,
            'new_alerts_count' => $generatedCount,
            'total_active_alerts' => SmartInsight::where('status', 'active')->count(),
        ];
    }

    /**
     * Resolve an alert.
     */
    public function resolveAlert(SmartInsight $alert, User $user, ?string $note = null): bool
    {
        $alert->update([
            'status' => 'resolved',
            'resolved_by' => $user->id,
            'resolved_at' => now(),
            'resolution_note' => $note ?? 'Diselesaikan oleh staf operasional.',
        ]);

        ActivityLogger::log('ALERT_RESOLVED', 'Intelligence', "Peringatan '{$alert->title}' diselesaikan oleh {$user->name}.");

        return true;
    }

    /**
     * Dismiss an alert.
     */
    public function dismissAlert(SmartInsight $alert, User $user, ?string $note = null): bool
    {
        $alert->update([
            'status' => 'dismissed',
            'resolved_by' => $user->id,
            'resolved_at' => now(),
            'resolution_note' => $note ?? 'Peringatan diabaikan.',
        ]);

        ActivityLogger::log('ALERT_DISMISSED', 'Intelligence', "Peringatan '{$alert->title}' diabaikan oleh {$user->name}.");

        return true;
    }
}
