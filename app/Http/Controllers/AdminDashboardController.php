<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\RevenueShare;
use App\Models\Vehicle;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $bookingsToday = Booking::whereDate('created_at', today())->count();
        $availableUnits = Vehicle::where('status', 'tersedia')->count();
        $maintenanceUnits = Vehicle::where('status', 'maintenance')->count();

        $totalMitra = \App\Models\User::where('role', 'mitra')->count();

        $monthlyRevenue = Booking::where('status', 'completed')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_harga');

        $allBookings = Booking::select('id', 'created_at', 'status', 'service_type', 'total_harga')->get();

        $monthSeries = collect(range(5, 0))->map(fn ($i) => now()->copy()->subMonths($i));

        $chartData = [
            'months' => $monthSeries->map(fn ($m) => $m->translatedFormat('M Y'))->values(),
            'bookingCounts' => $monthSeries->map(
                fn ($m) => $allBookings->filter(fn ($b) => $b->created_at->isSameMonth($m))->count()
            )->values(),
            'revenues' => $monthSeries->map(
                fn ($m) => $allBookings->filter(fn ($b) => $b->created_at->isSameMonth($m) && $b->status === 'completed')->sum('total_harga')
            )->values(),
            'services' => [
                'labels' => ['Rental', 'Travel'],
                'data' => [
                    $allBookings->where('service_type', 'rental')->count(),
                    $allBookings->where('service_type', 'travel')->count(),
                ],
            ],
            'status' => [
                'labels' => ['Menunggu', 'Sopir Ditugaskan', 'Selesai', 'Dibatalkan'],
                'data' => [
                    $allBookings->where('status', 'pending')->count(),
                    $allBookings->where('status', 'sopir_assigned')->count(),
                    $allBookings->where('status', 'completed')->count(),
                    $allBookings->where('status', 'cancelled')->count(),
                ],
            ],
        ];

        $recentBookings = Booking::with(['pelanggan', 'vehicle'])
            ->latest()
            ->take(4)
            ->get()
            ->map(function ($booking) {
                $status = match ($booking->status) {
                    'completed' => ['label' => 'Selesai', 'class' => 'bg-[#3F7D6C]'],
                    'cancelled' => ['label' => 'Dibatalkan', 'class' => 'bg-[#C1443C]'],
                    default => ['label' => 'Aktif', 'class' => 'bg-blue-900'],
                };

                $ticketStatus = match ($booking->ticket_status) {
                    'created' => ['label' => 'Tiket Dibuat', 'class' => 'bg-[#3F7D6C]'],
                    default => ['label' => 'Belum Dapat Tiket', 'class' => 'bg-[#E8A33D]'],
                };

                return [
                    'id' => $booking->id,
                    'status' => $status['label'],
                    'status_class' => $status['class'],
                    'ticket_status' => $ticketStatus['label'],
                    'ticket_status_class' => $ticketStatus['class'],
                    'plate' => $booking->vehicle?->plat_nomor ?? '-',
                    'customer' => $booking->pelanggan?->name ?? '-',
                    'unit' => $booking->vehicle?->nama ?? '-',
                    'action' => $booking->ticket_status === 'not_created' ? 'Kelola Tiket' : 'Detail',
                    'action_route' => route('admin.bookings.index'),
                ];
            });

        $revenueShare = RevenueShare::whereNull('mitra_id')->latest()->first();

        return view('admin.dashboard', compact(
            'bookingsToday',
            'availableUnits',
            'maintenanceUnits',
            'totalMitra',
            'monthlyRevenue',
            'recentBookings',
            'revenueShare',
            'chartData'
        ));
    }
}
