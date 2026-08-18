<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payout;
use App\Models\RevenueShare;

class RevenueService
{
    public function createPayout(Booking $booking, int $mitraId): ?Payout
    {
        $revenueShare = RevenueShare::where('mitra_id', $mitraId)->latest()->first();

        if (! $revenueShare) {
            $revenueShare = RevenueShare::whereNull('mitra_id')->latest()->first();
        }

        if (! $revenueShare) {
            return null;
        }

        $jumlahMitra = $booking->total_harga * ($revenueShare->persen_mitra / 100);
        $jumlahPlatform = $booking->total_harga * ($revenueShare->persen_platform / 100);

        $payout = Payout::firstOrCreate(
            ['booking_id' => $booking->id],
            [
                'mitra_id' => $mitraId,
                'jumlah_mitra' => $jumlahMitra,
                'jumlah_platform' => $jumlahPlatform,
                'status_pencairan' => 'pending',
            ]
        );

        if (!$payout->wasRecentlyCreated) {
            return $payout;
        }

        app(NotificationService::class)->log(
            $mitraId,
            'payout_created',
            'Payout #'.$payout->id.' sebesar Rp '.number_format((float) $payout->jumlah_mitra, 0, ',', '.').' untuk booking #'.$booking->id.' telah dibuat.',
            Payout::class,
            $payout->id
        );

        return $payout;
    }
}
