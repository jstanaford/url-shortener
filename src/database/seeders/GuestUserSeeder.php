<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class GuestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a guest user if it doesn't exist
        User::firstOrCreate(
            ['email' => 'guest@example.com'],
            [
                'name' => 'Guest User',
                'password' => Hash::make('guest-password-123'),
                'email_verified_at' => now(),
            ]
        );
    }
}
