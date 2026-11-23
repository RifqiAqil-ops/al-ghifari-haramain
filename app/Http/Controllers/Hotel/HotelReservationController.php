<?php

namespace App\Http\Controllers\Hotel;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\HotelCustomer;
use App\Models\HotelReservation;
use App\Models\HotelRoomType;
use App\Services\Hotel\ExchangeRateService;
use App\Services\Hotel\HotelInvoiceService;
use App\Services\Hotel\HotelReservationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HotelReservationController extends Controller
{
    protected HotelReservationService $reservationService;
    protected HotelInvoiceService $invoiceService;
    protected ExchangeRateService $fxService;

    public function __construct(
        HotelReservationService $reservationService,
        HotelInvoiceService $invoiceService,
        ExchangeRateService $fxService
    ) {
        $this->reservationService = $reservationService;
        $this->invoiceService = $invoiceService;
        $this->fxService = $fxService;
    }

    public function index(Request $request): View
    {
        $query = HotelReservation::with(['customer', 'items.hotel', 'items.roomType', 'invoice']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reservation_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', fn($c) => $c->where('name', 'like', "%{$search}%"));
            });
        }

        $reservations = $query->latest('booking_date')->paginate(10);

        return view('hotel.reservations.index', compact('reservations'));
    }

    public function create(): View
    {
        $customers = HotelCustomer::where('is_active', true)->orderBy('name')->get();
        $hotels = Hotel::with('roomTypes')->where('is_active', true)->orderBy('city')->orderBy('name')->get();
        $liveRate = $this->fxService->getLatestRate('SAR', 'IDR');

        return view('hotel.reservations.create', compact('customers', 'hotels', 'liveRate'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'customer_id' => 'required|exists:hotel_customers,id',
            'business_model' => 'required|in:reseller,commission,allotment',
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date|after:check_in_date',
            'items' => 'required|array|min:1',
            'items.*.hotel_id' => 'required|exists:hotels,id',
            'items.*.room_type_id' => 'required|exists:hotel_room_types,id',
            'items.*.room_quantity' => 'required|integer|min:1',
            'items.*.purchase_rate_per_night_sar' => 'nullable|numeric|min:0',
            'items.*.selling_rate_per_night_sar' => 'required|numeric|min:0',
        ]);

        $reservation = $this->reservationService->createReservation($request->all());

        // Auto-generate invoice if requested
        if ($request->boolean('generate_invoice', true)) {
            $this->invoiceService->createInvoiceFromReservation($reservation);
        }

        return redirect()->route('hotel.reservations.show', $reservation)
            ->with('toast_success', "Reservasi {$reservation->reservation_number} berhasil dibuat dan invoice telah diterbitkan.");
    }

    public function show(HotelReservation $reservation): View
    {
        $reservation->load(['customer', 'items.hotel', 'items.roomType', 'invoice.payments']);
        return view('hotel.reservations.show', compact('reservation'));
    }

    public function cancel(Request $request, HotelReservation $reservation): RedirectResponse
    {
        $request->validate(['cancellation_reason' => 'required|string|max:255']);
        $this->reservationService->cancelReservation($reservation, $request->cancellation_reason);

        return redirect()->route('hotel.reservations.show', $reservation)
            ->with('toast_info', 'Reservasi berhasil dibatalkan.');
    }
}
