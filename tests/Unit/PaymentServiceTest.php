<?php

namespace Tests\Unit;

use App\Models\Booking;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_snap_transaction_for_the_full_booking_amount(): void
    {
        config([
            'services.midtrans.server_key' => 'server-key',
            'services.midtrans.api_url' => 'https://midtrans.test/snap/v1/transactions',
        ]);
        Http::fake([
            'https://midtrans.test/*' => Http::response(['token' => 'snap-token'], 201),
        ]);

        $customer = User::factory()->create(['role' => 'customer']);
        $booking = Booking::create([
            'pelanggan_id' => $customer->id,
            'tanggal_mulai' => '2027-01-01',
            'tanggal_selesai' => '2027-01-01',
            'status' => Booking::STATUS_PENDING,
            'total_harga' => 250000,
        ]);

        $booking = (new PaymentService(new NotificationService))->createSnapTransaction($booking);

        $this->assertSame(Booking::PAYMENT_PENDING, $booking->payment_status);
        $this->assertSame('snap-token', $booking->payment_token);
        $this->assertStringStartsWith('ASRGO-'.$booking->id.'-', $booking->payment_order_id);
        Http::assertSent(function (ClientRequest $request) use ($booking) {
            return $request->url() === 'https://midtrans.test/snap/v1/transactions'
                && $request['transaction_details']['gross_amount'] === 250000
                && $request['item_details'][0]['price'] === 250000
                && str_starts_with($request['transaction_details']['order_id'], 'ASRGO-'.$booking->id.'-');
        });
    }

    public function test_it_marks_a_booking_paid_after_a_valid_settlement_notification(): void
    {
        $serverKey = 'server-key';
        config(['services.midtrans.server_key' => $serverKey]);

        $customer = User::factory()->create(['role' => 'customer']);
        $booking = Booking::create([
            'pelanggan_id' => $customer->id,
            'payment_order_id' => 'ASRGO-1-TEST',
            'payment_status' => Booking::PAYMENT_PENDING,
            'tanggal_mulai' => '2027-01-01',
            'tanggal_selesai' => '2027-01-01',
            'status' => Booking::STATUS_PENDING,
            'total_harga' => 150000,
        ]);
        $payload = [
            'order_id' => $booking->payment_order_id,
            'status_code' => '200',
            'gross_amount' => '150000.00',
            'transaction_status' => 'settlement',
            'transaction_id' => 'trx-123',
            'payment_type' => 'bank_transfer',
        ];
        $payload['signature_key'] = hash('sha512', $payload['order_id'].$payload['status_code'].$payload['gross_amount'].$serverKey);

        $updated = (new PaymentService(new NotificationService))->handleNotification($payload);

        $this->assertSame(Booking::PAYMENT_PAID, $updated->payment_status);
        $this->assertSame('trx-123', $updated->payment_transaction_id);
        $this->assertNotNull($updated->payment_paid_at);
        $this->assertDatabaseHas('notification_logs', [
            'user_id' => $customer->id,
            'type' => 'payment_paid',
            'related_id' => $booking->id,
        ]);
    }

    public function test_it_syncs_a_pending_booking_from_midtrans_status(): void
    {
        config([
            'services.midtrans.server_key' => 'server-key',
            'services.midtrans.core_api_url' => 'https://api.midtrans.test',
        ]);
        Http::fake([
            'https://api.midtrans.test/v2/ASRGO-1-SYNC/status' => Http::response([
                'order_id' => 'ASRGO-1-SYNC',
                'status_code' => '200',
                'gross_amount' => '200000.00',
                'transaction_status' => 'settlement',
                'transaction_id' => 'trx-sync',
                'payment_type' => 'qris',
            ], 200),
        ]);

        $customer = User::factory()->create(['role' => 'customer']);
        $booking = Booking::create([
            'pelanggan_id' => $customer->id,
            'payment_order_id' => 'ASRGO-1-SYNC',
            'payment_status' => Booking::PAYMENT_PENDING,
            'tanggal_mulai' => '2027-01-01',
            'tanggal_selesai' => '2027-01-01',
            'status' => Booking::STATUS_PENDING,
            'total_harga' => 200000,
        ]);

        $updated = (new PaymentService(new NotificationService))->syncTransactionStatus($booking);

        $this->assertSame(Booking::PAYMENT_PAID, $updated->payment_status);
        $this->assertSame('trx-sync', $updated->payment_transaction_id);
        Http::assertSent(fn (ClientRequest $request) => $request->method() === 'GET'
            && $request->url() === 'https://api.midtrans.test/v2/ASRGO-1-SYNC/status');
    }

    public function test_it_syncs_a_pending_refund_from_midtrans_status(): void
    {
        config([
            'services.midtrans.server_key' => 'server-key',
            'services.midtrans.core_api_url' => 'https://api.midtrans.test',
        ]);
        Http::fake([
            'https://api.midtrans.test/v2/ASRGO-1-REFUND-SYNC/status' => Http::response([
                'order_id' => 'ASRGO-1-REFUND-SYNC',
                'status_code' => '200',
                'gross_amount' => '200000.00',
                'transaction_status' => 'refund',
                'refund_amount' => '200000.00',
            ], 200),
        ]);

        $customer = User::factory()->create(['role' => 'customer']);
        $booking = Booking::create([
            'pelanggan_id' => $customer->id,
            'payment_order_id' => 'ASRGO-1-REFUND-SYNC',
            'payment_status' => Booking::PAYMENT_PAID,
            'refund_status' => Booking::REFUND_PENDING,
            'tanggal_mulai' => '2027-01-01',
            'tanggal_selesai' => '2027-01-01',
            'status' => Booking::STATUS_CANCELLED,
            'total_harga' => 200000,
        ]);

        $updated = (new PaymentService(new NotificationService))->syncTransactionStatus($booking);

        $this->assertSame(Booking::REFUND_COMPLETED, $updated->refund_status);
        $this->assertSame(200000, $updated->refund_amount);
        $this->assertNotNull($updated->refunded_at);
    }

    public function test_it_requests_a_full_refund_for_a_paid_booking(): void
    {
        config([
            'services.midtrans.server_key' => 'server-key',
            'services.midtrans.core_api_url' => 'https://api.midtrans.test',
        ]);
        Http::fake([
            'https://api.midtrans.test/*' => Http::response([
                'status_code' => '200',
                'status_message' => 'Refund requested',
                'refund_chargeback_id' => 'refund-123',
            ], 200),
        ]);

        $customer = User::factory()->create(['role' => 'customer']);
        $booking = Booking::create([
            'pelanggan_id' => $customer->id,
            'payment_order_id' => 'ASRGO-1-REFUND',
            'payment_status' => Booking::PAYMENT_PAID,
            'tanggal_mulai' => '2027-01-01',
            'tanggal_selesai' => '2027-01-01',
            'status' => Booking::STATUS_PENDING,
            'total_harga' => 175000,
        ]);

        $booking = (new PaymentService(new NotificationService))->refund($booking);

        $this->assertSame(Booking::REFUND_PENDING, $booking->refund_status);
        $this->assertSame('refund-123', $booking->refund_id);
        $this->assertSame(175000, $booking->refund_amount);
        Http::assertSent(fn (ClientRequest $request) => $request->url() === 'https://api.midtrans.test/v2/ASRGO-1-REFUND/refund'
            && $request['amount'] === 175000);
    }
}
