<?php

namespace App\Http\Controllers\Intelligence;

use App\Http\Controllers\Controller;
use App\Models\SmartInsight;
use App\Services\Intelligence\AlertCenterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AlertCenterController extends Controller
{
    protected AlertCenterService $alertService;

    public function __construct(AlertCenterService $alertService)
    {
        $this->alertService = $alertService;
    }

    public function index(Request $request): View
    {
        $query = SmartInsight::with('resolver');

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->where('status', 'active');
        }

        $alerts = $query->orderByRaw("CASE WHEN level = 'critical' THEN 1 WHEN level = 'warning' THEN 2 ELSE 3 END")
            ->latest()
            ->paginate(20);

        $counts = [
            'total' => SmartInsight::count(),
            'active' => SmartInsight::where('status', 'active')->count(),
            'critical' => SmartInsight::where('status', 'active')->where('level', 'critical')->count(),
            'warning' => SmartInsight::where('status', 'active')->where('level', 'warning')->count(),
            'resolved' => SmartInsight::where('status', 'resolved')->count(),
        ];

        return view('intelligence.alerts', compact('alerts', 'counts'));
    }

    public function scan(): RedirectResponse
    {
        $result = $this->alertService->scanAndGenerateAlerts();

        return back()->with('toast_success', "Pemindaian selesai: {$result['new_alerts_count']} peringatan baru teridentifikasi.");
    }

    public function resolve(Request $request, SmartInsight $alert): RedirectResponse
    {
        $user = Auth::user();
        $this->alertService->resolveAlert($alert, $user, $request->input('resolution_note'));

        return back()->with('toast_success', "Peringatan '{$alert->title}' berhasil diselesaikan.");
    }

    public function dismiss(Request $request, SmartInsight $alert): RedirectResponse
    {
        $user = Auth::user();
        $this->alertService->dismissAlert($alert, $user, $request->input('resolution_note'));

        return back()->with('toast_info', "Peringatan '{$alert->title}' telah diabaikan.");
    }
}
