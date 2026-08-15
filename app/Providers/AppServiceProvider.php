<?php

namespace App\Providers;

use App\Models\Booking;
use App\Models\Payout;
use App\Models\Vehicle;
use App\Policies\BookingPolicy;
use App\Policies\PayoutPolicy;
use App\Policies\VehiclePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Booking::class, BookingPolicy::class);
        Gate::policy(Payout::class, PayoutPolicy::class);
        Gate::policy(Vehicle::class, VehiclePolicy::class);
    }
}
