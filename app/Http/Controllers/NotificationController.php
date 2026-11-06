<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        $notifications = Notification::latest()->paginate(15);
        return view('settings.notifications', compact('notifications'));
    }

    public function markAsRead(Notification $notification): RedirectResponse
    {
        $notification->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        if ($notification->link) {
            return redirect($notification->link);
        }

        return back();
    }

    public function markAllAsRead(): RedirectResponse
    {
        Notification::where('is_read', false)->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return back()->with('toast_success', 'Semua notifikasi telah ditandai dibaca.');
    }
}
