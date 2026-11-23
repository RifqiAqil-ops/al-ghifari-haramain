<?php

namespace App\Http\Controllers\Hotel;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\HotelCustomer;
use App\Models\HotelInvoice;
use App\Models\HotelPayment;
use App\Models\HotelReservation;
use App\Services\Hotel\ExchangeRateService;
use App\Services\Hotel\HotelFinancialService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HotelDashboardController extends Controller
{
    protected HotelFinancialService $financialService;
    protected ExchangeRateService $fxService;

    public function __construct(HotelFinancialService $financialService, ExchangeRateService $fxService)
    {
        $this->financialService = $financialService;
        $this->fxService = $fxService;
    }

    public function index(Request $request): View
    {
        $metrics = $this->financialService->getHotelFinancialSummary();
        $liveFx = $this->fxService->getLatestRate('SAR', 'IDR');

        $upcomingCheckIns = HotelReservation::with(['customer', 'items.hotel'])
            ->where('check_in_date', '>=', now()->toDateString())
            ->where('status', '!=', 'cancelled')
            ->orderBy('check_in_date', 'asc')
            ->take(5)
            ->get();

        $recentInvoices = HotelInvoice::with(['customer'])
            ->latest('invoice_date')
            ->take(5)
            ->get();

        $topHotels = Hotel::withCount('reservationItems')
            ->orderBy('reservation_items_count', 'desc')
            ->take(4)
            ->get();

        return view('hotel.dashboard', compact('metrics', 'liveFx', 'upcomingCheckIns', 'recentInvoices', 'topHotels'));
    }
}
