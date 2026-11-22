<?php

namespace App\Http\Controllers\Intelligence;

use App\Http\Controllers\Controller;
use App\Models\Departure;
use App\Services\Intelligence\DepartureIntelligenceService;
use Illuminate\View\View;

class DepartureIntelligenceController extends Controller
{
    protected DepartureIntelligenceService $departureService;

    public function __construct(DepartureIntelligenceService $departureService)
    {
        $this->departureService = $departureService;
    }

    public function index(): View
    {
        $scoreboard = $this->departureService->getUpcomingDeparturesReadiness();

        return view('intelligence.departures', compact('scoreboard'));
    }

    public function show(Departure $departure): View
    {
        $readiness = $this->departureService->calculateReadinessScore($departure);

        return view('intelligence.departure-detail', compact('departure', 'readiness'));
    }
}
