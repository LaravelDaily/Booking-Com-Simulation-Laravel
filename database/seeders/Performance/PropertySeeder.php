<?php

namespace Database\Seeders\Performance;

use App\Models\City;
use App\Models\Property;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PropertySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(int $count = 100): void
    {
        $users = User::where('role_id', Role::ROLE_OWNER)->pluck('id');
        $cities = City::pluck('id');

        $properties = [];
        for ($i = 1; $i <= $count; $i++) {
            $properties[] = [
                'owner_id' => $users->random(),
                'city_id' => $cities->random(),
                'name' => 'Property ' . $i,
                'address_street' => 'Address ' . $i,
                'address_postcode' => rand(10000, 99999),
                'lat' => rand(-89, 89) + rand(-10000000, 10000000) / 10000000,
                'long' => rand(-89, 89) + rand(-10000000, 10000000) / 10000000,
                'created_at' => now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString(),
            ];
        }

        foreach (array_chunk($properties, 500) as $propertiesChunk) {
            Property::insert($propertiesChunk);
        }
    }
}
