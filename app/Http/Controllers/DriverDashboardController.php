<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\RouteAssignment;
use App\Models\TravelDeparture;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class DriverDashboardController extends Controller
{
    public function index()
    {
        $upcomingTasks = Booking::where('sopir_id', Auth::id())
            ->whereDate('tanggal_selesai', '>=', today())
            ->with('vehicle')
            ->orderBy('tanggal_mulai')
            ->get();

        $todayTasks = $upcomingTasks->filter(fn ($booking) => $booking->tanggal_mulai->lte(today()));
        $assignedCount = $upcomingTasks->whereNotIn('status', ['completed', 'cancelled'])->count();
        $completedToday = Booking::where('sopir_id', Auth::id())
            ->where('status', 'completed')
            ->whereDate('tanggal_selesai', today())
            ->count();

        $completedThisMonth = Booking::where('sopir_id', Auth::id())
            ->where('status', 'completed')
            ->whereMonth('tanggal_selesai', now()->month)
            ->count();

        $departureAssignments = RouteAssignment::with(['route', 'mitra.vehicles' => function ($query) {
            $query->where('sopir_id', Auth::id())->where('is_approved', true);
        }, 'departures' => function ($query) {
            $query->whereDate('departure_date', today());
        }])
            ->whereHas('mitra.vehicles', fn ($query) => $query->where('sopir_id', Auth::id())->where('is_approved', true))
            ->whereHas('route', fn ($query) => $query->where('service_type', 'travel'))
            ->orderBy('priority')
            ->orderBy('id')
            ->get();

        $passengerCounts = $todayTasks
            ->filter(fn ($booking) => $booking->service_type === 'travel' && !in_array($booking->status, ['cancelled', 'completed'], true))
            ->groupBy(fn ($booking) => "{$booking->route_id}:{$booking->session}:{$booking->vehicle_id}")
            ->map(fn ($bookings) => $bookings->sum('jumlah_penumpang'));

        return view('driver.dashboard', compact(
            'todayTasks', 'assignedCount',
            'completedToday', 'completedThisMonth', 'departureAssignments', 'passengerCounts', 'upcomingTasks'
        ));
    }

    public function depart(RouteAssignment $routeAssignment, Vehicle $vehicle): RedirectResponse
    {
        $routeAssignment->load(['route', 'mitra']);

        if ($vehicle->sopir_id !== Auth::id() || $vehicle->mitra_id !== $routeAssignment->mitra_id) {
            abort(403);
        }

        if (!$routeAssignment->mitra || !$routeAssignment->route) {
            return back()->withErrors(['departure' => 'Data kendaraan atau rute tidak ditemukan.']);
        }

        $departure = TravelDeparture::firstOrCreate(
            [
                'route_assignment_id' => $routeAssignment->id,
                'vehicle_id' => $vehicle->id,
                'departure_date' => today()->toDateString(),
            ],
            [
                'route_id' => $routeAssignment->route_id,
                'vehicle_id' => $vehicle->id,
                'driver_id' => Auth::id(),
                'session' => $routeAssignment->session,
                'departed_at' => now(),
            ]
        );

        if (!$departure->wasRecentlyCreated) {
            return back()->with('info', 'Keberangkatan ini sudah ditandai sebelumnya.');
        }

        return back()->with('success', 'Kendaraan ditandai sudah berangkat. Booking berikutnya akan dialihkan ke kendaraan selanjutnya.');
    }
}
