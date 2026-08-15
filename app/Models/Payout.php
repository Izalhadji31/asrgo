<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payout extends Model
{
    protected $fillable = [
        'mitra_id',
        'booking_id',
        'jumlah_mitra',
        'jumlah_platform',
        'status_pencairan',
    ];

    protected $casts = [
        'jumlah_mitra' => 'decimal:2',
        'jumlah_platform' => 'decimal:2',
    ];

    public function mitra()
    {
        return $this->belongsTo(User::class, 'mitra_id');
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
