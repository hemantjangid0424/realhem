<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@realhem.com'],
            [
                'name' => 'RealHem Super Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'broker@realhem.com'],
            [
                'name' => 'Rajesh Sharma (Apex Realty)',
                'password' => Hash::make('password'),
                'role' => 'agent',
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'owner@realhem.com'],
            [
                'name' => 'Amit Verma (Property Owner)',
                'password' => Hash::make('password'),
                'role' => 'owner',
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'buyer@realhem.com'],
            [
                'name' => 'Priya Patel (Home Buyer)',
                'password' => Hash::make('password'),
                'role' => 'user',
                'email_verified_at' => now(),
            ]
        );
    }
}
