<?php

namespace Database\Seeders\Performance;

use App\Models\City;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(int $count = 100): void
    {
        City::factory($count)->create();
    }
}
