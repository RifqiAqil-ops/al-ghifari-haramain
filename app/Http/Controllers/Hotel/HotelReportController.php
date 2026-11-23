<?php

namespace App\Http\Controllers\Hotel;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\HotelCustomer;
use App\Models\HotelInvoice;
use App\Services\Hotel\HotelFinancialService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HotelReportController extends Controller
{
    protected HotelFinancialService $financialService;

    public function __construct(HotelFinancialService $financialService)
    {
        $this->financialService = $financialService;
    }

    public function index(Request $request): View
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->endOfMonth()->toDateString());

        $hotelMetrics = $this->financialService->getHotelFinancialSummary($startDate, $endDate);
        $consolidated = $this->financialService->getConsolidatedCompanyMetrics();

        $invoices = HotelInvoice::with(['customer', 'reservation'])
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->where('status', '!=', 'batal')
            ->get();

        return view('hotel.reports.index', compact('hotelMetrics', 'consolidated', 'invoices', 'startDate', 'endDate'));
    }
}
