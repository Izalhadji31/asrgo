<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentService $paymentService,
    ) {}

    public function show(Request $request, Booking $booking): View|Response
    {
        $this->ensureCustomerOwnsBooking($booking);

        if (in_array($booking->status, [Booking::STATUS_COMPLETED, Booking::STATUS_CANCELLED], true)) {
            return redirect()->route('bookings.index')->with('info', 'Booking ini sudah tidak dapat dibayar.');
        }

        if ($booking->payment_status === Booking::PAYMENT_PAID) {
            return redirect()->route('bookings.index')->with('info', 'Pembayaran booking ini sudah berhasil.');
        }

        $this->authorize('pay', $booking);

        $scheme = (string) $request->query('scheme', '');
        if (! in_array($scheme, [Booking::PAYMENT_SCHEME_DP, Booking::PAYMENT_SCHEME_FULL], true)) {
            $scheme = '';
        }

        $error = null;
        $snapToken = null;

        // Token masih valid — jangan buat transaksi baru, tampilkan token yang ada.
        $hasActiveToken = $booking->payment_status === Booking::PAYMENT_PENDING
            && $booking->payment_token
            && (! $booking->payment_expired_at || $booking->payment_expired_at->isFuture());

        if ($hasActiveToken) {
            $snapToken = $booking->payment_token;
        } elseif ($scheme !== '' || $booking->service_type === 'travel') {
            // Travel selalu lunas; rental bisa DP 30% atau lunas sesuai pilihan user.
            $useScheme = $scheme !== '' ? $scheme : Booking::PAYMENT_SCHEME_FULL;
            try {
                $booking = $this->paymentService->createSnapTransaction($booking, $useScheme);
                $snapToken = $booking->payment_token;
            } catch (Throwable $exception) {
                Log::error('Unable to prepare booking payment.', [
                    'booking_id' => $booking->id,
                    'exception' => $exception,
                ]);
                $error = $exception->getMessage();
            }
        }

        $booking = $booking->fresh();

        return view('payments.show', [
            'booking' => $booking,
            'snapToken' => $snapToken,
            'error' => $error,
            'scheme' => $booking?->payment_scheme ?? ($scheme !== '' ? $scheme : null),
        ]);
    }

    public function status(Booking $booking): JsonResponse
    {
        $this->ensureCustomerOwnsBooking($booking);

        $this->paymentService->syncTransactionStatus($booking);
        $booking->refresh();

        return response()->json([
            'payment_status' => $booking->payment_status,
            'ticket_status' => $booking->ticket_status,
        ]);
    }

    public function statuses(): JsonResponse
    {
        abort_unless(in_array(auth()->user()?->role, ['admin', 'customer'], true), 403);

        $bookings = Booking::query()
            ->whereNotNull('payment_order_id')
            ->where(function ($query) {
                $query->where('payment_status', Booking::PAYMENT_PENDING)
                    ->orWhere('refund_status', Booking::REFUND_PENDING);
            })
            ->when(
                auth()->user()->role === 'customer',
                fn ($query) => $query->where('pelanggan_id', auth()->id())
            )
            ->get();
        $changed = false;

        foreach ($bookings as $booking) {
            $status = [$booking->payment_status, $booking->refund_status];
            $updatedBooking = $this->paymentService->syncTransactionStatus($booking);
            $changed = $changed || $status !== [$updatedBooking->payment_status, $updatedBooking->refund_status];
        }

        return response()->json(['changed' => $changed]);
    }

    public function notification(Request $request): JsonResponse
    {
        try {
            $this->paymentService->handleNotification($request->all());
        } catch (Throwable $exception) {
            Log::warning('Invalid Midtrans notification received.', [
                'message' => $exception->getMessage(),
            ]);

            return response()->json(['message' => 'Invalid notification.'], 400);
        }

        return response()->json(['message' => 'Notification processed.']);
    }

    private function ensureCustomerOwnsBooking(Booking $booking): void
    {
        abort_unless(auth()->id() === $booking->pelanggan_id && auth()->user()?->role === 'customer', 403);
    }
}
