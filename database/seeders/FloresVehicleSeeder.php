<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * DEPRECATED (2 Okt 2026) — armada dummy 16 unit sudah diganti data riil.
 * Sekarang meneruskan ke Fleet2026Seeder supaya `db:seed --class=FloresVehicleSeeder`
 * tidak menghidupkan lagi unit lama.
 */
class FloresVehicleSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->warn('FloresVehicleSeeder sudah digantikan Fleet2026Seeder (9 unit riil).');
        $this->call(Fleet2026Seeder::class);
    }
}
