<?php

namespace App\Http\Controllers\Intelligence;

use App\Http\Controllers\Controller;
use App\Models\AutomationLog;
use App\Models\AutomationRule;
use App\Services\Intelligence\AutomationEngineService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AutomationEngineController extends Controller
{
    protected AutomationEngineService $automationService;

    public function __construct(AutomationEngineService $automationService)
    {
        $this->automationService = $automationService;
    }

    public function index(): View
    {
        $rules = AutomationRule::withCount('logs')->latest()->get();
        $recentLogs = AutomationLog::with(['rule', 'executor'])->latest()->paginate(20);

        return view('intelligence.automation', compact('rules', 'recentLogs'));
    }

    public function toggle(AutomationRule $rule): RedirectResponse
    {
        $rule->update(['is_active' => !$rule->is_active]);

        $status = $rule->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('toast_success', "Aturan otomasi '{$rule->name}' berhasil {$status}.");
    }

    public function runRule(AutomationRule $rule): RedirectResponse
    {
        $user = Auth::user();
        $result = $this->automationService->executeRule($rule, 'manual', $user);

        if ($result['status'] === 'success') {
            return back()->with('toast_success', "Aturan '{$rule->name}' berhasil dieksekusi secara manual.");
        }

        return back()->with('toast_warning', "Eksekusi selesai dengan status: {$result['status']}.");
    }
}
