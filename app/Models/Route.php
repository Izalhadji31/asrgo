<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Route extends Model
{
    protected $fillable = ['origin', 'destination', 'service_type', 'price'];

    protected $casts = [
        'price' => 'integer',
    ];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function assignments()
    {
        return $this->hasMany(RouteAssignment::class)->orderBy('priority')->orderBy('id');
    }
}
