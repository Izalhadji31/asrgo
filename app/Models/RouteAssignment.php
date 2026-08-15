<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RouteAssignment extends Model
{
    protected $fillable = ['route_id', 'mitra_id', 'session', 'priority', 'vehicle_id'];

    protected $casts = [
        'priority' => 'integer',
    ];

    public function route()
    {
        return $this->belongsTo(Route::class);
    }

    public function mitra()
    {
        return $this->belongsTo(User::class, 'mitra_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function departures()
    {
        return $this->hasMany(TravelDeparture::class);
    }
}
