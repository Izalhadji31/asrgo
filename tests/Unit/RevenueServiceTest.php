<?php

namespace Tests\Unit;

use App\Models\Booking;
use App\Models\RevenueShare;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\NotificationService;
use App\Services\RevenueService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RevenueServiceTest extends TestCase
{
    use RefreshDatabase;

    private RevenueService $revenueService;

    protected function setUp(): void
    {
        parent::setUp();

        $notificationService = new NotificationService;
        $this->revenueService = new RevenueService;
    }

    public function test_create_payout_uses_matching_revenue_share_for_mitra(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $mitra = User::factory()->create(['role' => 'mitra']);
        $driver = User::factory()->create(['role' => 'driver']);

        $vehicle = Vehicle::create([
            'mitra_id' => $mitra->id,
            'nama' => 'Test Car',
            'plat_nomor' => 'B 8888 XX',
            'jenis' => 'sedan',
            'status' => 'disewa',
            'harga_sewa_per_hari' => 500000,
            'is_approved' => true,
        ]);

        RevenueShare::create([
            'mitra_id' => $mitra->id,
            'persen_platform' => 15,
            'persen_mitra' => 85,
        ]);

        $booking = Booking::create([
            'pelanggan_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'sopir_id' => $driver->id,
            'tanggal_mulai' => '2027-01-01',
            'tanggal_selesai' => '2027-01-02',
            'status' => 'completed',
            'total_harga' => 1000000,
        ]);

        $payout = $this->revenueService->createPayout($booking, $mitra->id);

        $this->assertNotNull($payout);
        $this->assertSame(850000.0, (float) $payout->jumlah_mitra);
        $this->assertSame(150000.0, (float) $payout->jumlah_platform);
        $this->assertSame('pending', $payout->status_pencairan);
    }

    public function test_create_payout_falls_back_to_global_revenue_share(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $mitra = User::factory()->create(['role' => 'mitra']);

        $vehicle = Vehicle::create([
            'mitra_id' => $mitra->id,
            'nama' => 'Test Car',
            'plat_nomor' => 'B 9999 XX',
            'jenis' => 'suv',
            'status' => 'disewa',
            'harga_sewa_per_hari' => 400000,
            'is_approved' => true,
        ]);

        RevenueShare::create([
            'mitra_id' => null,
            'persen_platform' => 20,
            'persen_mitra' => 80,
        ]);

        $booking = Booking::create([
            'pelanggan_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'tanggal_mulai' => '2027-02-01',
            'tanggal_selesai' => '2027-02-03',
            'status' => 'completed',
            'total_harga' => 1200000,
        ]);

        $payout = $this->revenueService->createPayout($booking, $mitra->id);

        $this->assertNotNull($payout);
        $this->assertSame(960000.0, (float) $payout->jumlah_mitra);
        $this->assertSame(240000.0, (float) $payout->jumlah_platform);
    }

    public function test_create_payout_returns_null_when_no_revenue_share_exists(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $mitra = User::factory()->create(['role' => 'mitra']);

        $vehicle = Vehicle::create([
            'mitra_id' => $mitra->id,
            'nama' => 'Test Car',
            'plat_nomor' => 'B 0000 XX',
            'jenis' => 'suv',
            'status' => 'disewa',
            'harga_sewa_per_hari' => 400000,
            'is_approved' => true,
        ]);

        $booking = Booking::create([
            'pelanggan_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'tanggal_mulai' => '2027-03-01',
            'tanggal_selesai' => '2027-03-02',
            'status' => 'completed',
            'total_harga' => 800000,
        ]);

        $payout = $this->revenueService->createPayout($booking, $mitra->id);

        $this->assertNull($payout);
    }

    public function test_create_payout_is_idempotent_for_a_booking(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $mitra = User::factory()->create(['role' => 'mitra']);

        $vehicle = Vehicle::create([
            'mitra_id' => $mitra->id,
            'nama' => 'Test Car',
            'plat_nomor' => 'B 1212 XX',
            'jenis' => 'suv',
            'status' => 'disewa',
            'harga_sewa_per_hari' => 400000,
            'is_approved' => true,
        ]);

        RevenueShare::create([
            'mitra_id' => $mitra->id,
            'persen_platform' => 20,
            'persen_mitra' => 80,
        ]);

        $booking = Booking::create([
            'pelanggan_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'tanggal_mulai' => '2027-04-01',
            'tanggal_selesai' => '2027-04-01',
            'status' => 'completed',
            'total_harga' => 400000,
        ]);

        $first = $this->revenueService->createPayout($booking, $mitra->id);
        $second = $this->revenueService->createPayout($booking, $mitra->id);

        $this->assertSame($first->id, $second->id);
        $this->assertDatabaseCount('payouts', 1);
        $this->assertDatabaseCount('notification_logs', 1);
        $this->assertDatabaseHas('notification_logs', [
            'related_id' => $first->id,
        ]);
    }
}
