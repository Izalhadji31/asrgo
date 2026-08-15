<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Support\Facades\Auth;

class CustomerDashboardController extends Controller
{
    public function index()
    {
        $activeBookings = Booking::where('pelanggan_id', Auth::id())
            ->whereIn('status', ['pending', 'sopir_assigned'])
            ->with('vehicle')
            ->latest()
            ->get();

        $history = Booking::where('pelanggan_id', Auth::id())
            ->whereIn('status', ['completed', 'cancelled'])
            ->with('vehicle')
            ->latest()
            ->take(5)
            ->get();

        $totalBookings = Booking::where('pelanggan_id', Auth::id())->count();
        $completedCount = Booking::where('pelanggan_id', Auth::id())->where('status', 'completed')->count();
        $cancelledCount = Booking::where('pelanggan_id', Auth::id())->where('status', 'cancelled')->count();

        return view('customer.dashboard', compact(
            'activeBookings', 'history', 'totalBookings', 'completedCount', 'cancelledCount'
        ));
    }
}
