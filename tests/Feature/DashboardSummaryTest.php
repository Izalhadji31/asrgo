<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DashboardSummaryTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_shows_summary_cards(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $mitra = User::factory()->create(['role' => 'mitra']);

        Vehicle::create([
            'mitra_id' => $mitra->id,
            'nama' => 'Mobil Test',
            'plat_nomor' => 'ABC 1234',
            'jenis' => 'mobil',
            'status' => 'tersedia',
            'harga_sewa_tanpa_sopir_per_hari' => 150000, 'harga_sewa_dengan_sopir_per_hari' => 150000,
            'is_approved' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Booking Hari Ini');
        $response->assertSee('Total Mitra');
        $response->assertSee('Papan Booking');
    }

    public function test_admin_booking_board_filters_ticket_status_separately(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);

        Booking::create([
            'pelanggan_id' => $customer->id,
            'tanggal_mulai' => '2026-08-10',
            'tanggal_selesai' => '2026-08-10',
            'status' => 'pending',
            'ticket_status' => Booking::TICKET_NOT_CREATED,
            'total_harga' => 100000,
        ]);

        Booking::create([
            'pelanggan_id' => $customer->id,
            'tanggal_mulai' => '2026-08-11',
            'tanggal_selesai' => '2026-08-11',
            'status' => 'sopir_assigned',
            'ticket_number' => 'TKT-TEST-001',
            'ticket_status' => Booking::TICKET_CREATED,
            'total_harga' => 100000,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.bookings.index', [
            'ticket_status' => Booking::TICKET_NOT_CREATED,
        ]));

        $response->assertOk();
        $response->assertSee('Belum Dapat Tiket');
        $response->assertDontSee('TKT-TEST-001');
    }

    public function test_admin_payment_status_poll_syncs_pending_midtrans_booking(): void
    {
        config([
            'services.midtrans.server_key' => 'server-key',
            'services.midtrans.core_api_url' => 'https://api.midtrans.test',
        ]);
        Http::fake([
            'https://api.midtrans.test/v2/ASRGO-1-POLL/status' => Http::response([
                'order_id' => 'ASRGO-1-POLL',
                'status_code' => '200',
                'gross_amount' => '100000.00',
                'transaction_status' => 'settlement',
            ], 200),
        ]);

        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);
        Booking::create([
            'pelanggan_id' => $customer->id,
            'payment_order_id' => 'ASRGO-1-POLL',
            'payment_status' => Booking::PAYMENT_PENDING,
            'tanggal_mulai' => '2026-08-10',
            'tanggal_selesai' => '2026-08-10',
            'status' => Booking::STATUS_PENDING,
            'total_harga' => 100000,
        ]);

        $response = $this->actingAs($admin)->get(route('payments.statuses'));

        $response->assertOk()->assertJson(['changed' => true]);
        $this->assertDatabaseHas('bookings', ['payment_order_id' => 'ASRGO-1-POLL', 'payment_status' => Booking::PAYMENT_PAID]);
    }
}
