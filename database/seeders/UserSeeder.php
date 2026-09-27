<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Seed the users table.
     */
    public function run(): void
    {
        // Fixed demo account for testing the auth API (safe to run the seeder again)
        User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => 'Password@123',
                'email_verified_at' => now(),
            ]
        );

        User::factory(10)->create();
    }
}
