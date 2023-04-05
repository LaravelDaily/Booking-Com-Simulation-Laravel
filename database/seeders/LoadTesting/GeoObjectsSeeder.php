<?php

namespace Database\Seeders\LoadTesting;

use App\Models\City;
use App\Models\Geoobject;
use Illuminate\Database\Seeder;

class GeoObjectsSeeder extends Seeder
{
    public function run(): void
    {
        if (Geoobject::count() < 1_000) {
            $cities = City::pluck('id');
            for ($i = 0; $i < 1_000; $i++) {
                Geoobject::factory()
                    ->create([
                        'city_id' => $cities->random(),
                    ]);
            }
        }
    }
}
