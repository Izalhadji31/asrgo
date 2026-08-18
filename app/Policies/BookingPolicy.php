<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    public function view(User $user, Booking $booking): bool
    {
        return $user->role === 'admin'
            || ($user->role === 'customer' && $user->id === $booking->pelanggan_id)
            || ($user->role === 'driver' && $user->id === $booking->sopir_id);
    }

    public function assign(User $user, Booking $booking): bool
    {
        return $user->role === 'admin'
            && ! in_array($booking->status, [Booking::STATUS_COMPLETED, Booking::STATUS_CANCELLED], true);
    }

    public function pay(User $user, Booking $booking): bool
    {
        return $user->role === 'customer'
            && $user->id === $booking->pelanggan_id
            && ! in_array($booking->status, [Booking::STATUS_COMPLETED, Booking::STATUS_CANCELLED], true);
    }

    public function complete(User $user, Booking $booking): bool
    {
        return $user->role === 'driver'
            && $user->id === $booking->sopir_id;
    }

    public function cancel(User $user, Booking $booking): bool
    {
        return $user->role === 'customer'
            && $user->id === $booking->pelanggan_id;
    }

    public function refund(User $user, Booking $booking): bool
    {
        return $user->role === 'admin'
            && $booking->payment_status === Booking::PAYMENT_PAID
            && in_array($booking->refund_status, [Booking::REFUND_NONE, Booking::REFUND_FAILED], true)
            && ! in_array($booking->status, [Booking::STATUS_COMPLETED, Booking::STATUS_CANCELLED], true);
    }

    public function requestRefund(User $user, Booking $booking): bool
    {
        return $user->role === 'customer'
            && $user->id === $booking->pelanggan_id
            && $booking->payment_status === Booking::PAYMENT_PAID
            && $booking->refund_status === Booking::REFUND_NONE
            && ! in_array($booking->status, [Booking::STATUS_COMPLETED, Booking::STATUS_CANCELLED], true)
            && ! $booking->departed();
    }

    public function approveRefund(User $user, Booking $booking): bool
    {
        return $user->role === 'admin'
            && $booking->payment_status === Booking::PAYMENT_PAID
            && in_array($booking->refund_status, [Booking::REFUND_REQUESTED, Booking::REFUND_FAILED], true)
            && ! in_array($booking->status, [Booking::STATUS_COMPLETED, Booking::STATUS_CANCELLED], true);
    }

    public function rejectRefund(User $user, Booking $booking): bool
    {
        return $user->role === 'admin'
            && $booking->refund_status === Booking::REFUND_REQUESTED;
    }

    public function review(User $user, Booking $booking): bool
    {
        return $user->role === 'customer'
            && $user->id === $booking->pelanggan_id
            && $booking->status === Booking::STATUS_COMPLETED
            && ! $booking->review()->exists();
    }
}
