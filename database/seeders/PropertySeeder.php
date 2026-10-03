<?php

namespace Database\Seeders;

use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Seeder;

class PropertySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first() ?? User::factory()->create();

        $properties = [
            [
                'user_id' => $user->id,
                'user_type' => 'Owner',
                'property_for' => 'Sell',
                'property_type' => 'Residential Apartment',
                'title' => '3 BHK Luxurious High-Rise Apartment in SG Highway',
                'description' => 'Spacious 3 BHK apartment with marble flooring, modular kitchen, cross ventilation, 3 covered balconies, and scenic views. Situated in a prime gated community.',
                'project_name' => 'Godrej Garden City',
                'city' => 'Ahmedabad',
                'locality' => 'SG Highway',
                'sub_locality' => 'Near Nirma University',
                'address' => 'Tower C, Godrej Garden City, SG Highway',
                'landmark' => 'Opposite Nirma University',
                'bedrooms' => 3,
                'bathrooms' => 3,
                'balconies' => 2,
                'carpet_area' => 1650,
                'super_builtup_area' => 2150,
                'furnishing_status' => 'Semi-Furnished',
                'floor_no' => '12',
                'total_floors' => 22,
                'facing' => 'East',
                'construction_status' => 'Ready to Move',
                'expected_price' => 11500000,
                'price_per_sqft' => 6969,
                'maintenance_charge' => 3500,
                'price_negotiable' => true,
                'amenities' => ['Lift', '24x7 Security', 'Gym', 'Swimming Pool', 'Reserved Parking', 'Power Backup', 'Clubhouse', 'Children Play Area', 'Piped Gas'],
                'photos' => [
                    'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=800&auto=format&fit=crop&q=60',
                    'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=800&auto=format&fit=crop&q=60',
                    'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=800&auto=format&fit=crop&q=60',
                ],
                'status' => 'active',
                'is_verified' => true,
                'is_featured' => true,
            ],
            [
                'user_id' => $user->id,
                'user_type' => 'Agent',
                'property_for' => 'Rent',
                'property_type' => 'Residential Apartment',
                'title' => '2 BHK Fully Furnished Flat in Bopal',
                'description' => 'Ready to move 2 BHK modern flat with split ACs, sofa, beds, refrigerator, and smart TV. Located close to schools and shopping malls.',
                'project_name' => 'Aarohi Elysium',
                'city' => 'Ahmedabad',
                'locality' => 'Bopal',
                'sub_locality' => 'South Bopal',
                'address' => 'South Bopal Road, Ahmedabad',
                'landmark' => 'Near Sobha City',
                'bedrooms' => 2,
                'bathrooms' => 2,
                'balconies' => 1,
                'carpet_area' => 1050,
                'super_builtup_area' => 1350,
                'furnishing_status' => 'Furnished',
                'floor_no' => '5',
                'total_floors' => 14,
                'facing' => 'North-East',
                'construction_status' => 'Ready to Move',
                'expected_price' => 28000,
                'price_per_sqft' => 27,
                'maintenance_charge' => 2000,
                'price_negotiable' => false,
                'amenities' => ['Lift', '24x7 Security', 'Reserved Parking', 'Power Backup', 'Piped Gas', 'Intercom'],
                'photos' => [
                    'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=800&auto=format&fit=crop&q=60',
                    'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=800&auto=format&fit=crop&q=60',
                ],
                'status' => 'active',
                'is_verified' => true,
                'is_featured' => false,
            ],
        ];

        foreach ($properties as $prop) {
            Property::create($prop);
        }
    }
}
