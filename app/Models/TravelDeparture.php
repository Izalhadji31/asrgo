<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TravelDeparture extends Model
{
    protected $fillable = [
        'route_assignment_id',
        'route_id',
        'vehicle_id',
        'driver_id',
        'departure_date',
        'session',
        'departed_at',
    ];

    protected $casts = [
        'departure_date' => 'date',
        'departed_at' => 'datetime',
    ];

    public function assignment()
    {
        return $this->belongsTo(RouteAssignment::class, 'route_assignment_id');
    }
}
