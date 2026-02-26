<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'HelpDesk Admin', 'password' => 'password', 'role' => 'admin', 'email_verified_at' => now()]
        );

        User::factory()
            ->count(12)
            ->agent()
            ->create();

        User::factory()
            ->count(60)
            ->customer()
            ->create();
    }
}
