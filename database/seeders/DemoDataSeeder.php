<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Payout;
use App\Models\Review;
use App\Models\Route;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Data demo realistis untuk kebutuhan sidang/skripsi:
 * 10 booking (6 bulan terakhir), ulasan, dan payout.
 * Versi 2 Okt 2026: TIDAK lagi memakai id kendaraan hardcode — memilih dari armada
 * yang ada (rental/travel) sehingga aman setelah armada diganti (Fleet2026Seeder).
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        if (Booking::count() >= 5) {
            $this->command->info('Demo data sudah ada, lewati.');

            return;
        }

        $customer = User::where('email', 'customer@asrgo.test')->firstOrFail();
        $mitra = User::where('role', 'mitra')->firstOrFail();

        $rentalCars = Vehicle::where('layanan', 'rental')->orderBy('id')->get()->values();
        $travelCars = Vehicle::where('layanan', 'travel')->orderBy('id')->get()->values();
        $routes = Route::where('service_type', 'travel')->orderBy('id')->get()->values();

        if ($rentalCars->isEmpty() || $travelCars->isEmpty() || $routes->isEmpty()) {
            $this->command->error('Armada/rute belum ada — jalankan Fleet2026Seeder dulu.');

            return;
        }

        // [layanan, mulai, selesai, sesi, penumpang, with_driver, status]
        $rencana = [
            ['rental', '2026-03-05', '2026-03-07', null, null, true, 'completed'],
            ['travel', '2026-03-12', '2026-03-12', 'pagi', 4, null, 'completed'],
            ['rental', '2026-04-10', '2026-04-11', null, null, false, 'completed'],
            ['rental', '2026-05-15', '2026-05-18', null, null, true, 'completed'],
            ['travel', '2026-06-08', '2026-06-08', 'siang', 6, null, 'completed'],
            ['rental', '2026-07-08', '2026-07-10', null, null, true, 'completed'],
            ['travel', '2026-08-03', '2026-08-03', 'pagi', 3, null, 'completed'],
            ['rental', '2026-08-05', '2026-08-06', null, null, false, 'completed'],
            ['rental', '2026-08-19', '2026-08-20', null, null, true, 'pending'],
            ['travel', '2026-08-20', '2026-08-20', 'pagi', 5, null, 'sopir_assigned'],
        ];

        $reviews = [
            'Mobil nyaman dan bersih, sopir ramah. Pengalaman sewa terbaik.',
            'Perjalanan lancar dan tepat waktu. Sangat recommended.',
            'Proses cepat, mobil sesuai pesanan.',
            'Armada bagus, layanan profesional. Pasti pesan lagi.',
            'Perjalanan aman dan nyaman, harga terjangkau.',
            'Mobil terawat, sopir berpengalaman. Sangat puas.',
            'Booking mudah, konfirmasi cepat.',
            'AC dingin, sopir sopan, harga wajar.',
        ];
        $ratings = [5, 4, 5, 5, 4, 5, 4, 5];

        $iRental = 0;
        $iTravel = 0;

        foreach ($rencana as $i => [$serviceType, $mulai, $selesai, $session, $penumpang, $withDriver, $status]) {
            $hari = max(1, Carbon::parse($mulai)->diffInDays(Carbon::parse($selesai)));

            if ($serviceType === 'travel') {
                $vehicle = $travelCars[$iTravel++ % $travelCars->count()];
                $route = $routes[$i % $routes->count()];
                $harga = (int) $route->price;
            } else {
                $vehicle = $rentalCars[$iRental++ % $rentalCars->count()];
                $route = null;
                $harga = (int) ($withDriver
                    ? $vehicle->harga_sewa_dengan_sopir_per_hari * $hari
                    : $vehicle->harga_sewa_tanpa_sopir_per_hari * $hari);
            }

            $dibayar = $status !== 'pending';

            $data = [
                'pelanggan_id' => $customer->id,
                'vehicle_id' => $vehicle->id,
                'sopir_id' => ($serviceType === 'travel' || $withDriver) ? $vehicle->sopir_id : null,
                'route_id' => $route?->id,
                'origin' => $route?->origin,
                'destination' => $route?->destination,
                'service_type' => $serviceType,
                'session' => $session,
                'jumlah_penumpang' => $penumpang ?? 1,
                'tanggal_mulai' => $mulai,
                'tanggal_selesai' => $selesai,
                'status' => $status,
                'with_driver' => (bool) $withDriver,
                'total_harga' => $harga,
                'payment_status' => $dibayar ? Booking::PAYMENT_PAID : Booking::PAYMENT_PENDING,
                'payment_scheme' => Booking::PAYMENT_SCHEME_FULL,
                'payment_method' => $dibayar ? Booking::PAYMENT_METHOD_MIDTRANS : null,
                'payment_amount' => $dibayar ? $harga : null,
                'payment_paid_at' => $dibayar ? $mulai.' 09:00:00' : null,
                'refund_status' => Booking::REFUND_NONE,
                'contact_hp' => '081234567890',
                'created_at' => $mulai.' 09:00:00',
                'updated_at' => $mulai.' 09:00:00',
            ];

            $booking = Booking::create($data);

            if ($status === 'completed') {
                $booking->update([
                    'ticket_status' => Booking::TICKET_CREATED,
                    'ticket_number' => 'ASRGO-'.str_replace('-', '', $mulai).'-'.$booking->id,
                ]);

                Payout::create([
                    'mitra_id' => $mitra->id,
                    'booking_id' => $booking->id,
                    'jumlah_mitra' => (int) round($harga * 0.8),
                    'jumlah_platform' => (int) round($harga * 0.2),
                    'status_pencairan' => $i % 3 === 0 ? 'pending' : 'paid',
                ]);

                Review::create([
                    'booking_id' => $booking->id,
                    'customer_id' => $customer->id,
                    'rating' => $ratings[$i % count($ratings)],
                    'komentar' => $reviews[$i % count($reviews)],
                ]);
            }
        }

        $this->command->info('Demo data selesai: '.Booking::count().' booking, '.Review::count().' ulasan, '.Payout::count().' payout.');
    }
}
