<?php

namespace Database\Seeders;

use App\Models\Geoobject;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GeoobjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Geoobject::create([
            'city_id' => 1,
            'name' => 'Statue of Liberty',
            'lat' => 40.689247,
            'long' => -74.044502
        ]);

        Geoobject::create([
            'city_id' => 2,
            'name' => 'Big Ben',
            'lat' => 51.500729,
            'long' => -0.124625
        ]);
    }
}
