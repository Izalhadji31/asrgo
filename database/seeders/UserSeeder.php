<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Idempotent: aman dijalankan berulang (tidak error duplikat email).
     */
    public function run(): void
    {
        $users = [
            ['Admin', 'admin@asrgo.test', 'admin'],
            ['Mitra', 'mitra@asrgo.test', 'mitra'],
            ['Driver', 'driver@asrgo.test', 'driver'],
            ['Customer', 'customer@asrgo.test', 'customer'],
        ];

        foreach ($users as [$nama, $email, $role]) {
            User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $nama,
                    'password' => bcrypt('password'),
                    'role' => $role,
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
