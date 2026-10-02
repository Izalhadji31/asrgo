<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(UserSeeder::class);
        $this->call(CitySeeder::class);
        $this->call(RevenueShareSeeder::class);
        $this->call(RouteSeeder::class);      // 2 rute: Ende <-> Mbay
        $this->call(Fleet2026Seeder::class);   // 9 unit riil + sopir 1:1 + assignment
        $this->call(DemoDataSeeder::class);    // booking/ulasan/payout demo
    }
}
