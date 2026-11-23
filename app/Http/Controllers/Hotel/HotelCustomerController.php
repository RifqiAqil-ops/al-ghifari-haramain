<?php

namespace App\Http\Controllers\Hotel;

use App\Http\Controllers\Controller;
use App\Models\HotelCustomer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HotelCustomerController extends Controller
{
    public function index(): View
    {
        $customers = HotelCustomer::withCount(['reservations', 'invoices'])->latest()->paginate(10);
        return view('hotel.customers.index', compact('customers'));
    }

    public function create(): View
    {
        return view('hotel.customers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'customer_type' => 'required|in:travel_agency,tour_operator,corporate,individual,partner',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'payment_terms_days' => 'required|integer|min:0|max:90',
        ]);

        $code = 'CUST-HTL-' . str_pad((HotelCustomer::count() + 1), 4, '0', STR_PAD_LEFT);

        $customer = HotelCustomer::create(array_merge($request->all(), ['customer_code' => $code]));

        return redirect()->route('hotel.customers.index')
            ->with('toast_success', "Pelanggan {$customer->name} ({$customer->company_name}) berhasil disimpan.");
    }

    public function show(HotelCustomer $customer): View
    {
        $customer->load(['reservations', 'invoices', 'payments']);
        return view('hotel.customers.show', compact('customer'));
    }
}
