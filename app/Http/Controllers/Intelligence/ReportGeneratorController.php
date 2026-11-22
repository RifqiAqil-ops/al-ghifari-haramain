<?php

namespace App\Http\Controllers\Intelligence;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use App\Services\Intelligence\ReportGeneratorService;
use Illuminate\View\View;

class ReportGeneratorController extends Controller
{
    protected ReportGeneratorService $reportService;

    public function __construct(ReportGeneratorService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function index(): View
    {
        $reportData = $this->reportService->generateExecutiveSummary();
        $setting = CompanySetting::first();

        return view('intelligence.reports', compact('reportData', 'setting'));
    }

    public function print(): View
    {
        $reportData = $this->reportService->generateExecutiveSummary();
        $setting = CompanySetting::first();

        return view('intelligence.report-print', compact('reportData', 'setting'));
    }
}
