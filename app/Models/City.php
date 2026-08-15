<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'initials',
        'latitude',
        'longitude',
        'is_popular',
        'image_url',
    ];

    protected $casts = [
        'is_popular' => 'boolean',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function routesAsOrigin()
    {
        return $this->hasMany(Route::class, 'origin_city_id');
    }

    public function routesAsDestination()
    {
        return $this->hasMany(Route::class, 'destination_city_id');
    }
}
