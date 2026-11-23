<?php

namespace App\Http\Controllers\Hotel;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\HotelCustomer;
use App\Models\HotelRoomType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HotelMasterController extends Controller
{
    public function index(): View
    {
        $hotels = Hotel::withCount('roomTypes')->latest()->paginate(10);
        return view('hotel.master.index', compact('hotels'));
    }

    public function create(): View
    {
        return view('hotel.master.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'category' => 'required|string|max:50',
            'star_rating' => 'required|integer|min:1|max:5',
            'address' => 'nullable|string',
            'contact_person' => 'nullable|string|max:100',
            'telephone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
        ]);

        $code = 'HTL-' . strtoupper(substr($request->city, 0, 3)) . '-' . str_pad((Hotel::count() + 1), 3, '0', STR_PAD_LEFT);

        $hotel = Hotel::create(array_merge($request->all(), ['code' => $code]));

        return redirect()->route('hotel.master.show', $hotel)
            ->with('toast_success', "Hotel {$hotel->name} berhasil didaftarkan.");
    }

    public function show(Hotel $hotel): View
    {
        $hotel->load(['roomTypes', 'contracts']);
        return view('hotel.master.show', compact('hotel'));
    }

    public function storeRoomType(Request $request, Hotel $hotel): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'max_occupancy' => 'required|integer|min:1|max:10',
            'default_purchase_rate' => 'nullable|numeric|min:0',
            'default_selling_rate' => 'required|numeric|min:0',
        ]);

        $hotel->roomTypes()->create($request->all());

        return redirect()->route('hotel.master.show', $hotel)
            ->with('toast_success', "Tipe kamar {$request->name} berhasil ditambahkan ke {$hotel->name}.");
    }
}
