<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Payout;
use App\Models\Review;
use App\Models\Route;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    /**
     * Data demo realistis untuk kebutuhan sidang/skripsi:
     * 10 booking (6 bulan terakhir), ulasan, dan payout.
     * Idempotent: dilewati kalau sudah ada >= 5 booking.
     */
    public function run(): void
    {
        if (Booking::count() >= 5) {
            $this->command->info('Demo data sudah ada, lewati.');

            return;
        }

        $customer = User::where('email', 'customer@asrgo.test')->firstOrFail();
        $mitra = User::where('role', 'mitra')->firstOrFail();

        $vehicles = Vehicle::where('is_approved', true)->get()->keyBy('id');
        $routes = Route::where('service_type', 'travel')->get()->keyBy('id');

        // [service, vehicle|route id, mulai, selesai, session, penumpang, with_driver, total, status]
        $bookings = [
            ['rental', 'vehicle', 21, '2026-03-05', '2026-03-07', null, null, true, 1300000, 'completed'],
            ['travel', 'route', 21, '2026-03-12', '2026-03-12', 'pagi', 4, null, 200000, 'completed'],
            ['rental', 'vehicle', 19, '2026-04-10', '2026-04-11', null, null, false, 350000, 'completed'],
            ['rental', 'vehicle', 23, '2026-05-15', '2026-05-18', null, null, true, 2850000, 'completed'],
            ['travel', 'route', 27, '2026-06-08', '2026-06-08', 'siang', 6, null, 150000, 'completed'],
            ['rental', 'vehicle', 25, '2026-07-08', '2026-07-10', null, null, true, 2700000, 'completed'],
            ['travel', 'route', 24, '2026-08-03', '2026-08-03', 'pagi', 3, null, 100000, 'completed'],
            ['rental', 'vehicle', 22, '2026-08-05', '2026-08-06', null, null, false, 450000, 'completed'],
            ['rental', 'vehicle', 32, '2026-08-19', '2026-08-20', null, null, true, 600000, 'pending'],
            ['travel', 'route', 23, '2026-08-20', '2026-08-20', 'pagi', 5, null, 100000, 'sopir_assigned'],
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

        foreach ($bookings as $i => [$serviceType, $kind, $refId, $mulai, $selesai, $session, $penumpang, $withDriver, $harga, $status]) {
            $data = [
                'pelanggan_id' => $customer->id,
                'service_type' => $serviceType,
                'tanggal_mulai' => $mulai,
                'tanggal_selesai' => $selesai,
                'status' => $status,
                'total_harga' => $harga,
                'payment_status' => $status === 'pending' ? Booking::PAYMENT_PENDING : Booking::PAYMENT_PAID,
                'payment_scheme' => Booking::PAYMENT_SCHEME_FULL,
                'payment_amount' => $harga,
                'payment_paid_at' => $status === 'pending' ? null : $mulai.' 09:00:00',
                'refund_status' => Booking::REFUND_NONE,
                'created_at' => $mulai.' 09:00:00',
                'updated_at' => $mulai.' 09:00:00',
            ];

            if ($serviceType === 'travel') {
                $route = $routes->get($refId);
                $travelVehicle = $vehicles->get(19 + (($i * 3) % 16));
                $data['route_id'] = $route->id;
                $data['origin'] = $route->origin;
                $data['destination'] = $route->destination;
                $data['session'] = $session;
                $data['jumlah_penumpang'] = $penumpang;
                $data['vehicle_id'] = $travelVehicle->id;
                $data['sopir_id'] = $travelVehicle->sopir_id;
            } else {
                $vehicle = $vehicles->get($refId);
                $data['vehicle_id'] = $vehicle->id;
                if ($withDriver) {
                    $data['sopir_id'] = $vehicle->sopir_id;
                }
            }

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
