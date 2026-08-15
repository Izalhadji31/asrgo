<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Auth;

class MitraDashboardController extends Controller
{
    public function index()
    {
        $totalUnits = Vehicle::where('mitra_id', Auth::id())->count();
        $activeUnits = Vehicle::where('mitra_id', Auth::id())->where('status', 'tersedia')->count();
        $rentedUnits = Vehicle::where('mitra_id', Auth::id())->where('status', 'disewa')->count();
        $maintenanceUnits = Vehicle::where('mitra_id', Auth::id())->where('status', 'maintenance')->count();
        $pendingUnits = Vehicle::where('mitra_id', Auth::id())->where('is_approved', false)->count();

        $monthlyIncome = Booking::whereHas('vehicle', function ($query) {
            $query->where('mitra_id', Auth::id());
        })->where('status', 'completed')->whereMonth('created_at', now()->month)->sum('total_harga');

        $recentBookings = Booking::whereHas('vehicle', function ($query) {
            $query->where('mitra_id', Auth::id());
        })->with('vehicle')->latest()->take(5)->get();

        return view('mitra.dashboard', compact(
            'totalUnits', 'activeUnits', 'rentedUnits', 'maintenanceUnits',
            'pendingUnits', 'monthlyIncome', 'recentBookings'
        ));
    }
}
