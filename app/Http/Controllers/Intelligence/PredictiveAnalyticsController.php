<?php

namespace App\Http\Controllers\Intelligence;

use App\Http\Controllers\Controller;
use App\Models\Departure;
use App\Services\Intelligence\PredictiveAnalyticsService;
use Illuminate\View\View;

class PredictiveAnalyticsController extends Controller
{
    protected PredictiveAnalyticsService $predictiveService;

    public function __construct(PredictiveAnalyticsService $predictiveService)
    {
        $this->predictiveService = $predictiveService;
    }

    public function index(): View
    {
        $growthForecast = $this->predictiveService->predictJamaahGrowth();
        $equipmentNeeds = $this->predictiveService->predictEquipmentNeeds();
        $cashflowForecast = $this->predictiveService->predictCashFlow();

        // Forecast occupancy for upcoming departures
        $upcomingDepartures = Departure::where('status', '!=', 'dibatalkan')
            ->orderBy('departure_date', 'asc')
            ->take(8)
            ->get();

        $occupancyPredictions = [];
        foreach ($upcomingDepartures as $dep) {
            $occupancyPredictions[] = $this->predictiveService->predictDepartureOccupancy($dep);
        }

        return view('intelligence.predictive', compact(
            'growthForecast',
            'equipmentNeeds',
            'cashflowForecast',
            'occupancyPredictions'
        ));
    }
}
