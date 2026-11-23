<?php

namespace App\Http\Controllers\Hotel;

use App\Http\Controllers\Controller;
use App\Models\HotelInvoice;
use App\Models\HotelPayment;
use App\Services\Hotel\ExchangeRateService;
use App\Services\Hotel\HotelInvoiceService;
use App\Services\Hotel\HotelPaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HotelInvoiceController extends Controller
{
    protected HotelInvoiceService $invoiceService;
    protected HotelPaymentService $paymentService;
    protected ExchangeRateService $fxService;

    public function __construct(
        HotelInvoiceService $invoiceService,
        HotelPaymentService $paymentService,
        ExchangeRateService $fxService
    ) {
        $this->invoiceService = $invoiceService;
        $this->paymentService = $paymentService;
        $this->fxService = $fxService;
    }

    public function index(Request $request): View
    {
        $query = HotelInvoice::with(['customer', 'reservation']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', fn($c) => $c->where('name', 'like', "%{$search}%"));
            });
        }

        $invoices = $query->latest('invoice_date')->paginate(10);

        return view('hotel.invoices.index', compact('invoices'));
    }

    public function show(HotelInvoice $invoice): View
    {
        $invoice->load(['customer', 'reservation.items.hotel', 'reservation.items.roomType', 'items', 'payments']);
        $liveRate = $this->fxService->getLatestRate('SAR', 'IDR');

        return view('hotel.invoices.show', compact('invoice', 'liveRate'));
    }

    public function print(HotelInvoice $invoice): View
    {
        $invoice->load(['customer', 'items', 'payments', 'reservation']);
        return view('hotel.invoices.print', compact('invoice'));
    }

    public function storePayment(Request $request, HotelInvoice $invoice): RedirectResponse
    {
        $request->validate([
            'payment_currency' => 'required|in:SAR,IDR',
            'payment_amount' => 'required|numeric|min:0.01',
            'payment_exchange_rate' => 'nullable|numeric|min:1000',
            'payment_date' => 'required|date',
            'payment_type' => 'required|in:dp,cicilan,pelunasan',
            'payment_method' => 'required|string',
            'bank_name' => 'nullable|string|max:100',
            'sender_name' => 'nullable|string|max:100',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:255',
        ]);

        $this->paymentService->recordPayment($invoice, $request->all());

        return redirect()->route('hotel.invoices.show', $invoice)
            ->with('toast_success', 'Pembayaran berhasil dicatat. Sisa tagihan dan selisih kurs telah diperbarui otomatis.');
    }
}
