<?php

namespace App\Http\Controllers;

use App\Models\Payout;
use App\Models\RevenueShare;
use Illuminate\Support\Facades\Auth;

class FinancialReportController extends Controller
{
    public function index()
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $payoutsQuery = Payout::with(['mitra', 'booking.vehicle', 'booking.pelanggan'])
            ->latest();
        $payouts = (clone $payoutsQuery)->get();

        $monthlyPayouts = $payouts->filter(function ($payout) {
            return optional($payout->created_at)->isCurrentMonth();
        });

        $platformRevenue = (float) $payouts->sum('jumlah_platform');
        $mitraRevenue = (float) $payouts->sum('jumlah_mitra');
        $grossRevenue = $platformRevenue + $mitraRevenue;

        $monthlyPlatformRevenue = (float) $monthlyPayouts->sum('jumlah_platform');
        $monthlyMitraRevenue = (float) $monthlyPayouts->sum('jumlah_mitra');
        $monthlyGrossRevenue = $monthlyPlatformRevenue + $monthlyMitraRevenue;

        $pendingPayouts = $payouts->where('status_pencairan', 'pending');
        $paidPayouts = $payouts->where('status_pencairan', 'paid');

        $globalShare = RevenueShare::whereNull('mitra_id')->latest()->first();

        $stats = [
            'gross' => $grossRevenue,
            'platform' => $platformRevenue,
            'mitra' => $mitraRevenue,
            'pending' => $pendingPayouts->sum('jumlah_mitra'),
            'paid' => $paidPayouts->sum('jumlah_mitra'),
            'monthly_gross' => $monthlyGrossRevenue,
            'monthly_platform' => $monthlyPlatformRevenue,
            'monthly_mitra' => $monthlyMitraRevenue,
        ];

        $statusBreakdown = [
            'pending' => $pendingPayouts->count(),
            'paid' => $paidPayouts->count(),
        ];

        $recentPayouts = (clone $payoutsQuery)->paginate(15)->withQueryString();

        return view('admin.reports.index', compact(
            'recentPayouts',
            'statusBreakdown',
            'globalShare',
            'stats'
        ));
    }

    public function markPaid(Payout $payout)
    {
        $this->authorize('update', $payout);

        $payout->update(['status_pencairan' => 'paid']);

        return redirect()->route('admin.reports.index')->with('success', 'Payout #'.$payout->id.' berhasil ditandai sebagai lunas.');
    }
}
