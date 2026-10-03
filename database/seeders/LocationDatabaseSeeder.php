<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Country;
use App\Models\Locality;
use App\Models\State;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LocationDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds for Countries, States, Cities, and Localities.
     */
    public function run(): void
    {
        $this->seedCountries();
        $this->seedStates();
        $this->seedCities();
        $this->seedLocalities();
    }

    /**
     * Seed primary Country (India) and key NRI investment markets.
     */
    protected function seedCountries(): void
    {
        $countries = [
            [
                'id' => 101,
                'name' => 'India',
                'iso2' => 'IN',
                'iso3' => 'IND',
                'phonecode' => '91',
                'currency' => 'INR',
            ],
            [
                'id' => 229,
                'name' => 'United Arab Emirates',
                'iso2' => 'AE',
                'iso3' => 'ARE',
                'phonecode' => '971',
                'currency' => 'AED',
            ],
            [
                'id' => 233,
                'name' => 'United States',
                'iso2' => 'US',
                'iso3' => 'USA',
                'phonecode' => '1',
                'currency' => 'USD',
            ],
            [
                'id' => 232,
                'name' => 'United Kingdom',
                'iso2' => 'GB',
                'iso3' => 'GBR',
                'phonecode' => '44',
                'currency' => 'GBP',
            ],
            [
                'id' => 196,
                'name' => 'Singapore',
                'iso2' => 'SG',
                'iso3' => 'SGP',
                'phonecode' => '65',
                'currency' => 'SGD',
            ],
        ];

        foreach ($countries as $c) {
            Country::updateOrCreate(['id' => $c['id']], $c);
        }
    }

    /**
     * Seed all 36 Indian States and Union Territories from downloaded states.csv.
     */
    protected function seedStates(): void
    {
        $filePath = database_path('data/states.csv');

        if (! file_exists($filePath)) {
            return;
        }

        $fp = fopen($filePath, 'r');
        fgetcsv($fp, 0, ',', '"', '\\'); // Skip header

        while (($row = fgetcsv($fp, 0, ',', '"', '\\')) !== false) {
            // Check if country_code is 'IN' (index 3) or country_id is 101 (index 2)
            if (($row[3] ?? '') === 'IN' || ($row[2] ?? '') == '101') {
                State::updateOrCreate(
                    ['id' => (int) $row[0]],
                    [
                        'country_id' => 101,
                        'name' => trim($row[1]),
                        'state_code' => trim($row[5] ?? ''),
                        'type' => trim($row[8] ?? 'state'),
                    ]
                );
            }
        }
        fclose($fp);
    }

    /**
     * Seed Indian Cities and Districts from downloaded indian_states_districts.json.
     */
    protected function seedCities(): void
    {
        $filePath = database_path('data/indian_states_districts.json');

        if (! file_exists($filePath)) {
            return;
        }

        $jsonData = json_decode(file_get_contents($filePath), true);
        if (! isset($jsonData['states'])) {
            return;
        }

        $metroNames = [
            'Delhi', 'New Delhi', 'Noida', 'Greater Noida', 'Gurugram', 'Gurgaon',
            'Faridabad', 'Ghaziabad', 'Mumbai', 'Mumbai Suburban', 'Thane', 'Navi Mumbai',
            'Pune', 'Bengaluru', 'Bengaluru Urban', 'Hyderabad', 'Chennai', 'Kolkata',
            'Ahmedabad', 'Surat', 'Jaipur', 'Lucknow', 'Chandigarh', 'Kochi',
        ];

        // Cache state name to state ID map for fast lookup
        $statesMap = State::where('country_id', 101)->pluck('id', 'name')->toArray();

        // Specific high-demand real-estate cities that might not be individual districts
        $extraRealEstateCities = [
            ['name' => 'Noida', 'state' => 'Uttar Pradesh', 'lat' => 28.5355, 'lng' => 77.3910],
            ['name' => 'Greater Noida', 'state' => 'Uttar Pradesh', 'lat' => 28.4744, 'lng' => 77.5040],
            ['name' => 'Gurugram', 'state' => 'Haryana', 'lat' => 28.4595, 'lng' => 77.0266],
            ['name' => 'Navi Mumbai', 'state' => 'Maharashtra', 'lat' => 19.0330, 'lng' => 73.0297],
            ['name' => 'Thane', 'state' => 'Maharashtra', 'lat' => 19.2183, 'lng' => 72.9781],
        ];

        foreach ($jsonData['states'] as $stateData) {
            $stateName = $stateData['state'];
            $stateId = $statesMap[$stateName] ?? null;

            if (! $stateId) {
                // Try fuzzy lookup
                $matchingState = State::where('country_id', 101)
                    ->where('name', 'LIKE', "%{$stateName}%")
                    ->first();
                $stateId = $matchingState?->id;
            }

            if (! $stateId) {
                continue;
            }

            foreach ($stateData['districts'] as $district) {
                $isMetro = in_array($district, $metroNames);

                City::updateOrCreate(
                    [
                        'name' => $district,
                        'state_id' => $stateId,
                    ],
                    [
                        'country_id' => 101,
                        'slug' => Str::slug($district),
                        'is_metro' => $isMetro,
                    ]
                );
            }
        }

        // Ensure key real-estate sub-cities are always present
        foreach ($extraRealEstateCities as $c) {
            $stateId = $statesMap[$c['state']] ?? null;
            if ($stateId) {
                City::updateOrCreate(
                    [
                        'name' => $c['name'],
                        'state_id' => $stateId,
                    ],
                    [
                        'country_id' => 101,
                        'slug' => Str::slug($c['name']),
                        'latitude' => $c['lat'],
                        'longitude' => $c['lng'],
                        'is_metro' => true,
                    ]
                );
            }
        }
    }

    /**
     * Seed top high-demand 99acres real estate localities with price metrics.
     */
    protected function seedLocalities(): void
    {
        $localitiesData = [
            // Noida & Greater Noida
            'Noida' => [
                ['name' => 'Sector 150', 'pincode' => '201310', 'rate' => 8900, 'growth' => 14.5],
                ['name' => 'Sector 62', 'pincode' => '201309', 'rate' => 9500, 'growth' => 10.2],
                ['name' => 'Sector 137', 'pincode' => '201305', 'rate' => 8200, 'growth' => 12.8],
                ['name' => 'Sector 75', 'pincode' => '201307', 'rate' => 7800, 'growth' => 9.5],
                ['name' => 'Sector 128 (Expressway)', 'pincode' => '201304', 'rate' => 14200, 'growth' => 18.0],
                ['name' => 'Sector 78', 'pincode' => '201307', 'rate' => 7400, 'growth' => 8.4],
            ],
            'Greater Noida' => [
                ['name' => 'Pari Chowk', 'pincode' => '201310', 'rate' => 5800, 'growth' => 11.0],
                ['name' => 'Noida Extension (Greater Noida West)', 'pincode' => '201306', 'rate' => 5200, 'growth' => 15.2],
                ['name' => 'Alpha 1', 'pincode' => '201308', 'rate' => 6100, 'growth' => 8.9],
                ['name' => 'Techzone 4', 'pincode' => '201306', 'rate' => 5400, 'growth' => 13.5],
            ],
            // Gurugram
            'Gurugram' => [
                ['name' => 'Golf Course Road', 'pincode' => '122002', 'rate' => 24500, 'growth' => 21.4],
                ['name' => 'Golf Course Extension Road', 'pincode' => '122018', 'rate' => 15600, 'growth' => 19.2],
                ['name' => 'DLF Phase 5', 'pincode' => '122009', 'rate' => 22000, 'growth' => 18.5],
                ['name' => 'Sohna Road', 'pincode' => '122018', 'rate' => 9200, 'growth' => 11.0],
                ['name' => 'Dwarka Expressway (Sector 109)', 'pincode' => '122017', 'rate' => 11800, 'growth' => 24.0],
                ['name' => 'Sector 57', 'pincode' => '122003', 'rate' => 12500, 'growth' => 14.1],
            ],
            // Mumbai / Western Suburbs
            'Mumbai' => [
                ['name' => 'Bandra West', 'pincode' => '400050', 'rate' => 48000, 'growth' => 8.5],
                ['name' => 'Andheri West', 'pincode' => '400058', 'rate' => 28500, 'growth' => 7.8],
                ['name' => 'Juhu', 'pincode' => '400049', 'rate' => 45000, 'growth' => 6.4],
                ['name' => 'Powai', 'pincode' => '400076', 'rate' => 24000, 'growth' => 9.2],
                ['name' => 'Worli', 'pincode' => '400018', 'rate' => 42000, 'growth' => 10.0],
                ['name' => 'Goregaon East', 'pincode' => '400063', 'rate' => 21500, 'growth' => 8.1],
                ['name' => 'Borivali West', 'pincode' => '400092', 'rate' => 19500, 'growth' => 7.2],
            ],
            // Bengaluru
            'Bengaluru' => [
                ['name' => 'Whitefield', 'pincode' => '560066', 'rate' => 9800, 'growth' => 14.8],
                ['name' => 'Electronic City Phase 1', 'pincode' => '560100', 'rate' => 6400, 'growth' => 11.2],
                ['name' => 'Indiranagar', 'pincode' => '560038', 'rate' => 18500, 'growth' => 8.4],
                ['name' => 'Koramangala', 'pincode' => '560034', 'rate' => 17200, 'growth' => 9.0],
                ['name' => 'HSR Layout', 'pincode' => '560102', 'rate' => 13500, 'growth' => 12.5],
                ['name' => 'Bellandur / Outer Ring Road', 'pincode' => '560103', 'rate' => 11200, 'growth' => 13.0],
                ['name' => 'Sarjapur Road', 'pincode' => '560035', 'rate' => 8900, 'growth' => 15.6],
                ['name' => 'Hebbal', 'pincode' => '560024', 'rate' => 11500, 'growth' => 12.0],
            ],
            // Pune
            'Pune' => [
                ['name' => 'Hinjawadi Phase 1', 'pincode' => '411057', 'rate' => 7800, 'growth' => 12.3],
                ['name' => 'Kharadi', 'pincode' => '411014', 'rate' => 8900, 'growth' => 14.0],
                ['name' => 'Wakad', 'pincode' => '411057', 'rate' => 7600, 'growth' => 10.5],
                ['name' => 'Baner', 'pincode' => '411045', 'rate' => 10200, 'growth' => 11.8],
                ['name' => 'Viman Nagar', 'pincode' => '411014', 'rate' => 11500, 'growth' => 9.4],
                ['name' => 'Kothrud', 'pincode' => '411038', 'rate' => 12800, 'growth' => 8.2],
            ],
            // Hyderabad
            'Hyderabad' => [
                ['name' => 'Gachibowli', 'pincode' => '500032', 'rate' => 9800, 'growth' => 16.5],
                ['name' => 'HITEC City', 'pincode' => '500081', 'rate' => 11200, 'growth' => 15.0],
                ['name' => 'Kondapur', 'pincode' => '500084', 'rate' => 8700, 'growth' => 13.4],
                ['name' => 'Madhapur', 'pincode' => '500081', 'rate' => 10500, 'growth' => 14.2],
                ['name' => 'Kukatpally', 'pincode' => '500072', 'rate' => 7400, 'growth' => 10.8],
                ['name' => 'Banjara Hills', 'pincode' => '500034', 'rate' => 16500, 'growth' => 8.5],
                ['name' => 'Jubilee Hills', 'pincode' => '500033', 'rate' => 18000, 'growth' => 9.0],
            ],
            // Ahmedabad
            'Ahmedabad' => [
                ['name' => 'Jagatpur', 'pincode' => '382470', 'rate' => 5100, 'growth' => 13.2],
                ['name' => 'Chandkheda', 'pincode' => '382424', 'rate' => 4800, 'growth' => 11.5],
                ['name' => 'Zundal', 'pincode' => '382421', 'rate' => 4400, 'growth' => 14.8],
                ['name' => 'Tragad', 'pincode' => '382470', 'rate' => 4600, 'growth' => 12.0],
                ['name' => 'Vaishnodevi Circle', 'pincode' => '382421', 'rate' => 6200, 'growth' => 16.5],
                ['name' => 'Gota', 'pincode' => '382481', 'rate' => 5300, 'growth' => 12.8],
                ['name' => 'Charodi', 'pincode' => '382481', 'rate' => 4900, 'growth' => 13.0],
                ['name' => 'New Ranip', 'pincode' => '382480', 'rate' => 4700, 'growth' => 10.5],
                ['name' => 'New CG Road', 'pincode' => '382424', 'rate' => 5200, 'growth' => 11.2],
                ['name' => 'Nigam Nagar', 'pincode' => '382424', 'rate' => 4500, 'growth' => 9.8],
                ['name' => 'Chainpur', 'pincode' => '382481', 'rate' => 4300, 'growth' => 10.0],
                ['name' => 'D Cabin', 'pincode' => '380019', 'rate' => 4200, 'growth' => 9.2],
                ['name' => 'Janta Nagar', 'pincode' => '382424', 'rate' => 4400, 'growth' => 9.5],
                ['name' => 'SG Highway', 'pincode' => '380054', 'rate' => 7200, 'growth' => 13.8],
                ['name' => 'Ranip', 'pincode' => '382480', 'rate' => 4600, 'growth' => 9.6],
                ['name' => 'Sabarmati', 'pincode' => '380005', 'rate' => 5500, 'growth' => 10.0],
                ['name' => 'Chamunda Nagar', 'pincode' => '382424', 'rate' => 4100, 'growth' => 8.8],
                ['name' => 'Godrej Garden City', 'pincode' => '382470', 'rate' => 5600, 'growth' => 15.0],
                ['name' => 'Motera', 'pincode' => '380005', 'rate' => 6400, 'growth' => 14.2],
                ['name' => 'Chandlodiya', 'pincode' => '382481', 'rate' => 4800, 'growth' => 10.1],
                ['name' => 'Anand Nagar', 'pincode' => '380051', 'rate' => 6800, 'growth' => 11.5],
                ['name' => 'Koteshwar', 'pincode' => '380005', 'rate' => 6100, 'growth' => 13.4],
                ['name' => 'Khodiyar', 'pincode' => '382421', 'rate' => 4300, 'growth' => 12.2],
                ['name' => 'Bopal', 'pincode' => '380058', 'rate' => 5400, 'growth' => 15.2],
                ['name' => 'Prahlad Nagar', 'pincode' => '380015', 'rate' => 8500, 'growth' => 9.1],
                ['name' => 'Satellite', 'pincode' => '380015', 'rate' => 8200, 'growth' => 8.4],
                ['name' => 'Thaltej', 'pincode' => '380059', 'rate' => 8900, 'growth' => 10.4],
            ],
        ];

        foreach ($localitiesData as $cityName => $localities) {
            $city = City::where('name', $cityName)->first();
            if (! $city) {
                // Try fuzzy lookup
                $city = City::where('name', 'LIKE', "%{$cityName}%")->first();
            }

            if (! $city) {
                continue;
            }

            foreach ($localities as $loc) {
                Locality::updateOrCreate(
                    [
                        'city_id' => $city->id,
                        'name' => $loc['name'],
                    ],
                    [
                        'slug' => Str::slug($loc['name']),
                        'pincode' => $loc['pincode'],
                        'avg_price_per_sqft' => $loc['rate'],
                        'yoy_growth_percent' => $loc['growth'],
                    ]
                );
            }
        }
    }
}
