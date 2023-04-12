<?php

namespace Database\Seeders\Performance;

use App\Models\Country;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(int $count = 100): void
    {
        Country::factory($count)->create();
    }
}
