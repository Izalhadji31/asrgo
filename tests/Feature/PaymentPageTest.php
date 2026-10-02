<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regresi Bug #1 (Agu 2026): PaymentController::show() punya return type View|Response
 * tapi mengembalikan RedirectResponse -> TypeError 500 saat booking sudah lunas/selesai/dibatalkan.
 */
class PaymentPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_page_redirects_when_booking_already_paid(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $booking = $this->booking($customer, [
            'payment_status' => Booking::PAYMENT_PAID,
        ]);

        $this->actingAs($customer)
            ->get(route('payments.show', $booking))
            ->assertRedirect(route('bookings.index'));
    }

    public function test_payment_page_redirects_when_booking_completed(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $booking = $this->booking($customer, [
            'status' => Booking::STATUS_COMPLETED,
            'payment_status' => Booking::PAYMENT_PAID,
        ]);

        $this->actingAs($customer)
            ->get(route('payments.show', $booking))
            ->assertRedirect(route('bookings.index'));
    }

    public function test_payment_page_redirects_when_booking_cancelled(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $booking = $this->booking($customer, [
            'status' => Booking::STATUS_CANCELLED,
            'payment_status' => Booking::PAYMENT_UNPAID,
        ]);

        $this->actingAs($customer)
            ->get(route('payments.show', $booking))
            ->assertRedirect(route('bookings.index'));
    }

    public function test_payment_page_is_forbidden_for_other_customer(): void
    {
        $owner = User::factory()->create(['role' => 'customer']);
        $other = User::factory()->create(['role' => 'customer']);
        $booking = $this->booking($owner);

        $this->actingAs($other)
            ->get(route('payments.show', $booking))
            ->assertForbidden();
    }

    public function test_unpaid_booking_payment_page_shows_payment_method_options(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $booking = $this->booking($customer, [
            'service_type' => 'rental',
            'total_harga' => 600000,
        ]);

        $this->actingAs($customer)
            ->get(route('payments.show', $booking))
            ->assertOk()
            ->assertSee('Bayar Online (Midtrans)')
            ->assertSee('Bayar Cash / Tunai');
    }

    private function booking(User $customer, array $attributes = []): Booking
    {
        return Booking::create(array_merge([
            'pelanggan_id' => $customer->id,
            'service_type' => 'rental',
            'status' => Booking::STATUS_PENDING,
            'payment_status' => Booking::PAYMENT_UNPAID,
            'tanggal_mulai' => '2027-02-01',
            'tanggal_selesai' => '2027-02-02',
            'total_harga' => 300000,
        ], $attributes));
    }
}
