<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RefundRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_submit_a_refund_request_for_a_paid_booking(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $booking = $this->paidBooking($customer);

        $response = $this->actingAs($customer)->post(route('bookings.refund.request', $booking), [
            'refund_reason' => 'Ada perubahan jadwal perjalanan dari pelanggan.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'refund_status' => Booking::REFUND_REQUESTED,
            'refund_reason' => 'Ada perubahan jadwal perjalanan dari pelanggan.',
        ]);
    }

    public function test_customer_cannot_request_refund_for_another_customers_booking(): void
    {
        $owner = User::factory()->create(['role' => 'customer']);
        $customer = User::factory()->create(['role' => 'customer']);
        $booking = $this->paidBooking($owner);

        $this->actingAs($customer)
            ->post(route('bookings.refund.request', $booking), [
                'refund_reason' => 'Alasan refund yang cukup panjang.',
            ])
            ->assertForbidden();
    }

    public function test_admin_can_reject_a_refund_request(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);
        $booking = $this->paidBooking($customer, [
            'refund_status' => Booking::REFUND_REQUESTED,
            'refund_reason' => 'Ada perubahan jadwal perjalanan dari pelanggan.',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.bookings.refund.reject', $booking), [
            'refund_rejection_reason' => 'Booking sudah masuk jadwal operasional.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'refund_status' => Booking::REFUND_REJECTED,
            'refund_rejection_reason' => 'Booking sudah masuk jadwal operasional.',
        ]);
    }

    public function test_admin_approval_submits_refund_to_midtrans_and_cancels_booking(): void
    {
        config([
            'services.midtrans.server_key' => 'server-key',
            'services.midtrans.core_api_url' => 'https://api.midtrans.test',
        ]);
        Http::fake([
            'https://api.midtrans.test/v2/ASRGO-REFUND-1-REQUEST/refund' => Http::response([
                'status_code' => '200',
                'refund_chargeback_id' => 'refund-approval-123',
            ], 200),
        ]);

        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);
        $booking = $this->paidBooking($customer, [
            'payment_order_id' => 'ASRGO-REFUND-1-REQUEST',
            'refund_status' => Booking::REFUND_REQUESTED,
            'refund_reason' => 'Ada perubahan jadwal perjalanan dari pelanggan.',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.bookings.refund.approve', $booking));

        $response->assertRedirect();
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => Booking::STATUS_CANCELLED,
            'refund_status' => Booking::REFUND_PENDING,
            'refund_id' => 'refund-approval-123',
        ]);
    }

    private function paidBooking(User $customer, array $attributes = []): Booking
    {
        return Booking::create(array_merge([
            'pelanggan_id' => $customer->id,
            'payment_status' => Booking::PAYMENT_PAID,
            'tanggal_mulai' => '2027-01-01',
            'tanggal_selesai' => '2027-01-01',
            'status' => Booking::STATUS_PENDING,
            'total_harga' => 150000,
        ], $attributes));
    }
}
