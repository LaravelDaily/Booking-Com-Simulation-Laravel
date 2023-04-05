<?php

namespace Database\Seeders\LoadTesting;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class OwnerUserSeeder extends Seeder
{
    public function run(): void
    {
        if (User::where('role_id', Role::ROLE_OWNER)->count() < 10_000) {
            User::factory()
                ->count(10_000)
                ->owner()
                ->create();
        }
    }
}
