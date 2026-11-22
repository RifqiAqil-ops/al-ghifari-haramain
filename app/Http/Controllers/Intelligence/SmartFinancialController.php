<?php

namespace App\Http\Controllers\Intelligence;

use App\Http\Controllers\Controller;
use App\Services\Intelligence\FinancialIntelligenceService;
use Illuminate\View\View;

class SmartFinancialController extends Controller
{
    protected FinancialIntelligenceService $financeService;

    public function __construct(FinancialIntelligenceService $financeService)
    {
        $this->financeService = $financeService;
    }

    public function index(): View
    {
        $indicators = $this->financeService->getFinancialHealthIndicators();
        $agingReceivables = $this->financeService->getAgingReceivables();
        $unusualTransactions = $this->financeService->detectUnusualTransactions();
        $duplicatePayments = $this->financeService->detectDuplicatePayments();

        return view('intelligence.financial', compact(
            'indicators',
            'agingReceivables',
            'unusualTransactions',
            'duplicatePayments'
        ));
    }
}
