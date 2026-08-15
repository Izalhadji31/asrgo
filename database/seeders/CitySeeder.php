<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cities = [
            [
                'name' => 'Labuan Bajo',
                'slug' => 'labuan-bajo',
                'initials' => 'LB',
                'description' => 'Gerbang menuju Taman Nasional Komodo dan destinasi wisata terkenal di Flores Barat',
                'latitude' => -8.4833,
                'longitude' => 119.8833,
                'is_popular' => true,
                'image_url' => 'https://images.unsplash.com/photo-1596394516093-501ba68a0ba6?w=800',
            ],
            [
                'name' => 'Ende',
                'slug' => 'ende',
                'initials' => 'EN',
                'description' => 'Kota Pancasila dan ibu kota Kabupaten Ende, pusat administrasi Flores Tengah',
                'latitude' => -8.8500,
                'longitude' => 121.3167,
                'is_popular' => true,
                'image_url' => 'https://images.unsplash.com/photo-1555238748-0f6891c29544?w=800',
            ],
            [
                'name' => 'Maumere',
                'slug' => 'maumere',
                'initials' => 'MM',
                'description' => 'Surga bawah laut dan kota terbesar di Flores Timur dengan pelabuhan utama',
                'latitude' => -8.6167,
                'longitude' => 122.2500,
                'is_popular' => true,
                'image_url' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=800',
            ],
            [
                'name' => 'Ruteng',
                'slug' => 'ruteng',
                'initials' => 'RT',
                'description' => 'Kota dingin di dataran tinggi Manggarai, gerbang menuju Desa Wae Rebo',
                'latitude' => -8.6167,
                'longitude' => 120.4667,
                'is_popular' => true,
                'image_url' => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800',
            ],
            [
                'name' => 'Bajawa',
                'slug' => 'bajawa',
                'initials' => 'BJ',
                'description' => 'Kota adat Ngada dengan desa tradisional Bena dan sumber air panas alami',
                'latitude' => -8.7167,
                'longitude' => 120.7667,
                'is_popular' => true,
                'image_url' => 'https://images.unsplash.com/photo-1472214103451-9374bd1c798e?w=800',
            ],
            [
                'name' => 'Larantuka',
                'slug' => 'larantuka',
                'initials' => 'LT',
                'description' => 'Kota tradisi Semana Santa dan pusat keagamaan Katolik di Flores Timur',
                'latitude' => -8.3833,
                'longitude' => 122.9833,
                'is_popular' => false,
                'image_url' => 'https://images.unsplash.com/photo-1469474968028-56623f02e42e?w=800',
            ],
            [
                'name' => 'Borong',
                'slug' => 'borong',
                'initials' => 'BR',
                'description' => 'Ibu kota Kabupaten Sikka, kota transit antara Maumere dan Ende',
                'latitude' => -8.7333,
                'longitude' => 121.5833,
                'is_popular' => false,
                'image_url' => 'https://images.unsplash.com/photo-1501785888041-af3ef285b470?w=800',
            ],
            [
                'name' => 'Reok',
                'slug' => 'reok',
                'initials' => 'RK',
                'description' => 'Kota kecil di Manggarai dengan keindahan alam pedesaan yang asri',
                'latitude' => -8.4500,
                'longitude' => 120.2500,
                'is_popular' => false,
                'image_url' => 'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=800',
            ],
            [
                'name' => 'Mbay',
                'slug' => 'mbay',
                'initials' => 'MB',
                'description' => 'Ibu kota Kabupaten Nagekeo, pusat pertanian dan perkebunan',
                'latitude' => -8.6500,
                'longitude' => 121.2500,
                'is_popular' => false,
                'image_url' => 'https://images.unsplash.com/photo-1505142468610-359e7d316be0?w=800',
            ],
            [
                'name' => 'Waingapu',
                'slug' => 'waingapu',
                'initials' => 'WG',
                'description' => 'Kota di ujung timur Flores dengan pantai pasir putih yang indah',
                'latitude' => -9.6500,
                'longitude' => 120.4167,
                'is_popular' => false,
                'image_url' => 'https://images.unsplash.com/photo-1559827260-dc66d52bef19?w=800',
            ],
        ];

        foreach ($cities as $city) {
            DB::table('cities')->insert([
                'name' => $city['name'],
                'slug' => $city['slug'],
                'initials' => $city['initials'],
                'description' => $city['description'],
                'latitude' => $city['latitude'],
                'longitude' => $city['longitude'],
                'is_popular' => $city['is_popular'],
                'image_url' => $city['image_url'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('Cities seeder completed successfully!');
    }
}
