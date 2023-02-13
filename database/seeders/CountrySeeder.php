<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Country::create(['name' => 'United States', 'lat' => 37.09024, 'long' => -95.712891]);
        Country::create(['name' => 'United Kingdom', 'lat' => 55.378051, 'long' => -3.435973]);
    }
}
