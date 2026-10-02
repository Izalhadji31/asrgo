<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vehicle;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * GAP B (Oct 2026): saat kendaraan+sopir otomatis ditugaskan di createBooking
 * (rental dengan sopir / travel auto-assign), sopir tadinya TIDAK dapat notifikasi.
 */
class DriverAssignmentNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_driver_is_notified_when_rental_vehicle_with_driver_is_booked(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $driver = User::factory()->create(['role' => 'driver']);
        $vehicle = $this->vehicle(['sopir_id' => $driver->id]);

        $booking = app(BookingService::class)->createBooking(
            customerId: $customer->id,
            vehicleId: $vehicle->id,
            tanggalMulai: '2027-07-01',
            tanggalSelesai: '2027-07-02',
            serviceType: 'rental',
            withDriver: true,
            durationDays: 1,
            passengerCount: 1,
            contactHp: '081234567890',
        );

        $this->assertSame($driver->id, $booking->fresh()->sopir_id);
        $this->assertDatabaseHas('notification_logs', [
            'user_id' => $driver->id,
            'type' => 'booking_assigned_driver',
            'related_id' => $booking->id,
        ]);
    }

    public function test_driver_is_not_notified_when_booked_without_driver(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $driver = User::factory()->create(['role' => 'driver']);
        $vehicle = $this->vehicle(['sopir_id' => $driver->id]);

        $booking = app(BookingService::class)->createBooking(
            customerId: $customer->id,
            vehicleId: $vehicle->id,
            tanggalMulai: '2027-08-01',
            tanggalSelesai: '2027-08-02',
            serviceType: 'rental',
            withDriver: false,
            durationDays: 1,
            passengerCount: 1,
            contactHp: '081234567890',
        );

        $this->assertNull($booking->fresh()->sopir_id);
        $this->assertDatabaseMissing('notification_logs', [
            'user_id' => $driver->id,
            'type' => 'booking_assigned_driver',
            'related_id' => $booking->id,
        ]);
    }

    private function vehicle(array $attributes = []): Vehicle
    {
        $mitra = User::factory()->create(['role' => 'mitra']);

        return Vehicle::create(array_merge([
            'mitra_id' => $mitra->id,
            'nama' => 'Toyota Avanza',
            'plat_nomor' => 'EB 9999 ZZ',
            'jenis' => 'minibus',
            'kapasitas_penumpang' => 7,
            'status' => 'tersedia',
            'harga_sewa_tanpa_sopir_per_hari' => 400000,
            'harga_sewa_dengan_sopir_per_hari' => 500000,
            'tarif_sopir_harian' => 100000,
            'is_approved' => true,
        ], $attributes));
    }
}
