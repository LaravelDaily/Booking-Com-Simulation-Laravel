<?php

namespace Database\Seeders\LoadTesting;

use App\Models\Property;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class PropertiesSeeder extends Seeder
{
    public function run(): void
    {
        $count = Property::count();
        if ($count < 50_000) {
            $users = User::where('role_id', Role::ROLE_OWNER)->pluck('id');

            for ($i = 0; $i < 50_000 - $count; $i++) {
                Property::factory()->create([
                    'owner_id' => $users->random(),
                ]);
            }
        }
    }
}
