<?php

namespace App\Http\Controllers\Intelligence;

use App\Http\Controllers\Controller;
use App\Models\Departure;
use App\Models\Equipment;
use App\Models\Invoice;
use App\Models\Jamaah;
use App\Models\SmartInsight;
use App\Services\Intelligence\DepartureIntelligenceService;
use App\Services\Intelligence\FinancialIntelligenceService;
use App\Services\Intelligence\PredictiveAnalyticsService;
use Illuminate\View\View;

class IntelligenceDashboardController extends Controller
{
    public function index(
        FinancialIntelligenceService $financialService,
        DepartureIntelligenceService $departureService,
        PredictiveAnalyticsService $predictiveService
    ): View {
        $financialHealth = $financialService->getFinancialHealthIndicators();
        $upcomingReadiness = $departureService->getUpcomingDeparturesReadiness();
        $growthPrediction = $predictiveService->predictJamaahGrowth();
        $equipmentNeeds = $predictiveService->predictEquipmentNeeds();

        $activeAlerts = SmartInsight::where('status', 'active')
            ->orderByRaw("CASE WHEN level = 'critical' THEN 1 WHEN level = 'warning' THEN 2 ELSE 3 END")
            ->latest()
            ->take(6)
            ->get();

        $criticalCount = SmartInsight::where('status', 'active')->where('level', 'critical')->count();
        $warningCount = SmartInsight::where('status', 'active')->where('level', 'warning')->count();

        $totalActiveJamaah = Jamaah::where('status', 'aktif')->count();
        $upcomingDeparturesCount = Departure::where('departure_date', '>=', now())
            ->where('status', '!=', 'dibatalkan')
            ->count();

        // Overall Business Health Index (0 - 100)
        $readinessAvg = !empty($upcomingReadiness) ? collect($upcomingReadiness)->avg('readiness_score') : 80;
        $businessHealthScore = round(($financialHealth['health_score'] * 0.5) + ($readinessAvg * 0.5));

        return view('intelligence.dashboard', compact(
            'financialHealth',
            'upcomingReadiness',
            'growthPrediction',
            'equipmentNeeds',
            'activeAlerts',
            'criticalCount',
            'warningCount',
            'totalActiveJamaah',
            'upcomingDeparturesCount',
            'businessHealthScore'
        ));
    }
}
