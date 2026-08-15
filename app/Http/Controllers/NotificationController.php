<?php

namespace App\Http\Controllers;

use App\Models\NotificationLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        $notifications = NotificationLog::where('user_id', Auth::id())
            ->latest()
            ->paginate(20)
            ->withQueryString();
        $layout = match (Auth::user()->role) {
            'admin' => 'layouts.admin',
            'mitra' => 'layouts.mitra',
            'driver' => 'layouts.driver',
            default => 'layouts.customer',
        };

        return view('notifications.index', compact('notifications', 'layout'));
    }

    public function read(NotificationLog $notification): RedirectResponse
    {
        $this->ensureOwner($notification);
        $notification->update(['read_at' => now()]);

        return back();
    }

    public function readAll(): RedirectResponse
    {
        NotificationLog::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back()->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }

    private function ensureOwner(NotificationLog $notification): void
    {
        abort_unless($notification->user_id === Auth::id(), 403);
    }
}
