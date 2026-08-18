<?php

namespace Tests\Unit;

use App\Models\Booking;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\BookingService;
use App\Services\NotificationService;
use App\Services\RevenueService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingServiceTest extends TestCase
{
    use RefreshDatabase;

    private BookingService $bookingService;

    private NotificationService $notificationService;

    private RevenueService $revenueService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->notificationService = new NotificationService;
        $this->revenueService = new RevenueService;
        $this->bookingService = new BookingService($this->notificationService, $this->revenueService);
    }

    public function test_calculate_total_price_returns_correct_amount(): void
    {
        $vehicle = new Vehicle([
            'harga_sewa_tanpa_sopir_per_hari' => 200000, 'harga_sewa_dengan_sopir_per_hari' => 200000,
        ]);

        $total = $this->bookingService->calculateTotalPrice($vehicle, '2026-08-10', '2026-08-12', 'rental', null, 3);

        $this->assertSame(600000, $total);
    }

    public function test_calculate_total_price_for_single_day_returns_daily_rate(): void
    {
        $vehicle = new Vehicle([
            'harga_sewa_tanpa_sopir_per_hari' => 150000, 'harga_sewa_dengan_sopir_per_hari' => 150000,
        ]);

        $total = $this->bookingService->calculateTotalPrice($vehicle, '2026-08-10', '2026-08-10');

        $this->assertSame(150000, $total);
    }

    public function test_has_vehicle_conflict_detects_overlapping_booking(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $mitra = User::factory()->create(['role' => 'mitra']);
        $vehicle = Vehicle::create([
            'mitra_id' => $mitra->id,
            'nama' => 'Test Car',
            'plat_nomor' => 'B 1111 XX',
            'jenis' => 'sedan',
            'status' => 'tersedia',
            'harga_sewa_tanpa_sopir_per_hari' => 100000, 'harga_sewa_dengan_sopir_per_hari' => 100000,
            'is_approved' => true,
        ]);

        Booking::create([
            'pelanggan_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'tanggal_mulai' => '2026-09-10',
            'tanggal_selesai' => '2026-09-15',
            'status' => 'confirmed',
            'total_harga' => 500000,
        ]);

        $conflict = $this->bookingService->hasVehicleConflict($vehicle->id, '2026-09-12', '2026-09-14');

        $this->assertTrue($conflict);
    }

    public function test_has_vehicle_conflict_excludes_cancelled_bookings(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $mitra = User::factory()->create(['role' => 'mitra']);
        $vehicle = Vehicle::create([
            'mitra_id' => $mitra->id,
            'nama' => 'Test Car',
            'plat_nomor' => 'B 2222 XX',
            'jenis' => 'sedan',
            'status' => 'tersedia',
            'harga_sewa_tanpa_sopir_per_hari' => 100000, 'harga_sewa_dengan_sopir_per_hari' => 100000,
            'is_approved' => true,
        ]);

        Booking::create([
            'pelanggan_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'tanggal_mulai' => '2026-09-10',
            'tanggal_selesai' => '2026-09-15',
            'status' => 'cancelled',
            'total_harga' => 500000,
        ]);

        $conflict = $this->bookingService->hasVehicleConflict($vehicle->id, '2026-09-12', '2026-09-14');

        $this->assertFalse($conflict);
    }

    public function test_has_vehicle_conflict_returns_false_when_no_overlap(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $mitra = User::factory()->create(['role' => 'mitra']);
        $vehicle = Vehicle::create([
            'mitra_id' => $mitra->id,
            'nama' => 'Test Car',
            'plat_nomor' => 'B 3333 XX',
            'jenis' => 'sedan',
            'status' => 'tersedia',
            'harga_sewa_tanpa_sopir_per_hari' => 100000, 'harga_sewa_dengan_sopir_per_hari' => 100000,
            'is_approved' => true,
        ]);

        Booking::create([
            'pelanggan_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'tanggal_mulai' => '2026-09-01',
            'tanggal_selesai' => '2026-09-05',
            'status' => 'confirmed',
            'total_harga' => 400000,
        ]);

        $conflict = $this->bookingService->hasVehicleConflict($vehicle->id, '2026-09-10', '2026-09-15');

        $this->assertFalse($conflict);
    }

    public function test_has_driver_conflict_detects_overlapping_assignment(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $driver = User::factory()->create(['role' => 'driver']);
        $mitra = User::factory()->create(['role' => 'mitra']);
        $vehicle = Vehicle::create([
            'mitra_id' => $mitra->id,
            'nama' => 'Test Car',
            'plat_nomor' => 'B 4444 XX',
            'jenis' => 'sedan',
            'status' => 'tersedia',
            'harga_sewa_tanpa_sopir_per_hari' => 100000, 'harga_sewa_dengan_sopir_per_hari' => 100000,
            'is_approved' => true,
        ]);

        Booking::create([
            'pelanggan_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'sopir_id' => $driver->id,
            'tanggal_mulai' => '2026-10-01',
            'tanggal_selesai' => '2026-10-05',
            'status' => 'sopir_assigned',
            'total_harga' => 400000,
        ]);

        $conflict = $this->bookingService->hasDriverConflict($driver->id, '2026-10-03', '2026-10-07');

        $this->assertTrue($conflict);
    }

    public function test_has_driver_conflict_excludes_completed_bookings(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $driver = User::factory()->create(['role' => 'driver']);
        $mitra = User::factory()->create(['role' => 'mitra']);
        $vehicle = Vehicle::create([
            'mitra_id' => $mitra->id,
            'nama' => 'Test Car',
            'plat_nomor' => 'B 5555 XX',
            'jenis' => 'sedan',
            'status' => 'tersedia',
            'harga_sewa_tanpa_sopir_per_hari' => 100000, 'harga_sewa_dengan_sopir_per_hari' => 100000,
            'is_approved' => true,
        ]);

        Booking::create([
            'pelanggan_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'sopir_id' => $driver->id,
            'tanggal_mulai' => '2026-10-01',
            'tanggal_selesai' => '2026-10-05',
            'status' => 'completed',
            'total_harga' => 400000,
        ]);

        $conflict = $this->bookingService->hasDriverConflict($driver->id, '2026-10-03', '2026-10-07');

        $this->assertFalse($conflict);
    }

    public function test_create_booking_persists_booking_with_correct_status(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $mitra = User::factory()->create(['role' => 'mitra']);
        $vehicle = Vehicle::create([
            'mitra_id' => $mitra->id,
            'nama' => 'Test Car',
            'plat_nomor' => 'B 6666 XX',
            'jenis' => 'sedan',
            'status' => 'tersedia',
            'harga_sewa_tanpa_sopir_per_hari' => 200000, 'harga_sewa_dengan_sopir_per_hari' => 200000,
            'is_approved' => true,
        ]);

        $booking = $this->bookingService->createBooking($customer->id, $vehicle->id, '2026-11-01', '2026-11-03', durationDays: 3, withDriver: false);

        $this->assertInstanceOf(Booking::class, $booking);
        $this->assertSame('pending', $booking->status);
        $this->assertSame(Booking::TICKET_NOT_CREATED, $booking->ticket_status);
        $this->assertSame(600000, $booking->total_harga);
    }

    public function test_cancel_booking_updates_status_and_restores_vehicle(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $mitra = User::factory()->create(['role' => 'mitra']);
        $vehicle = Vehicle::create([
            'mitra_id' => $mitra->id,
            'nama' => 'Test Car',
            'plat_nomor' => 'B 7777 XX',
            'jenis' => 'sedan',
            'status' => 'disewa',
            'harga_sewa_tanpa_sopir_per_hari' => 100000, 'harga_sewa_dengan_sopir_per_hari' => 100000,
            'is_approved' => true,
        ]);

        $booking = Booking::create([
            'pelanggan_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'tanggal_mulai' => '2026-12-01',
            'tanggal_selesai' => '2026-12-03',
            'status' => 'pending',
            'total_harga' => 200000,
        ]);

        $this->bookingService->cancel($booking);

        $booking->refresh();
        $vehicle->refresh();

        $this->assertSame('cancelled', $booking->status);
        $this->assertSame('tersedia', $vehicle->status);
    }
}
