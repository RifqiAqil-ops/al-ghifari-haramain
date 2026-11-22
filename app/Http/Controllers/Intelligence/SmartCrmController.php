<?php

namespace App\Http\Controllers\Intelligence;

use App\Http\Controllers\Controller;
use App\Models\Jamaah;
use App\Services\Intelligence\SmartCrmService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SmartCrmController extends Controller
{
    protected SmartCrmService $crmService;

    public function __construct(SmartCrmService $crmService)
    {
        $this->crmService = $crmService;
    }

    public function index(Request $request): View
    {
        $followUpQueue = $this->crmService->generateFollowUpQueue();

        $jamaahsQuery = Jamaah::with(['registrations.package', 'registrations.departure']);

        if ($request->filled('search')) {
            $search = $request->search;
            $jamaahsQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('registration_number', 'like', "%{$search}%");
            });
        }

        $jamaahs = $jamaahsQuery->latest()->paginate(15);

        return view('intelligence.crm', compact('followUpQueue', 'jamaahs'));
    }

    public function profile(Jamaah $jamaah): View
    {
        $profile360 = $this->crmService->getJamaah360Profile($jamaah);

        return view('intelligence.crm-profile', compact('profile360', 'jamaah'));
    }

    public function sendFollowUp(Request $request): RedirectResponse
    {
        $request->validate([
            'jamaah_id' => 'required|exists:jamaahs,id',
            'phone' => 'required|string',
            'message' => 'required|string|max:1000',
        ]);

        $res = $this->crmService->sendWhatsAppFollowUp(
            (int) $request->jamaah_id,
            $request->phone,
            $request->message,
            'crm_followup'
        );

        return back()->with('toast_success', 'Pesan follow-up WhatsApp telah dicatat/dikirim.');
    }
}
