<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'mitra_id',
        'sopir_id',
        'nama',
        'plat_nomor',
        'jenis',
        'kapasitas_penumpang',
        'prioritas_travel',
        'status',
        'harga_sewa_per_hari',
        'foto',
        'is_approved',
    ];

    protected $casts = [
        'harga_sewa_per_hari' => 'integer',
        'kapasitas_penumpang' => 'integer',
        'prioritas_travel' => 'integer',
        'is_approved' => 'boolean',
    ];

    public function mitra()
    {
        return $this->belongsTo(User::class, 'mitra_id');
    }

    public function sopir()
    {
        return $this->belongsTo(User::class, 'sopir_id');
    }
}
