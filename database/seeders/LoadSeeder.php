<?php

namespace Database\Seeders;

use Database\Seeders\LoadTesting\ApartmentsSeeder;
use Database\Seeders\LoadTesting\BookingsSeeder;
use Database\Seeders\LoadTesting\CitiesSeeder;
use Database\Seeders\LoadTesting\CountriesSeeder;
use Database\Seeders\LoadTesting\GeoObjectsSeeder;
use Database\Seeders\LoadTesting\OwnerUserSeeder;
use Database\Seeders\LoadTesting\PropertiesSeeder;
use Database\Seeders\LoadTesting\UserSeeder;
use Illuminate\Database\Seeder;

class LoadSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            OwnerUserSeeder::class, // 10k
            UserSeeder::class, // 10k
            CountriesSeeder::class, // 100
            CitiesSeeder::class, // 1k
            GeoObjectsSeeder::class, // 1k
            PropertiesSeeder::class, // 50k
            ApartmentsSeeder::class, // 100k
            BookingsSeeder::class, // 1M (500k no rating, 500k with rating)
        ]);
    }
}
