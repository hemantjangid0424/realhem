<?php

namespace Database\Factories;

use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Property>
 */
class PropertyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $bhk = fake()->randomElement([1, 2, 3, 4]);
        $carpetArea = $bhk * fake()->numberBetween(450, 650);
        $pricePerSqft = fake()->numberBetween(5500, 14000);
        $expectedPrice = $carpetArea * $pricePerSqft;

        $cities = ['Ahmedabad', 'Delhi NCR', 'Mumbai', 'Bangalore', 'Pune', 'Hyderabad'];
        $city = fake()->randomElement($cities);

        $localitiesByCity = [
            'Ahmedabad' => ['Bopal', 'SG Highway', 'Prahlad Nagar', 'Thaltej', 'Satellite', 'Bodakdev', 'Shela', 'Gota'],
            'Delhi NCR' => ['Sector 62, Noida', 'Indirapuram', 'Golf Course Road, Gurgaon', 'Dwarka Sector 10', 'Sector 150, Noida'],
            'Mumbai' => ['Andheri West', 'Bandra West', 'Powai', 'Goregaon East', 'Thane West', 'Kandivali East'],
            'Bangalore' => ['Whitefield', 'Indiranagar', 'Koramangala', 'HSR Layout', 'Sarjapur Road', 'Bellandur'],
            'Pune' => ['Kharadi', 'Hinjewadi', 'Baner', 'Wakad', 'Viman Nagar', 'Kothrud'],
            'Hyderabad' => ['Gachibowli', 'Hitec City', 'Kondapur', 'Madhapur', 'Banjara Hills', 'Jubilee Hills'],
        ];

        $locality = fake()->randomElement($localitiesByCity[$city] ?? ['City Center']);

        return [
            'user_id' => User::factory(),
            'user_type' => fake()->randomElement(['Owner', 'Agent', 'Builder']),
            'property_for' => fake()->randomElement(['Sell', 'Rent']),
            'property_type' => 'Residential Apartment',
            'title' => "{$bhk} BHK Luxury Apartment in {$locality}",
            'description' => fake()->paragraph(),
            'project_name' => fake()->company().' Heights',
            'city' => $city,
            'locality' => $locality,
            'sub_locality' => 'Near Central Park',
            'address' => fake()->streetAddress(),
            'landmark' => 'Near Metro Station',
            'bedrooms' => $bhk,
            'bathrooms' => max(1, $bhk - 1),
            'balconies' => fake()->numberBetween(1, 3),
            'carpet_area' => $carpetArea,
            'super_builtup_area' => (int) round($carpetArea * 1.3),
            'furnishing_status' => fake()->randomElement(['Unfurnished', 'Semi-Furnished', 'Furnished']),
            'floor_no' => (string) fake()->numberBetween(2, 18),
            'total_floors' => 20,
            'facing' => fake()->randomElement(['North', 'East', 'North-East']),
            'construction_status' => fake()->randomElement(['Ready to Move', 'Under Construction']),
            'expected_price' => $expectedPrice,
            'price_per_sqft' => $pricePerSqft,
            'maintenance_charge' => fake()->numberBetween(2000, 6000),
            'price_negotiable' => fake()->boolean(),
            'amenities' => ['Lift', '24x7 Security', 'Gym', 'Swimming Pool', 'Reserved Parking', 'Power Backup', 'Clubhouse'],
            'photos' => [
                'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=800&auto=format&fit=crop&q=60',
                'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=800&auto=format&fit=crop&q=60',
            ],
            'status' => 'active',
            'is_verified' => true,
            'is_featured' => fake()->boolean(30),
        ];
    }
}
