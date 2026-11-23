<?php

namespace App\Http\Controllers\Hotel;

use App\Http\Controllers\Controller;
use App\Models\HotelExchangeRate;
use App\Services\Hotel\ExchangeRateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HotelExchangeRateController extends Controller
{
    protected ExchangeRateService $fxService;

    public function __construct(ExchangeRateService $fxService)
    {
        $this->fxService = $fxService;
    }

    public function index(): View
    {
        $currentRate = $this->fxService->getLatestRate('SAR', 'IDR');
        $rateHistory = HotelExchangeRate::where('base_currency', 'SAR')
            ->where('target_currency', 'IDR')
            ->latest('fetched_at')
            ->paginate(15);

        return view('hotel.exchange-rates', compact('currentRate', 'rateHistory'));
    }

    public function refresh(): RedirectResponse
    {
        $this->fxService->forceRefreshRate('SAR', 'IDR');

        return redirect()->route('hotel.rates.index')
            ->with('toast_success', 'Kurs SAR ke IDR berhasil diperbarui secara langsung dari penyedia API.');
    }

    public function override(Request $request): RedirectResponse
    {
        $request->validate([
            'rate' => 'required|numeric|min:1000|max:10000',
            'override_reason' => 'required|string|min:5|max:255',
            'duration_hours' => 'nullable|integer|min:1|max:72',
        ]);

        $expiresAt = now()->addHours($request->input('duration_hours', 12));

        $this->fxService->manualOverride(
            (float) $request->rate,
            $request->override_reason,
            $expiresAt
        );

        return redirect()->route('hotel.rates.index')
            ->with('toast_success', "Kurs manual berhasil ditetapkan sebesar Rp " . number_format($request->rate, 2) . " dengan alasan audit.");
    }
}
