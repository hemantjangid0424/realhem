<?php

namespace Database\Factories;

use App\Models\Property;
use App\Models\User;
use App\Models\UserActivity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserActivity>
 */
class UserActivityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'activity_type' => 'view_property',
            'property_id' => Property::factory(),
            'metadata' => [
                'device' => 'desktop',
                'source' => 'web',
            ],
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
        ];
    }
}
