<?php

namespace Database\Seeders;

use App\Models\Property;
use App\Models\User;
use App\Models\UserActivity;
use Illuminate\Database\Seeder;

class UserActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first();
        $properties = Property::take(5)->get();

        if ($user && $properties->isNotEmpty()) {
            foreach ($properties as $property) {
                UserActivity::create([
                    'user_id' => $user->id,
                    'activity_type' => 'view_property',
                    'property_id' => $property->id,
                    'metadata' => [
                        'slug' => $property->slug,
                        'title' => $property->title,
                        'city' => $property->city,
                        'locality' => $property->locality,
                    ],
                ]);
            }
        }
    }
}
