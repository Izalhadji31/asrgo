<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use LogicException;

class Booking extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_ASSIGNED = 'sopir_assigned';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    public const TICKET_NOT_CREATED = 'not_created';

    public const TICKET_CREATED = 'created';

    public const PAYMENT_UNPAID = 'unpaid';

    public const PAYMENT_PENDING = 'pending';

    public const PAYMENT_PAID = 'paid';

    public const PAYMENT_FAILED = 'failed';

    public const PAYMENT_EXPIRED = 'expired';

    public const REFUND_NONE = 'none';

    public const REFUND_REQUESTED = 'requested';

    public const REFUND_REJECTED = 'rejected';

    public const REFUND_PENDING = 'pending';

    public const REFUND_COMPLETED = 'completed';

    public const REFUND_FAILED = 'failed';

    protected $fillable = [
        'pelanggan_id',
        'vehicle_id',
        'route_id',
        'sopir_id',
        'service_type',
        'session',
        'jumlah_penumpang',
        'tanggal_mulai',
        'tanggal_selesai',
        'origin',
        'destination',
        'flight_number',
        'notes',
        'status',
        'total_harga',
        'payment_status',
        'payment_order_id',
        'payment_token',
        'payment_transaction_id',
        'payment_type',
        'payment_paid_at',
        'payment_expired_at',
        'payment_payload',
        'refund_status',
        'refund_reason',
        'refund_rejection_reason',
        'refund_requested_at',
        'refund_reviewed_at',
        'refund_id',
        'refund_amount',
        'refunded_at',
        'ticket_number',
        'ticket_status',
        'with_driver',
    ];

    protected $casts = [
        'total_harga' => 'integer',
        'jumlah_penumpang' => 'integer',
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'with_driver' => 'boolean',
        'payment_paid_at' => 'datetime',
        'payment_expired_at' => 'datetime',
        'payment_payload' => 'array',
        'refund_amount' => 'integer',
        'refund_requested_at' => 'datetime',
        'refund_reviewed_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    public function pelanggan()
    {
        return $this->belongsTo(User::class, 'pelanggan_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function sopir()
    {
        return $this->belongsTo(User::class, 'sopir_id');
    }

    public function payout()
    {
        return $this->hasOne(Payout::class);
    }

    public function route()
    {
        return $this->belongsTo(Route::class);
    }

    public function ticketStatusLabel(): string
    {
        return match ($this->ticket_status) {
            self::TICKET_CREATED => 'Tiket Dibuat',
            default => 'Belum Dapat Tiket',
        };
    }

    public function canTransitionTo(string $nextStatus): bool
    {
        if ($this->status === $nextStatus) {
            return true;
        }

        return match ($this->status) {
            self::STATUS_PENDING, 'confirmed' => in_array($nextStatus, [self::STATUS_ASSIGNED, self::STATUS_CANCELLED], true),
            self::STATUS_ASSIGNED => in_array($nextStatus, [self::STATUS_COMPLETED, self::STATUS_CANCELLED], true),
            default => false,
        };
    }

    public function transitionTo(string $nextStatus): void
    {
        if (! $this->canTransitionTo($nextStatus)) {
            throw new LogicException("Booking #{$this->id} tidak dapat berpindah dari {$this->status} ke {$nextStatus}.");
        }

        $this->update(['status' => $nextStatus]);
    }
}
