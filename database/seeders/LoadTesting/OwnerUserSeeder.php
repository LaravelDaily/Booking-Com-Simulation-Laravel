<?php

namespace Database\Seeders\LoadTesting;

use App\Models\User;
use Illuminate\Database\Seeder;

class OwnerUserSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()
            ->count(10_000)
            ->owner()
            ->create();
    }
}
