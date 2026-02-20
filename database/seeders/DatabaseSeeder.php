<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->updateOrCreate([
            'email' => 'admin@sidoagung.com',
        ], [
            'name' => 'Super Admin',
            'password' => 'password123',
            'role' => Role::SUPERADMIN,
        ]);
    }
}
