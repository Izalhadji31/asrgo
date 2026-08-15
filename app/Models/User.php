<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function notifications()
    {
        return $this->hasMany(NotificationLog::class);
    }

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class, 'mitra_id');
    }

    public function bookingsAsPelanggan()
    {
        return $this->hasMany(Booking::class, 'pelanggan_id');
    }

    public function bookingsAsSopir()
    {
        return $this->hasMany(Booking::class, 'sopir_id');
    }

    public function revenueShares()
    {
        return $this->hasMany(RevenueShare::class, 'mitra_id');
    }

    public function payouts()
    {
        return $this->hasMany(Payout::class, 'mitra_id');
    }
}
