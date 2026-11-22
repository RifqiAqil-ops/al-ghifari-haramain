<?php

namespace App\Services\Intelligence;

use App\Models\Departure;
use App\Models\EquipmentDistribution;
use App\Models\JamaahDocument;
use Carbon\Carbon;

class DepartureIntelligenceService
{
    /**
     * Compute Departure Readiness Score & Audit Breakdown for a Kloter.
     */
    public function calculateReadinessScore(Departure $departure): array
    {
        $departure->load(['registrations.jamaah.documents', 'registrations.invoice.payments', 'package', 'checklists']);

        $activeRegistrations = $departure->registrations->where('status', '!=', 'batal');
        $totalRegistered = $activeRegistrations->count();
        $quota = max(1, $departure->quota);

        // 1. Quota Fill Score (Max 20 pts)
        $quotaRatio = min(1.0, $totalRegistered / $quota);
        $quotaScore = round($quotaRatio * 20, 1);

        // 2. Document Verification Score (Max 30 pts)
        $jamaahWithVerifiedDocs = 0;
        $missingDocsCount = 0;
        foreach ($activeRegistrations as $reg) {
            $hasVerifiedKtp = $reg->jamaah?->documents
                ->where('document_type', 'ktp')
                ->where('status', 'diverifikasi')
                ->isNotEmpty();

            $hasVerifiedPaspor = $reg->jamaah?->documents
                ->where('document_type', 'paspor')
                ->where('status', 'diverifikasi')
                ->isNotEmpty();

            if ($hasVerifiedKtp && $hasVerifiedPaspor) {
                $jamaahWithVerifiedDocs++;
            } else {
                $missingDocsCount++;
            }
        }
        $docRatio = $totalRegistered > 0 ? ($jamaahWithVerifiedDocs / $totalRegistered) : 0;
        $docScore = round($docRatio * 30, 1);

        // 3. Financial Settlement Score (Max 25 pts)
        $fullyPaidCount = 0;
        $unpaidAmount = 0;
        foreach ($activeRegistrations as $reg) {
            $inv = $reg->invoice;
            if ($inv && $inv->status === 'lunas') {
                $fullyPaidCount++;
            }
            if ($inv) {
                $unpaidAmount += (float) $inv->remaining_amount;
            }
        }
        $paymentRatio = $totalRegistered > 0 ? ($fullyPaidCount / $totalRegistered) : 0;
        $paymentScore = round($paymentRatio * 25, 1);

        // 4. Equipment Distribution Score (Max 15 pts)
        $distributedCount = EquipmentDistribution::where('departure_id', $departure->id)
            ->where('status', 'diserahkan')
            ->count();
        $equipRatio = $totalRegistered > 0 ? min(1.0, $distributedCount / $totalRegistered) : 0;
        $equipScore = round($equipRatio * 15, 1);

        // 5. Checklist Items Score (Max 10 pts)
        $totalChecklists = $departure->checklists->count();
        $completedChecklists = $departure->checklists->where('is_completed', true)->count();
        $checklistRatio = $totalChecklists > 0 ? ($completedChecklists / $totalChecklists) : 0;
        $checklistScore = round($checklistRatio * 10, 1);

        // Overall Total Readiness Score (0 - 100%)
        $totalScore = round($quotaScore + $docScore + $paymentScore + $equipScore + $checklistScore, 1);

        // Grade & Status
        if ($totalScore >= 85) {
            $statusLabel = 'Siap Berangkat (Sangat Siap)';
            $badge = 'success';
        } elseif ($totalScore >= 65) {
            $statusLabel = 'Persiapan Sedang Berjalan';
            $badge = 'warning';
        } else {
            $statusLabel = 'Kesiapan Kritis (Butuh Tindakan Segera)';
            $badge = 'danger';
        }

        // Risks and Bottlenecks Identification
        $daysToDeparture = Carbon::today()->diffInDays(Carbon::parse($departure->departure_date), false);
        $risks = [];

        if ($missingDocsCount > 0 && $daysToDeparture <= 30) {
            $risks[] = "Terdapat {$missingDocsCount} jamaah belum memiliki berkas KTP & Paspor terverifikasi, sementara keberangkatan tersisa {$daysToDeparture} hari.";
        }

        if ($unpaidAmount > 0 && $daysToDeparture <= 14) {
            $formattedUnpaid = number_format($unpaidAmount, 0, ',', '.');
            $risks[] = "Terdapat sisa tagihan piutang sebesar Rp {$formattedUnpaid} yang belum dilunasi pada H-{$daysToDeparture} keberangkatan.";
        }

        if ($totalRegistered < ($quota * 0.5) && $daysToDeparture <= 45) {
            $risks[] = "Keterisian kursi masih di bawah 50% ({$totalRegistered}/{$quota}) menjelang 45 hari keberangkatan.";
        }

        if ($totalChecklists > 0 && $completedChecklists < ($totalChecklists * 0.7) && $daysToDeparture <= 10) {
            $risks[] = "Checklist logistik & manifest kloter baru terpenuhi {$completedChecklists}/{$totalChecklists} item.";
        }

        return [
            'departure_id' => $departure->id,
            'code' => $departure->code,
            'departure_date' => $departure->departure_date,
            'days_to_departure' => $daysToDeparture,
            'package_name' => $departure->package?->name ?? 'Paket Umrah',
            'total_registered' => $totalRegistered,
            'quota' => $quota,
            'readiness_score' => $totalScore,
            'status_label' => $statusLabel,
            'badge' => $badge,
            'breakdown' => [
                'quota' => ['score' => $quotaScore, 'max' => 20, 'ratio' => round($quotaRatio * 100)],
                'documents' => ['score' => $docScore, 'max' => 30, 'ratio' => round($docRatio * 100), 'missing' => $missingDocsCount],
                'payments' => ['score' => $paymentScore, 'max' => 25, 'ratio' => round($paymentRatio * 100), 'unpaid_amount' => $unpaidAmount],
                'equipment' => ['score' => $equipScore, 'max' => 15, 'ratio' => round($equipRatio * 100), 'distributed' => $distributedCount],
                'checklists' => ['score' => $checklistScore, 'max' => 10, 'ratio' => round($checklistRatio * 100), 'completed' => $completedChecklists, 'total' => $totalChecklists],
            ],
            'risks' => $risks,
        ];
    }

    /**
     * Get readiness scoreboard for all active upcoming departures.
     */
    public function getUpcomingDeparturesReadiness(): array
    {
        $departures = Departure::where('status', '!=', 'dibatalkan')
            ->orderBy('departure_date', 'asc')
            ->get();

        $scores = [];
        foreach ($departures as $dep) {
            $scores[] = $this->calculateReadinessScore($dep);
        }

        return $scores;
    }
}
