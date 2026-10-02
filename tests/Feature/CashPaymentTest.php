<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Metode pembayaran cash (tunai): pelanggan memilih bayar tunai, admin mengonfirmasi
 * pembayaran lewat tombol "Tandai Lunas (Bayar Cash)".
 * Skema DP 30% sudah dihapus (2 Okt 2026).
 */
class CashPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_choosing_cash_records_method_and_keeps_booking_unpaid(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $booking = $this->booking($customer);

        $this->actingAs($customer)
            ->get(route('payments.show', ['booking' => $booking, 'method' => 'cash']))
            ->assertOk()
            ->assertSee('Pembayaran Tunai di Kantor');

        $booking->refresh();
        $this->assertSame(Booking::PAYMENT_METHOD_CASH, $booking->payment_method);
        $this->assertSame(Booking::PAYMENT_UNPAID, $booking->payment_status);
        $this->assertSame((int) $booking->total_harga, (int) $booking->payment_amount);
        $this->assertNull($booking->payment_token);
    }

    public function test_admin_confirming_cash_marks_booking_paid_and_notifies_customer(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);
        $booking = $this->booking($customer, [
            'payment_method' => Booking::PAYMENT_METHOD_CASH,
            'payment_amount' => 300000,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.bookings.mark-paid', $booking))
            ->assertRedirect();

        $booking->refresh();
        $this->assertSame(Booking::PAYMENT_PAID, $booking->payment_status);
        $this->assertSame(Booking::PAYMENT_METHOD_CASH, $booking->payment_method);
        $this->assertSame(300000, (int) $booking->payment_amount);
        $this->assertNotNull($booking->payment_paid_at);

        $this->assertDatabaseHas('notification_logs', [
            'user_id' => $customer->id,
            'type' => 'payment_settled',
            'related_id' => $booking->id,
        ]);
    }

    public function test_admin_cannot_mark_paid_a_booking_that_is_already_paid(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);
        $booking = $this->booking($customer, [
            'payment_status' => Booking::PAYMENT_PAID,
            'payment_method' => Booking::PAYMENT_METHOD_MIDTRANS,
            'payment_paid_at' => now(),
        ]);

        $this->actingAs($admin)
            ->post(route('admin.bookings.mark-paid', $booking))
            ->assertRedirect();

        $booking->refresh();
        $this->assertSame(Booking::PAYMENT_METHOD_MIDTRANS, $booking->payment_method);
    }

    public function test_paid_cash_booking_can_have_ticket_generated(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);
        $vehicle = \App\Models\Vehicle::create([
            'mitra_id' => User::factory()->create(['role' => 'mitra'])->id,
            'nama' => 'Toyota Avanza',
            'plat_nomor' => 'EB 1111 AA',
            'jenis' => 'minibus',
            'kapasitas_penumpang' => 7,
            'status' => 'tersedia',
            'harga_sewa_tanpa_sopir_per_hari' => 400000,
            'harga_sewa_dengan_sopir_per_hari' => 500000,
            'is_approved' => true,
        ]);
        $booking = $this->booking($customer, [
            'vehicle_id' => $vehicle->id,
            'payment_status' => Booking::PAYMENT_PAID,
            'payment_method' => Booking::PAYMENT_METHOD_CASH,
            'payment_amount' => 300000,
            'payment_paid_at' => now(),
        ]);

        $this->actingAs($admin)
            ->post(route('admin.bookings.generate-ticket', $booking))
            ->assertRedirect();

        $booking->refresh();
        $this->assertSame(Booking::TICKET_CREATED, $booking->ticket_status);
        $this->assertNotNull($booking->ticket_number);
    }

    private function booking(User $customer, array $attributes = []): Booking
    {
        return Booking::create(array_merge([
            'pelanggan_id' => $customer->id,
            'service_type' => 'rental',
            'status' => Booking::STATUS_PENDING,
            'payment_status' => Booking::PAYMENT_UNPAID,
            'tanggal_mulai' => '2027-09-01',
            'tanggal_selesai' => '2027-09-02',
            'total_harga' => 300000,
        ], $attributes));
    }
}
