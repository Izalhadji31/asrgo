<?php

namespace App\Services;

use App\Models\Booking;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class PaymentService
{
    public function __construct(
        private readonly NotificationService $notificationService,
    ) {}

    public function createSnapTransaction(Booking $booking, string $scheme = 'full'): Booking
    {
        if (in_array($booking->status, [Booking::STATUS_COMPLETED, Booking::STATUS_CANCELLED], true)) {
            throw new RuntimeException('Booking yang sudah selesai atau dibatalkan tidak dapat dibayar.');
        }

        if ($booking->payment_status === Booking::PAYMENT_PAID) {
            return $booking;
        }

        if ($booking->payment_status === Booking::PAYMENT_PENDING
            && $booking->payment_token
            && (! $booking->payment_expired_at || $booking->payment_expired_at->isFuture())) {
            return $booking;
        }

        if ($scheme !== Booking::PAYMENT_SCHEME_DP && $scheme !== Booking::PAYMENT_SCHEME_FULL) {
            $scheme = Booking::PAYMENT_SCHEME_FULL;
        }

        if ($booking->service_type === 'travel' && $scheme === Booking::PAYMENT_SCHEME_DP) {
            throw new RuntimeException('Travel wajib dibayar lunas di awal.');
        }

        $serverKey = (string) config('services.midtrans.server_key');
        if ($serverKey === '') {
            throw new RuntimeException('Midtrans server key belum dikonfigurasi.');
        }

        $booking->loadMissing('pelanggan');
        $orderId = 'ASRGO-'.$booking->id.'-'.Str::upper(Str::random(10));
        $customer = $booking->pelanggan;

        $isDownPayment = $scheme === Booking::PAYMENT_SCHEME_DP;
        $grossAmount = $isDownPayment
            ? (int) ceil($booking->total_harga * 0.30)
            : (int) $booking->total_harga;

        $transaction = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $grossAmount,
            ],
            'item_details' => [[
                'id' => 'booking-'.$booking->id,
                'price' => $grossAmount,
                'quantity' => 1,
                'name' => $isDownPayment
                    ? 'DP 30% Booking ASR GO #'.$booking->id
                    : 'Pembayaran Booking ASR GO #'.$booking->id,
            ]],
            'customer_details' => [
                'first_name' => $customer?->name ?? 'Customer',
                'email' => $customer?->email,
            ],
            'credit_card' => [
                'secure' => (bool) config('services.midtrans.is_3ds', true),
            ],
        ];
        $enabledPayments = config('services.midtrans.enabled_payments', []);
        if ($enabledPayments !== []) {
            $transaction['enabled_payments'] = $enabledPayments;
        }

        $response = Http::timeout(15)
            ->withBasicAuth($serverKey, '')
            ->acceptJson()
            ->post(config('services.midtrans.api_url'), $transaction);

        $token = $response->json('token');
        if ($response->failed() || ! is_string($token) || $token === '') {
            Log::error('Midtrans Snap transaction creation failed.', [
                'booking_id' => $booking->id,
                'status' => $response->status(),
                'response' => $response->json(),
            ]);

            $gatewayMessage = $response->json('error_messages.0');
            throw new RuntimeException(
                app()->isLocal() && is_string($gatewayMessage) && $gatewayMessage !== ''
                    ? $gatewayMessage
                    : 'Pembayaran belum dapat dibuat. Silakan coba lagi.'
            );
        }

        $booking->forceFill([
            'payment_status' => Booking::PAYMENT_PENDING,
            'payment_order_id' => $orderId,
            'payment_token' => $token,
            'payment_scheme' => $scheme,
            'payment_amount' => $grossAmount,
            'payment_expired_at' => now()->addDay(),
        ])->save();

        return $booking->refresh();
    }

    public function syncTransactionStatus(Booking $booking): Booking
    {
        $booking = $booking->fresh() ?? $booking;

        $syncPayment = $booking?->payment_status === Booking::PAYMENT_PENDING;
        $syncRefund = $booking?->refund_status === Booking::REFUND_PENDING;

        if (! $booking || ! $booking->payment_order_id || (! $syncPayment && ! $syncRefund)) {
            return $booking;
        }

        $serverKey = (string) config('services.midtrans.server_key');
        if ($serverKey === '') {
            return $booking;
        }

        $response = Http::timeout(10)
            ->withBasicAuth($serverKey, '')
            ->acceptJson()
            ->get(config('services.midtrans.core_api_url').'/v2/'.$booking->payment_order_id.'/status');

        if ($response->failed()) {
            Log::warning('Midtrans transaction status sync failed.', [
                'booking_id' => $booking->id,
                'status' => $response->status(),
            ]);

            return $booking;
        }

        $payload = $response->json();
        $expectedAmount = $booking->payment_amount ?? $booking->total_harga;
        if (! is_array($payload)
            || ($payload['order_id'] ?? null) !== $booking->payment_order_id
            || (int) round((float) ($payload['gross_amount'] ?? 0)) !== (int) $expectedAmount) {
            Log::warning('Midtrans transaction status response was invalid.', [
                'booking_id' => $booking->id,
            ]);

            return $booking;
        }

        return $this->applyPaymentStatus($booking, $payload);
    }

    public function handleNotification(array $payload): Booking
    {
        $orderId = (string) ($payload['order_id'] ?? '');
        $statusCode = (string) ($payload['status_code'] ?? '');
        $grossAmount = (string) ($payload['gross_amount'] ?? '');
        $signature = (string) ($payload['signature_key'] ?? '');
        $serverKey = (string) config('services.midtrans.server_key');

        if ($orderId === '' || $statusCode === '' || $grossAmount === '' || $signature === '' || $serverKey === '') {
            throw new RuntimeException('Payload notifikasi Midtrans tidak lengkap.');
        }

        $expectedSignature = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);
        if (! hash_equals($expectedSignature, $signature)) {
            throw new RuntimeException('Signature notifikasi Midtrans tidak valid.');
        }

        $booking = Booking::where('payment_order_id', $orderId)->firstOrFail();
        $expectedAmount = $booking->payment_amount ?? $booking->total_harga;
        if ((int) round((float) $grossAmount) !== (int) $expectedAmount) {
            throw new RuntimeException('Nominal pembayaran tidak sesuai booking.');
        }

        return $this->applyPaymentStatus($booking, $payload);
    }

    private function applyPaymentStatus(Booking $booking, array $payload): Booking
    {
        $transactionStatus = (string) ($payload['transaction_status'] ?? '');
        $fraudStatus = (string) ($payload['fraud_status'] ?? '');
        $isPaid = $transactionStatus === 'settlement'
            || ($transactionStatus === 'capture' && $fraudStatus === 'accept');

        $wasPaid = $booking->payment_status === Booking::PAYMENT_PAID;
        $paymentStatus = match (true) {
            $isPaid => Booking::PAYMENT_PAID,
            $transactionStatus === 'expire' => Booking::PAYMENT_EXPIRED,
            in_array($transactionStatus, ['cancel', 'deny', 'failure'], true) => Booking::PAYMENT_FAILED,
            default => Booking::PAYMENT_PENDING,
        };

        if (! $wasPaid || $isPaid) {
            $booking->forceFill([
                'payment_status' => $paymentStatus,
                'payment_transaction_id' => $payload['transaction_id'] ?? null,
                'payment_type' => $payload['payment_type'] ?? null,
                'payment_paid_at' => $isPaid ? ($booking->payment_paid_at ?? now()) : $booking->payment_paid_at,
                'payment_payload' => $payload,
            ])->save();
        }

        $this->applyRefundStatus($booking, $payload);

        if ($isPaid && ! $wasPaid) {
            $this->notificationService->log(
                $booking->pelanggan_id,
                'payment_paid',
                'Pembayaran booking Anda berhasil diterima. Booking menunggu proses tiket.',
                Booking::class,
                $booking->id
            );

            $paymentLabel = $booking->payment_scheme === Booking::PAYMENT_SCHEME_DP
                ? 'DP 30% (Rp '.number_format($booking->payment_amount ?? 0, 0, ',', '.').')'
                : 'lunas (Rp '.number_format($booking->payment_amount ?? $booking->total_harga, 0, ',', '.').')';

            foreach (\App\Models\User::where('role', 'admin')->pluck('id') as $adminId) {
                $this->notificationService->log(
                    $adminId,
                    'payment_paid_admin',
                    'Booking #'.$booking->id.' telah dibayar '.$paymentLabel.'. Sisa pelunasan dapat dikonfirmasi di panel admin.',
                    Booking::class,
                    $booking->id
                );
            }
        }

        return $booking->refresh();
    }

    private function applyRefundStatus(Booking $booking, array $payload): void
    {
        $refundStatus = match ((string) ($payload['refund_status'] ?? '')) {
            'pending' => Booking::REFUND_PENDING,
            'completed' => Booking::REFUND_COMPLETED,
            'failed' => Booking::REFUND_FAILED,
            default => ($payload['transaction_status'] ?? null) === 'refund'
                ? Booking::REFUND_COMPLETED
                : null,
        };

        if ($refundStatus === null) {
            return;
        }

        $booking->forceFill([
            'refund_status' => $refundStatus,
            'refund_id' => $payload['refund_chargeback_id'] ?? $booking->refund_id,
            'refund_amount' => isset($payload['refund_amount'])
                ? (int) round((float) $payload['refund_amount'])
                : $booking->refund_amount,
            'refunded_at' => $refundStatus === Booking::REFUND_COMPLETED
                ? ($booking->refunded_at ?? now())
                : $booking->refunded_at,
            'payment_payload' => array_merge($booking->payment_payload ?? [], ['refund_response' => $payload]),
        ])->save();
    }

    public function refund(Booking $booking): Booking
    {
        if (in_array($booking->status, [Booking::STATUS_COMPLETED, Booking::STATUS_CANCELLED], true)) {
            throw new RuntimeException('Booking yang sudah selesai atau dibatalkan tidak dapat direfund.');
        }

        if ($booking->payment_status !== Booking::PAYMENT_PAID) {
            throw new RuntimeException('Hanya booking lunas yang dapat direfund.');
        }

        if ($booking->refund_status === Booking::REFUND_COMPLETED
            || $booking->refund_status === Booking::REFUND_PENDING) {
            return $booking;
        }

        if (! $booking->payment_order_id) {
            throw new RuntimeException('Order ID Midtrans tidak tersedia untuk refund.');
        }

        $serverKey = (string) config('services.midtrans.server_key');
        if ($serverKey === '') {
            throw new RuntimeException('Midtrans server key belum dikonfigurasi.');
        }

        $refundKey = 'ASRGO-REFUND-'.$booking->id.'-'.Str::upper(Str::random(10));
        $response = Http::timeout(15)
            ->withBasicAuth($serverKey, '')
            ->acceptJson()
            ->post(config('services.midtrans.core_api_url').'/v2/'.$booking->payment_order_id.'/refund', [
                'refund_key' => $refundKey,
                'amount' => (int) $booking->total_harga,
                'reason' => 'Refund booking ASR GO #'.$booking->id,
            ]);

        if ($response->failed()) {
            $booking->update([
                'refund_status' => Booking::REFUND_FAILED,
                'payment_payload' => array_merge($booking->payment_payload ?? [], ['refund_response' => $response->json()]),
            ]);

            throw new RuntimeException('Refund Midtrans gagal diproses.');
        }

        $booking->forceFill([
            'refund_status' => Booking::REFUND_PENDING,
            'refund_id' => $response->json('refund_chargeback_id') ?: $refundKey,
            'refund_amount' => $booking->total_harga,
            'payment_payload' => array_merge($booking->payment_payload ?? [], ['refund_response' => $response->json()]),
        ])->save();

        $this->notificationService->log(
            $booking->pelanggan_id,
            'refund_requested',
            'Refund booking Anda sudah disetujui dan diajukan ke Midtrans.',
            Booking::class,
            $booking->id
        );

        return $booking->refresh();
    }
}
