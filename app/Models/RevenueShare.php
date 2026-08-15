<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RevenueShare extends Model
{
    protected $fillable = [
        'mitra_id',
        'persen_platform',
        'persen_mitra',
    ];

    protected $casts = [
        'persen_platform' => 'decimal:2',
        'persen_mitra' => 'decimal:2',
    ];

    public function mitra()
    {
        return $this->belongsTo(User::class, 'mitra_id');
    }
}
