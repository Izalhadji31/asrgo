<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payout;
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

    public function payouts()
    {
        $mitraId = Auth::id();

        $payouts = Payout::with('booking.vehicle')
            ->where('mitra_id', $mitraId)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => (float) Payout::where('mitra_id', $mitraId)->sum('jumlah_mitra'),
            'pending' => (float) Payout::where('mitra_id', $mitraId)->where('status_pencairan', 'pending')->sum('jumlah_mitra'),
            'paid' => (float) Payout::where('mitra_id', $mitraId)->where('status_pencairan', 'paid')->sum('jumlah_mitra'),
            'count' => Payout::where('mitra_id', $mitraId)->count(),
        ];

        $unitStats = Vehicle::where('mitra_id', $mitraId)
            ->get()
            ->map(function ($vehicle) use ($mitraId) {
                $total = (float) Payout::where('mitra_id', $mitraId)
                    ->whereHas('booking', fn ($query) => $query->where('vehicle_id', $vehicle->id))
                    ->sum('jumlah_mitra');

                $bookings = (int) Booking::where('vehicle_id', $vehicle->id)
                    ->where('status', Booking::STATUS_COMPLETED)
                    ->count();

                return [
                    'nama' => $vehicle->nama,
                    'plat' => $vehicle->plat_nomor,
                    'status' => $vehicle->status,
                    'bookings' => $bookings,
                    'total' => $total,
                ];
            })
            ->sortByDesc('total')
            ->values();

        return view('mitra.payouts.index', compact('payouts', 'stats', 'unitStats'));
    }
}
