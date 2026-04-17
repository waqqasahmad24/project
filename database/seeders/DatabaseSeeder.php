<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Provider;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create a Test User
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password'),
        ]);

        // 2. Create Service Providers
        $providers = [
            [
                'name' => 'Dr. Smith',
                'service_type' => 'doctor',
                'description' => 'General Physician with 10 years experience.',
                'working_hours' => ['start' => '09:00', 'end' => '17:00'],
                'available_days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
            ],
            [
                'name' => 'Elite Salon',
                'service_type' => 'salon',
                'description' => 'Luxury hair and spa treatments.',
                'working_hours' => ['start' => '10:00', 'end' => '20:00'],
                'available_days' => ['Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
            ],
            [
                'name' => 'Tech Consultant',
                'service_type' => 'consultant',
                'description' => 'Business and Tech strategy expert.',
                'working_hours' => ['start' => '08:00', 'end' => '16:00'],
                'available_days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday'],
            ],
        ];

        foreach ($providers as $providerData) {
            // Create a user for the provider as well (optional, but good for relations)
            $pUser = User::create([
                'name' => $providerData['name'],
                'email' => strtolower(str_replace(' ', '.', $providerData['name'])) . '@example.com',
                'password' => Hash::make('password'),
            ]);

            Provider::create(array_merge($providerData, ['user_id' => $pUser->id]));
        }
    }
}
