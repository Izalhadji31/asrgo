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
        'harga_sewa_tanpa_sopir_per_hari',
        'harga_sewa_dengan_sopir_per_hari',
        'tarif_sopir_harian',
        'foto',
        'is_approved',
        'transmission',
        'capacity',
        'year',
        'vehicle_type',
        'fuel_type',
        'features',
        'brand',
    ];

    protected $casts = [
        'harga_sewa_tanpa_sopir_per_hari' => 'integer',
        'harga_sewa_dengan_sopir_per_hari' => 'integer',
        'tarif_sopir_harian' => 'integer',
        'kapasitas_penumpang' => 'integer',
        'prioritas_travel' => 'integer',
        'is_approved' => 'boolean',
        'capacity' => 'integer',
        'year' => 'integer',
        'features' => 'array',
    ];

    /**
     * Get harga berdasarkan opsi sopir
     */
    public function getHarga($withDriver = false)
    {
        return $withDriver ? $this->harga_sewa_dengan_sopir_per_hari : $this->harga_sewa_tanpa_sopir_per_hari;
    }

    /**
     * Format harga ke Rupiah
     */
    public function getFormattedHarga($withDriver = false)
    {
        return 'Rp ' . number_format($this->getHarga($withDriver), 0, ',', '.');
    }

    public function mitra()
    {
        return $this->belongsTo(User::class, 'mitra_id');
    }

    public function sopir()
    {
        return $this->belongsTo(User::class, 'sopir_id');
    }
}
