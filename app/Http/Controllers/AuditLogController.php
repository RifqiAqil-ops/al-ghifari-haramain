<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $module = $request->input('module');
        $action = $request->input('action');

        $query = ActivityLog::with('user');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('user_name', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        if ($module) {
            $query->where('module', $module);
        }

        if ($action) {
            $query->where('action', $action);
        }

        $logs = $query->latest()->paginate(20)->withQueryString();

        $modules = ActivityLog::select('module')->distinct()->pluck('module');
        $actions = ActivityLog::select('action')->distinct()->pluck('action');

        return view('settings.audit-logs', compact('logs', 'modules', 'actions', 'search', 'module', 'action'));
    }

    public function show(ActivityLog $log): View
    {
        return view('settings.audit-log-detail', compact('log'));
    }
}
