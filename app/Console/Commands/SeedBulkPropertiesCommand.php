<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SeedBulkPropertiesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'properties:seed-bulk 
                            {count=20000 : Total number of properties to create} 
                            {--chunk=500 : Batch chunk size for database inserts}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed bulk realistic Indian property listings with high-performance batch insertion';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $count = (int) $this->argument('count');
        $chunkSize = max((int) $this->option('chunk'), 100);

        if ($count <= 0) {
            $this->error('Count must be greater than 0.');

            return self::FAILURE;
        }

        $this->info("Preparing to seed {$count} property records (batch size: {$chunkSize})...");

        // 1. Ensure we have users to assign properties to
        $userIds = User::pluck('id')->all();
        if (empty($userIds)) {
            $this->warn('No users found. Creating fallback users...');
            $fallbackUsers = [
                ['name' => 'Apex Realty Agent', 'email' => 'broker.seed@realhem.com', 'mobile' => '9876500001', 'role' => 'agent', 'password' => bcrypt('password')],
                ['name' => 'Prime Properties Developer', 'email' => 'builder.seed@realhem.com', 'mobile' => '9876500002', 'role' => 'owner', 'password' => bcrypt('password')],
                ['name' => 'Verified Property Owner', 'email' => 'owner.seed@realhem.com', 'mobile' => '9876500003', 'role' => 'owner', 'password' => bcrypt('password')],
            ];
            foreach ($fallbackUsers as $u) {
                $userIds[] = DB::table('users')->insertGetId(array_merge($u, [
                    'country_code' => '+91',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }

        // 2. High-quality curated Indian Hubs & Localities
        $cityHubs = [
            [
                'city' => 'Ahmedabad',
                'state' => 'Gujarat',
                'pincodes' => ['380015', '380054', '380058', '380059', '382424', '382481', '382418', '380009'],
                'localities' => [
                    'Prahlad Nagar', 'Bodakdev', 'Satellite', 'Thaltej', 'Bopal', 'South Bopal',
                    'Shela', 'SG Highway', 'Gota', 'Jagatpur', 'Vastral', 'Science City',
                    'Chandkheda', 'Motera', 'Nikol', 'Maninagar', 'Navrangpura', 'Paldi', 'Makarba',
                ],
                'projects' => [
                    'Godrej Garden City', 'The Metropark', 'Trinay Anagh', 'Sun South Park',
                    'Gala Haven', 'Safal Parisar', 'Orchid Whitefield', 'Iscon Platinum',
                    'Adani Shantigram', 'Venus Pasadena', 'Shilp Shaligram', 'Titanium City Center',
                    'Palash Homes', 'Swati Clover', 'Shela Sky Villas',
                ],
                'price_mult' => 1.0,
            ],
            [
                'city' => 'Delhi NCR',
                'state' => 'Delhi',
                'pincodes' => ['110075', '110001', '201301', '201310', '201309', '122002', '122018', '122009'],
                'localities' => [
                    'Sector 150, Noida', 'Sector 62, Noida', 'Sector 137, Noida', 'Sector 75, Noida',
                    'Golf Course Road, Gurgaon', 'DLF Phase 5, Gurgaon', 'Sohna Road, Gurgaon',
                    'Dwarka Expressway', 'Dwarka Sector 10', 'Vasant Kunj', 'Rohini', 'Indirapuram, Ghaziabad',
                    'Noida Extension', 'Greater Noida West',
                ],
                'projects' => [
                    'DLF The Camellias', 'ATS Knightsbridge', 'Mahagun Moderne', 'Godrej Woods',
                    'Tata Primanti', 'Paras Quartier', 'M3M Golfestate', 'Ireo Skyon',
                    'Supertech Cape Town', 'Gaur City 2', 'Emaar Palm Hills',
                ],
                'price_mult' => 1.45,
            ],
            [
                'city' => 'Mumbai',
                'state' => 'Maharashtra',
                'pincodes' => ['400050', '400058', '400069', '400076', '400018', '400063', '400092', '400601'],
                'localities' => [
                    'Bandra West', 'Andheri West', 'Andheri East', 'Powai', 'Juhu', 'Worli',
                    'Goregaon East', 'Borivali West', 'Thane West', 'Vashi, Navi Mumbai',
                    'Malad West', 'Chembur', 'Kandivali East', 'Lower Parel',
                ],
                'projects' => [
                    'Lodha Park', 'Rustomjee Elements', 'Hiranandani Gardens', 'Kalpataru Sparkle',
                    'Oberoi Exquisite', 'Godrej Platinum', 'Piramal Mahalaxmi', 'Raheja Artesia',
                    'Runwal Bliss', 'Wadhwa Courtyard', 'Shapoorji Pallonji Vicinia',
                ],
                'price_mult' => 2.2,
            ],
            [
                'city' => 'Bengaluru',
                'state' => 'Karnataka',
                'pincodes' => ['560066', '560100', '560038', '560034', '560102', '560103', '560035', '560024'],
                'localities' => [
                    'Whitefield', 'Indiranagar', 'Koramangala', 'HSR Layout', 'Bellandur',
                    'Sarjapur Road', 'Hebbal', 'Electronic City', 'Yelahanka', 'JP Nagar',
                    'Marathahalli', 'Bannerghatta Road', 'Thanisandra',
                ],
                'projects' => [
                    'Prestige Falcon City', 'Sobha Dream Acres', 'Brigade Gateway', 'Godrej Air',
                    'Salarpuria Sattva Magnificence', 'Puravankara Palm Beach', 'Total Environment Windmills',
                    'Shriram Blue', 'Assetz Marq', 'Embassy Pristine',
                ],
                'price_mult' => 1.35,
            ],
            [
                'city' => 'Pune',
                'state' => 'Maharashtra',
                'pincodes' => ['411057', '411014', '411045', '411038', '411028', '411007', '411021'],
                'localities' => [
                    'Kharadi', 'Hinjawadi', 'Baner', 'Wakad', 'Viman Nagar', 'Kothrud',
                    'Hadapsar', 'Magarpatta City', 'Aundh', 'Bavdhan', 'Pimple Saudagar',
                ],
                'projects' => [
                    'Amanora Park Town', 'Kolte Patil Life Republic', 'Rohan Mithila',
                    'Panchshil Towers', 'Godrej 24', 'Kasturi The Balmoral', 'Kumar Privie',
                    'VTP Blue Waters', 'Pride World City', 'Nyati Elysia',
                ],
                'price_mult' => 1.1,
            ],
            [
                'city' => 'Hyderabad',
                'state' => 'Telangana',
                'pincodes' => ['500081', '500084', '500034', '500033', '500072', '500089', '500032'],
                'localities' => [
                    'Gachibowli', 'Hitec City', 'Kondapur', 'Madhapur', 'Banjara Hills',
                    'Jubilee Hills', 'Financial District', 'Kukatpally', 'Tellapur', 'Manikonda', 'Kokapet',
                ],
                'projects' => [
                    'My Home Bhooja', 'Aparna Serene Park', 'Rajapushpa Atria', 'Jayabheri The Peak',
                    'Prestige High Fields', 'SMR Vinay Iconia', 'Lodha Bellezza', 'Ramky Towers',
                    'Incor One City', 'DSR The World',
                ],
                'price_mult' => 1.25,
            ],
        ];

        // 3. Curated real estate photos (Unsplash high quality architectural/apartment photos)
        $curatedPhotos = [
            'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=800&auto=format&fit=crop&q=70',
            'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=800&auto=format&fit=crop&q=70',
            'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=800&auto=format&fit=crop&q=70',
            'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=800&auto=format&fit=crop&q=70',
            'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=800&auto=format&fit=crop&q=70',
            'https://images.unsplash.com/photo-1600566753376-12c8ab7fb75b?w=800&auto=format&fit=crop&q=70',
            'https://images.unsplash.com/photo-1600573472591-ee6b68d14c68?w=800&auto=format&fit=crop&q=70',
            'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=800&auto=format&fit=crop&q=70',
            'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=800&auto=format&fit=crop&q=70',
            'https://images.unsplash.com/photo-1560185127-6ed189bf02f4?w=800&auto=format&fit=crop&q=70',
            'https://images.unsplash.com/photo-1513694203232-719a280e022f?w=800&auto=format&fit=crop&q=70',
            'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=800&auto=format&fit=crop&q=70',
            'https://images.unsplash.com/photo-1570129477492-45c003edd2be?w=800&auto=format&fit=crop&q=70',
            'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?w=800&auto=format&fit=crop&q=70',
        ];

        $allAmenities = [
            'Lift', '24x7 Security', 'Gym', 'Swimming Pool', 'Reserved Parking',
            'Power Backup', 'Clubhouse', 'Children Play Area', 'Park / Garden',
            'Intercom', 'CCTV Surveillance', 'Piped Gas',
        ];

        $furnishingOptions = ['Unfurnished', 'Semi-Furnished', 'Furnished'];
        $facings = ['East', 'North', 'North-East', 'West', 'South'];
        $constStatuses = ['Ready to Move', 'Ready to Move', 'Under Construction', 'New Launch'];

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        $inserted = 0;
        $batch = [];
        $runId = base_convert(time() % 1000000, 10, 36);

        // Precompute base timestamps
        $now = now();

        for ($i = 1; $i <= $count; $i++) {
            $hub = $cityHubs[array_rand($cityHubs)];
            $city = $hub['city'];
            $state = $hub['state'];
            $locality = $hub['localities'][array_rand($hub['localities'])];
            $project = $hub['projects'][array_rand($hub['projects'])];
            $pincode = $hub['pincodes'][array_rand($hub['pincodes'])];
            $priceMult = $hub['price_mult'];

            // Type & Bedroom configuration
            $typeRoll = mt_rand(1, 100);
            if ($typeRoll <= 70) {
                $propType = 'Residential Apartment';
                $bhk = mt_rand(1, 100) <= 20 ? 1 : (mt_rand(1, 100) <= 50 ? 2 : (mt_rand(1, 100) <= 80 ? 3 : 4));
            } elseif ($typeRoll <= 82) {
                $propType = 'Independent House/Villa';
                $bhk = mt_rand(3, 5);
            } elseif ($typeRoll <= 90) {
                $propType = 'Commercial Office';
                $bhk = 0;
            } elseif ($typeRoll <= 95) {
                $propType = 'Commercial Shop';
                $bhk = 0;
            } else {
                $propType = 'Residential Plot';
                $bhk = 0;
            }

            $propertyFor = mt_rand(1, 100) <= 80 ? 'Sell' : 'Rent';
            $userType = mt_rand(1, 100) <= 45 ? 'Owner' : (mt_rand(1, 100) <= 75 ? 'Agent' : 'Builder');
            $userId = $userIds[array_rand($userIds)];

            // Carpet & Price calculations
            if ($propType === 'Residential Plot') {
                $carpetArea = mt_rand(1000, 4500);
                $superBuiltup = $carpetArea;
                $bathrooms = 0;
                $balconies = 0;
                $ratePerSqft = (int) round(mt_rand(3500, 9500) * $priceMult);
                $expectedPrice = (int) round($carpetArea * $ratePerSqft);
                $title = "{$carpetArea} sq.ft. Prime Residential Plot in {$locality}, {$city}";
                $furnishing = 'Unfurnished';
                $floorNo = 'G';
                $totalFloors = 0;
            } elseif (str_contains($propType, 'Commercial')) {
                $carpetArea = mt_rand(400, 3500);
                $superBuiltup = (int) round($carpetArea * 1.35);
                $bathrooms = mt_rand(1, 3);
                $balconies = 0;
                $furnishing = $furnishingOptions[array_rand($furnishingOptions)];
                $floorNo = (string) mt_rand(1, 12);
                $totalFloors = 15;

                if ($propertyFor === 'Rent') {
                    $ratePerSqft = (int) round(mt_rand(45, 120) * $priceMult);
                    $expectedPrice = (int) round($carpetArea * $ratePerSqft);
                    $title = "{$carpetArea} sq.ft. {$furnishing} {$propType} for Rent in {$project}, {$locality}";
                } else {
                    $ratePerSqft = (int) round(mt_rand(7500, 19000) * $priceMult);
                    $expectedPrice = (int) round($carpetArea * $ratePerSqft);
                    $title = "{$carpetArea} sq.ft. {$furnishing} {$propType} for Sale in {$project}, {$locality}";
                }
            } else {
                // Apartment or Villa
                $carpetArea = match ($bhk) {
                    1 => mt_rand(450, 650),
                    2 => mt_rand(800, 1150),
                    3 => mt_rand(1300, 1850),
                    4 => mt_rand(2100, 3200),
                    default => mt_rand(3500, 5200),
                };
                $superBuiltup = (int) round($carpetArea * 1.28);
                $bathrooms = max(1, $bhk - (mt_rand(0, 1)));
                $balconies = mt_rand(1, 3);
                $furnishing = $furnishingOptions[array_rand($furnishingOptions)];
                $floorNo = (string) mt_rand(1, 24);
                $totalFloors = 25;

                if ($propertyFor === 'Rent') {
                    $baseRent = match ($bhk) {
                        1 => mt_rand(12000, 22000),
                        2 => mt_rand(20000, 42000),
                        3 => mt_rand(32000, 75000),
                        4 => mt_rand(60000, 140000),
                        default => mt_rand(100000, 250000),
                    };
                    $expectedPrice = (int) round($baseRent * $priceMult);
                    $ratePerSqft = (int) round($expectedPrice / max(1, $carpetArea));
                    $title = "{$bhk} BHK {$propType} for Rent in {$project}, {$locality}, {$city}";
                } else {
                    $baseRate = match ($bhk) {
                        1 => mt_rand(5200, 8500),
                        2 => mt_rand(6200, 10500),
                        3 => mt_rand(7200, 14500),
                        4 => mt_rand(9500, 21000),
                        default => mt_rand(12000, 28000),
                    };
                    $ratePerSqft = (int) round($baseRate * $priceMult);
                    $expectedPrice = (int) round($carpetArea * $ratePerSqft);
                    $forLabel = $propType === 'Independent House/Villa' ? 'Villa' : 'Apartment';
                    $title = "{$bhk} BHK Luxury {$forLabel} for Sale in {$project}, {$locality}";
                }
            }

            // Pick 2-3 photos
            $photoKeys = (array) array_rand($curatedPhotos, mt_rand(2, 3));
            $photos = array_values(array_intersect_key($curatedPhotos, array_flip($photoKeys)));

            // Pick 4-7 amenities
            $amenityKeys = (array) array_rand($allAmenities, mt_rand(4, 7));
            $amenities = array_values(array_intersect_key($allAmenities, array_flip($amenityKeys)));

            // Unique slug generation (deterministic unique key using runId + index)
            $slugBase = Str::slug(implode(' ', array_filter([
                $bhk ? "{$bhk} bhk" : null,
                $propType,
                'for',
                strtolower($propertyFor),
                'in',
                $locality,
                $city,
            ])));
            $slug = "{$slugBase}-spld-{$runId}-{$i}";

            $isVerified = mt_rand(1, 100) <= 65;
            $isFeatured = mt_rand(1, 100) <= 12;
            $statusRoll = mt_rand(1, 100);
            $status = $statusRoll <= 94 ? 'active' : ($statusRoll <= 97 ? 'sold' : 'rented');

            $createdAt = (clone $now)->subDays(mt_rand(0, 150))->subMinutes(mt_rand(0, 1440));

            $towerNum = ($i % 12) + 1;

            $batch[] = [
                'user_id' => $userId,
                'user_type' => $userType,
                'property_for' => $propertyFor,
                'property_type' => $propType,
                'title' => $title,
                'slug' => $slug,
                'description' => "Stunning {$propType} situated in the prestigious neighborhood of {$locality}, {$city}. Offers spacious living, excellent ventilation, and proximity to top business hubs, metro, and elite schools. Built with premium materials and modern amenities.",
                'project_name' => $project,
                'city' => $city,
                'state' => $state,
                'locality' => $locality,
                'sub_locality' => "Near {$locality} Main Hub",
                'address' => "Tower {$towerNum}, {$project}, {$locality}, {$city}",
                'landmark' => 'Near Metro Station & Express Highway',
                'pincode' => $pincode,
                'latitude' => null,
                'longitude' => null,
                'bedrooms' => $bhk,
                'bathrooms' => $bathrooms,
                'balconies' => $balconies,
                'carpet_area' => $carpetArea,
                'super_builtup_area' => $superBuiltup,
                'furnishing_status' => $furnishing,
                'floor_no' => $floorNo,
                'total_floors' => $totalFloors,
                'facing' => $facings[array_rand($facings)],
                'construction_status' => $constStatuses[array_rand($constStatuses)],
                'expected_price' => $expectedPrice,
                'price_per_sqft' => $ratePerSqft,
                'maintenance_charge' => mt_rand(1500, 6000),
                'price_negotiable' => mt_rand(1, 100) <= 40 ? 1 : 0,
                'amenities' => json_encode($amenities),
                'photos' => json_encode($photos),
                'status' => $status,
                'is_verified' => $isVerified ? 1 : 0,
                'is_featured' => $isFeatured ? 1 : 0,
                'created_at' => $createdAt,
                'updated_at' => $now,
            ];

            if (count($batch) >= $chunkSize) {
                DB::table('properties')->insert($batch);
                $inserted += count($batch);
                $bar->advance(count($batch));
                $batch = [];
            }
        }

        // Insert remaining
        if (! empty($batch)) {
            DB::table('properties')->insert($batch);
            $inserted += count($batch);
            $bar->advance(count($batch));
        }

        $bar->finish();
        $this->newLine(2);

        // Clear query caches so listings immediately reflect the 20,000 properties
        Cache::flush();

        $totalInDb = DB::table('properties')->count();
        $this->info("✓ Successfully created {$inserted} property listings!");
        $this->info("✓ Total properties in database now: {$totalInDb}");

        return self::SUCCESS;
    }
}
