<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@asrgo.test',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Mitra',
            'email' => 'mitra@asrgo.test',
            'password' => bcrypt('password'),
            'role' => 'mitra',
        ]);

        User::create([
            'name' => 'Driver',
            'email' => 'driver@asrgo.test',
            'password' => bcrypt('password'),
            'role' => 'driver',
        ]);

        User::create([
            'name' => 'Customer',
            'email' => 'customer@asrgo.test',
            'password' => bcrypt('password'),
            'role' => 'customer',
        ]);
    }
}
