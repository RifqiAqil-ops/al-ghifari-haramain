<?php

namespace App\Services\Intelligence;

use App\Models\Departure;
use App\Models\Equipment;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Jamaah;
use App\Models\Registration;
use App\Services\Intelligence\DepartureIntelligenceService;
use App\Services\Intelligence\FinancialIntelligenceService;
use App\Services\Intelligence\PredictiveAnalyticsService;
use Carbon\Carbon;

class ReportGeneratorService
{
    protected FinancialIntelligenceService $financeService;
    protected DepartureIntelligenceService $departureService;
    protected PredictiveAnalyticsService $predictiveService;

    public function __construct(
        FinancialIntelligenceService $financeService,
        DepartureIntelligenceService $departureService,
        PredictiveAnalyticsService $predictiveService
    ) {
        $this->financeService = $financeService;
        $this->departureService = $departureService;
        $this->predictiveService = $predictiveService;
    }

    /**
     * Generate Comprehensive Executive Intelligence Summary.
     */
    public function generateExecutiveSummary(): array
    {
        $today = Carbon::today();
        $financialHealth = $this->financeService->getFinancialHealthIndicators();
        $agingReceivables = $this->financeService->getAgingReceivables();
        $upcomingReadiness = $this->departureService->getUpcomingDeparturesReadiness();
        $equipmentForecast = $this->predictiveService->predictEquipmentNeeds();
        $growthForecast = $this->predictiveService->predictJamaahGrowth();
        $cashflowForecast = $this->predictiveService->predictCashFlow();

        // Strategic Evidence-Based Recommendations
        $recommendations = [];

        if ($financialHealth['collection_rate'] < 80) {
            $recommendations[] = [
                'priority' => 'Tinggi',
                'category' => 'Keuangan & Penagihan',
                'title' => 'Percepat Pengumpulan Piutang Jatuh Tempo',
                'narrative' => 'Tingkat realisasi pembayaran saat ini berada di ' . $financialHealth['collection_rate'] . '%. Nilai piutang >30 hari sebesar Rp ' . number_format($agingReceivables['overdue_31_60_days'] + $agingReceivables['overdue_over_60_days'], 0, ',', '.') . ' perlu diintensifkan dengan automasi WhatsApp reminder.',
            ];
        }

        $lowReadinessKloter = collect($upcomingReadiness)->where('readiness_score', '<', 65)->count();
        if ($lowReadinessKloter > 0) {
            $recommendations[] = [
                'priority' => 'Kritis',
                'category' => 'Operasional Kloter',
                'title' => "Tindak Lanjut {$lowReadinessKloter} Kloter dengan Kesiapan Kritis",
                'narrative' => 'Ditemukan kloter keberangkatan dekat yang memiliki kendala dokumen paspor belum terverifikasi atau checklist logistik belum lengkap.',
            ];
        }

        $deficitItems = collect($equipmentForecast['items'])->where('deficit', '>', 0);
        if ($deficitItems->isNotEmpty()) {
            $recommendations[] = [
                'priority' => 'Tinggi',
                'category' => 'Logistik Perlengkapan',
                'title' => 'Pengadaan Restock Perlengkapan Segera',
                'narrative' => 'Proyeksi keberangkatan 60 hari ke depan menunjukkan potensi kekurangan stok untuk: ' . $deficitItems->pluck('name')->implode(', ') . '.',
            ];
        }

        if (empty($recommendations)) {
            $recommendations[] = [
                'priority' => 'Normal',
                'category' => 'Strategi Bisnis',
                'title' => 'Pertahankan Kinerja Operasional Prima',
                'narrative' => 'Seluruh parameter bisnis (keuangan, kesiapan kloter, dan logistik) dalam kondisi sehat dan terkendali.',
            ];
        }

        return [
            'generated_at' => now()->translatedFormat('d F Y H:i'),
            'financial_health' => $financialHealth,
            'aging_receivables' => $agingReceivables,
            'upcoming_departures_count' => count($upcomingReadiness),
            'upcoming_readiness' => $upcomingReadiness,
            'equipment_forecast' => $equipmentForecast,
            'growth_forecast' => $growthForecast,
            'cashflow_forecast' => $cashflowForecast,
            'recommendations' => $recommendations,
        ];
    }
}
