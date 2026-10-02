<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Refund untuk pembayaran tunai (cash): diproses manual oleh admin di kantor,
 * TIDAK memanggil API Midtrans (booking cash tidak punya payment_order_id).
 */
class CashRefundTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_completes_cash_refund_without_hitting_midtrans(): void
    {
        Http::fake();
        Http::preventStrayRequests();

        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);
        $booking = $this->booking($customer, [
            'payment_method' => Booking::PAYMENT_METHOD_CASH,
            'payment_amount' => 500000,
            'payment_order_id' => null,
            'refund_status' => Booking::REFUND_REQUESTED,
            'refund_requested_at' => now(),
        ]);

        $this->actingAs($admin)
            ->post(route('admin.bookings.refund.manual', $booking))
            ->assertRedirect();

        $booking->refresh();
        $this->assertSame(Booking::REFUND_COMPLETED, $booking->refund_status);
        $this->assertSame(500000, (int) $booking->refund_amount);
        $this->assertNotNull($booking->refunded_at);
        $this->assertSame(Booking::STATUS_CANCELLED, $booking->status);

        $this->assertDatabaseHas('notification_logs', [
            'user_id' => $customer->id,
            'type' => 'refund_completed',
            'related_id' => $booking->id,
        ]);

        Http::assertNothingSent();
    }

    public function test_manual_refund_requires_pending_request(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);
        $booking = $this->booking($customer, [
            'payment_method' => Booking::PAYMENT_METHOD_CASH,
            'refund_status' => Booking::REFUND_NONE,
        ]);

        // Policy approveRefund menolak karena belum ada pengajuan refund dari pelanggan.
        $this->actingAs($admin)
            ->post(route('admin.bookings.refund.manual', $booking))
            ->assertForbidden();

        $this->assertSame(Booking::REFUND_NONE, $booking->fresh()->refund_status);
    }

    public function test_manual_refund_blocked_for_midtrans_payment(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);
        $booking = $this->booking($customer, [
            'payment_method' => Booking::PAYMENT_METHOD_MIDTRANS,
            'payment_order_id' => 'ASRGO-1-ABCDEFGHIJ',
            'refund_status' => Booking::REFUND_REQUESTED,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.bookings.refund.manual', $booking))
            ->assertSessionHasErrors('refund');

        $this->assertSame(Booking::REFUND_REQUESTED, $booking->fresh()->refund_status);
    }

    private function booking(User $customer, array $attributes = []): Booking
    {
        return Booking::create(array_merge([
            'pelanggan_id' => $customer->id,
            'service_type' => 'rental',
            'status' => Booking::STATUS_ASSIGNED,
            'payment_status' => Booking::PAYMENT_PAID,
            'payment_scheme' => Booking::PAYMENT_SCHEME_FULL,
            'payment_amount' => 500000,
            'payment_paid_at' => now(),
            'tanggal_mulai' => '2027-09-01',
            'tanggal_selesai' => '2027-09-02',
            'total_harga' => 500000,
        ], $attributes));
    }
}
