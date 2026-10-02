<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Bug #2 (Agu 2026): refund dulu selalu memakai total_harga, padahal untuk booking
 * DP 30% Midtrans hanya menerima nominal DP -> refund 100% tidak sah.
 * Sekarang nominal refund = payment_amount ?? total_harga.
 */
class RefundAmountTest extends TestCase
{
    use RefreshDatabase;

    public function test_refund_uses_down_payment_amount_for_dp_booking(): void
    {
        Http::fake([
            'https://api.midtrans.test/v2/ASRGO-DP-1/refund' => Http::response([
                'status_code' => '200',
                'refund_chargeback_id' => 'refund-dp-1',
            ], 200),
        ]);

        $booking = $this->refundableBooking([
            'total_harga' => 1000000,
            'payment_amount' => 300000,
            'payment_scheme' => Booking::PAYMENT_SCHEME_DP,
            'payment_order_id' => 'ASRGO-DP-1',
        ]);

        $this->actingAs($this->admin())->post(route('admin.bookings.refund.approve', $booking))
            ->assertRedirect();

        Http::assertSent(function ($request) {
            return str_ends_with($request->url(), '/ASRGO-DP-1/refund')
                && (int) $request['amount'] === 300000;
        });

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'refund_amount' => 300000,
            'refund_status' => Booking::REFUND_PENDING,
        ]);
    }

    public function test_refund_uses_full_amount_when_booking_was_paid_in_full(): void
    {
        Http::fake([
            'https://api.midtrans.test/v2/ASRGO-FULL-1/refund' => Http::response([
                'status_code' => '200',
                'refund_chargeback_id' => 'refund-full-1',
            ], 200),
        ]);

        $booking = $this->refundableBooking([
            'total_harga' => 1000000,
            'payment_amount' => 1000000,
            'payment_scheme' => Booking::PAYMENT_SCHEME_FULL,
            'payment_order_id' => 'ASRGO-FULL-1',
        ]);

        $this->actingAs($this->admin())->post(route('admin.bookings.refund.approve', $booking))
            ->assertRedirect();

        Http::assertSent(fn ($request) => (int) $request['amount'] === 1000000);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'refund_amount' => 1000000,
        ]);
    }

    public function test_refund_falls_back_to_total_price_for_legacy_booking_without_payment_amount(): void
    {
        Http::fake([
            'https://api.midtrans.test/v2/ASRGO-LEGACY-1/refund' => Http::response([
                'status_code' => '200',
                'refund_chargeback_id' => 'refund-legacy-1',
            ], 200),
        ]);

        $booking = $this->refundableBooking([
            'total_harga' => 750000,
            'payment_amount' => null,
            'payment_scheme' => null,
            'payment_order_id' => 'ASRGO-LEGACY-1',
        ]);

        $this->actingAs($this->admin())->post(route('admin.bookings.refund.approve', $booking))
            ->assertRedirect();

        Http::assertSent(fn ($request) => (int) $request['amount'] === 750000);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'refund_amount' => 750000,
        ]);
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function refundableBooking(array $attributes = []): Booking
    {
        config([
            'services.midtrans.server_key' => 'server-key',
            'services.midtrans.core_api_url' => 'https://api.midtrans.test',
        ]);

        $customer = User::factory()->create(['role' => 'customer']);

        return Booking::create(array_merge([
            'pelanggan_id' => $customer->id,
            'service_type' => 'rental',
            'status' => Booking::STATUS_PENDING,
            'payment_status' => Booking::PAYMENT_PAID,
            'payment_paid_at' => now(),
            'refund_status' => Booking::REFUND_REQUESTED,
            'refund_reason' => 'Pelanggan membatalkan perjalanan sebelum keberangkatan.',
            'tanggal_mulai' => '2027-03-01',
            'tanggal_selesai' => '2027-03-02',
            'total_harga' => 500000,
        ], $attributes));
    }
}
