<?php

namespace App\Services;

use App\Models\City;
use App\Models\Locality;

class GeocodingService
{
    /**
     * Curated Indian real estate geographic dictionary mapping localities & landmarks
     * to precision coordinates (lat, lng) and 6-digit postal pincodes.
     *
     * @var array<string, array{lat: float, lng: float, pincode: string}>
     */
    protected const LOCALITY_COORDINATES = [
        // Ahmedabad
        'vastral' => ['lat' => 23.0039, 'lng' => 72.6517, 'pincode' => '382418'],
        'the metropark' => ['lat' => 23.0045, 'lng' => 72.6525, 'pincode' => '382418'],
        'prahlad nagar' => ['lat' => 23.0124, 'lng' => 72.5076, 'pincode' => '380015'],
        'satellite' => ['lat' => 23.0264, 'lng' => 72.5244, 'pincode' => '380015'],
        'anand nagar' => ['lat' => 23.0165, 'lng' => 72.5165, 'pincode' => '380051'],
        'bopal' => ['lat' => 23.0336, 'lng' => 72.4646, 'pincode' => '380058'],
        'south bopal' => ['lat' => 23.0210, 'lng' => 72.4680, 'pincode' => '380058'],
        'shela' => ['lat' => 23.0120, 'lng' => 72.4540, 'pincode' => '380058'],
        'jagatpur' => ['lat' => 23.1042, 'lng' => 72.5489, 'pincode' => '382470'],
        'godrej garden city' => ['lat' => 23.0988, 'lng' => 72.5482, 'pincode' => '382470'],
        'gota' => ['lat' => 23.0977, 'lng' => 72.5369, 'pincode' => '382481'],
        'charodi' => ['lat' => 23.1250, 'lng' => 72.5420, 'pincode' => '382481'],
        'chandkheda' => ['lat' => 23.1118, 'lng' => 72.5833, 'pincode' => '382424'],
        'new cg road' => ['lat' => 23.1080, 'lng' => 72.5860, 'pincode' => '382424'],
        'motera' => ['lat' => 23.0934, 'lng' => 72.5978, 'pincode' => '380005'],
        'sabarmati' => ['lat' => 23.0825, 'lng' => 72.5932, 'pincode' => '380005'],
        'ranip' => ['lat' => 23.0784, 'lng' => 72.5746, 'pincode' => '382480'],
        'new ranip' => ['lat' => 23.0890, 'lng' => 72.5710, 'pincode' => '382480'],
        'thaltej' => ['lat' => 23.0504, 'lng' => 72.5165, 'pincode' => '380059'],
        'bodakdev' => ['lat' => 23.0392, 'lng' => 72.5130, 'pincode' => '380054'],
        'sg highway' => ['lat' => 23.0754, 'lng' => 72.5076, 'pincode' => '380054'],
        'science city' => ['lat' => 23.0805, 'lng' => 72.5030, 'pincode' => '380060'],
        'jodhpur' => ['lat' => 23.0225, 'lng' => 72.5284, 'pincode' => '380015'],
        'trinay anagh' => ['lat' => 23.0228, 'lng' => 72.5289, 'pincode' => '380015'],
        'vaishnodevi circle' => ['lat' => 23.1360, 'lng' => 72.5460, 'pincode' => '382421'],
        'zundal' => ['lat' => 23.1412, 'lng' => 72.5724, 'pincode' => '382421'],
        'tragad' => ['lat' => 23.1215, 'lng' => 72.5654, 'pincode' => '382470'],
        'koteshwar' => ['lat' => 23.1025, 'lng' => 72.6050, 'pincode' => '380005'],
        'nikol' => ['lat' => 23.0489, 'lng' => 72.6718, 'pincode' => '382350'],
        'naroda' => ['lat' => 23.0680, 'lng' => 72.6580, 'pincode' => '382330'],
        'maninagar' => ['lat' => 22.9978, 'lng' => 72.6033, 'pincode' => '380008'],
        'navrangpura' => ['lat' => 23.0373, 'lng' => 72.5614, 'pincode' => '380009'],
        'paldi' => ['lat' => 23.0134, 'lng' => 72.5627, 'pincode' => '380007'],

        // Noida & Greater Noida
        'sector 150' => ['lat' => 28.4385, 'lng' => 77.4983, 'pincode' => '201310'],
        'sector 62' => ['lat' => 28.6258, 'lng' => 77.3654, 'pincode' => '201309'],
        'sector 137' => ['lat' => 28.5147, 'lng' => 77.4069, 'pincode' => '201305'],
        'sector 75' => ['lat' => 28.5714, 'lng' => 77.3822, 'pincode' => '201307'],
        'sector 78' => ['lat' => 28.5680, 'lng' => 77.3890, 'pincode' => '201307'],
        'sector 128' => ['lat' => 28.5320, 'lng' => 77.3780, 'pincode' => '201304'],
        'noida extension' => ['lat' => 28.5996, 'lng' => 77.4475, 'pincode' => '201306'],
        'pari chowk' => ['lat' => 28.4695, 'lng' => 77.5108, 'pincode' => '201310'],
        'alpha 1' => ['lat' => 28.4720, 'lng' => 77.5020, 'pincode' => '201308'],
        'techzone 4' => ['lat' => 28.5850, 'lng' => 77.4410, 'pincode' => '201306'],

        // Gurugram
        'golf course road' => ['lat' => 28.4595, 'lng' => 77.0988, 'pincode' => '122002'],
        'golf course extension road' => ['lat' => 28.4112, 'lng' => 77.0825, 'pincode' => '122018'],
        'dlf phase 5' => ['lat' => 28.4542, 'lng' => 77.0910, 'pincode' => '122009'],
        'sohna road' => ['lat' => 28.3846, 'lng' => 77.0425, 'pincode' => '122018'],
        'dwarka expressway' => ['lat' => 28.5028, 'lng' => 76.9942, 'pincode' => '122017'],
        'sector 57' => ['lat' => 28.4285, 'lng' => 77.0780, 'pincode' => '122003'],

        // Delhi
        'dwarka' => ['lat' => 28.5921, 'lng' => 77.0460, 'pincode' => '110075'],
        'connaught place' => ['lat' => 28.6304, 'lng' => 77.2177, 'pincode' => '110001'],
        'saket' => ['lat' => 28.5244, 'lng' => 77.2167, 'pincode' => '110017'],
        'vasant kunj' => ['lat' => 28.5298, 'lng' => 77.1511, 'pincode' => '110070'],
        'rohini' => ['lat' => 28.7495, 'lng' => 77.0659, 'pincode' => '110085'],

        // Mumbai & MMR
        'bandra west' => ['lat' => 19.0596, 'lng' => 72.8295, 'pincode' => '400050'],
        'bandra east' => ['lat' => 19.0664, 'lng' => 72.8687, 'pincode' => '400051'],
        'andheri west' => ['lat' => 19.1363, 'lng' => 72.8277, 'pincode' => '400058'],
        'andheri east' => ['lat' => 19.1136, 'lng' => 72.8697, 'pincode' => '400069'],
        'juhu' => ['lat' => 19.1075, 'lng' => 72.8263, 'pincode' => '400049'],
        'powai' => ['lat' => 19.1176, 'lng' => 72.9060, 'pincode' => '400076'],
        'worli' => ['lat' => 19.0178, 'lng' => 72.8178, 'pincode' => '400018'],
        'goregaon east' => ['lat' => 19.1663, 'lng' => 72.8593, 'pincode' => '400063'],
        'borivali west' => ['lat' => 19.2307, 'lng' => 72.8567, 'pincode' => '400092'],
        'thane west' => ['lat' => 19.2183, 'lng' => 72.9781, 'pincode' => '400601'],
        'vashi' => ['lat' => 19.0771, 'lng' => 72.9986, 'pincode' => '400703'],

        // Bengaluru
        'whitefield' => ['lat' => 12.9698, 'lng' => 77.7500, 'pincode' => '560066'],
        'electronic city' => ['lat' => 12.8452, 'lng' => 77.6602, 'pincode' => '560100'],
        'indiranagar' => ['lat' => 12.9784, 'lng' => 77.6408, 'pincode' => '560038'],
        'koramangala' => ['lat' => 12.9352, 'lng' => 77.6245, 'pincode' => '560034'],
        'hsr layout' => ['lat' => 12.9121, 'lng' => 77.6446, 'pincode' => '560102'],
        'bellandur' => ['lat' => 12.9304, 'lng' => 77.6784, 'pincode' => '560103'],
        'sarjapur road' => ['lat' => 12.9116, 'lng' => 77.6744, 'pincode' => '560035'],
        'hebbal' => ['lat' => 13.0358, 'lng' => 77.5970, 'pincode' => '560024'],
        'yelahanka' => ['lat' => 13.1007, 'lng' => 77.5963, 'pincode' => '560064'],

        // Pune
        'hinjawadi' => ['lat' => 18.5913, 'lng' => 73.7389, 'pincode' => '411057'],
        'kharadi' => ['lat' => 18.5516, 'lng' => 73.9348, 'pincode' => '411014'],
        'wakad' => ['lat' => 18.5987, 'lng' => 73.7686, 'pincode' => '411057'],
        'baner' => ['lat' => 18.5590, 'lng' => 73.7868, 'pincode' => '411045'],
        'viman nagar' => ['lat' => 18.5679, 'lng' => 73.9143, 'pincode' => '411014'],
        'kothrud' => ['lat' => 18.5074, 'lng' => 73.8077, 'pincode' => '411038'],
        'hadapsar' => ['lat' => 18.5089, 'lng' => 73.9259, 'pincode' => '411028'],

        // Hyderabad
        'gachibowli' => ['lat' => 17.4401, 'lng' => 78.3489, 'pincode' => '500032'],
        'hitec city' => ['lat' => 17.4435, 'lng' => 78.3772, 'pincode' => '500081'],
        'kondapur' => ['lat' => 17.4699, 'lng' => 78.3578, 'pincode' => '500084'],
        'madhapur' => ['lat' => 17.4483, 'lng' => 78.3915, 'pincode' => '500081'],
        'kukatpally' => ['lat' => 17.4849, 'lng' => 78.4138, 'pincode' => '500072'],
        'banjara hills' => ['lat' => 17.4156, 'lng' => 78.4350, 'pincode' => '500034'],
        'jubilee hills' => ['lat' => 17.4319, 'lng' => 78.4073, 'pincode' => '500033'],

        // Chennai
        'omr' => ['lat' => 12.9352, 'lng' => 80.2298, 'pincode' => '600097'],
        'velachery' => ['lat' => 12.9815, 'lng' => 80.2180, 'pincode' => '600042'],
        'anna nagar' => ['lat' => 13.0850, 'lng' => 80.2101, 'pincode' => '600040'],

        // Kolkata
        'new town' => ['lat' => 22.5867, 'lng' => 88.4756, 'pincode' => '700156'],
        'salt lake' => ['lat' => 22.5710, 'lng' => 88.4168, 'pincode' => '700091'],
    ];

    /**
     * City center fallback coordinates & default pincodes.
     *
     * @var array<string, array{lat: float, lng: float, pincode: string}>
     */
    protected const CITY_COORDINATES = [
        'ahmedabad' => ['lat' => 23.0225, 'lng' => 72.5714, 'pincode' => '380001'],
        'gandhinagar' => ['lat' => 23.2156, 'lng' => 72.6369, 'pincode' => '382010'],
        'mumbai' => ['lat' => 19.0760, 'lng' => 72.8777, 'pincode' => '400001'],
        'delhi ncr' => ['lat' => 28.6139, 'lng' => 77.2090, 'pincode' => '110001'],
        'delhi' => ['lat' => 28.6139, 'lng' => 77.2090, 'pincode' => '110001'],
        'noida' => ['lat' => 28.5355, 'lng' => 77.3910, 'pincode' => '201301'],
        'greater noida' => ['lat' => 28.4744, 'lng' => 77.5040, 'pincode' => '201310'],
        'gurugram' => ['lat' => 28.4595, 'lng' => 77.0266, 'pincode' => '122001'],
        'bangalore' => ['lat' => 12.9716, 'lng' => 77.5946, 'pincode' => '560001'],
        'bengaluru' => ['lat' => 12.9716, 'lng' => 77.5946, 'pincode' => '560001'],
        'pune' => ['lat' => 18.5204, 'lng' => 73.8567, 'pincode' => '411001'],
        'hyderabad' => ['lat' => 17.3850, 'lng' => 78.4867, 'pincode' => '500001'],
        'chennai' => ['lat' => 13.0827, 'lng' => 80.2707, 'pincode' => '600001'],
        'kolkata' => ['lat' => 22.5726, 'lng' => 88.3639, 'pincode' => '700001'],
        'surat' => ['lat' => 21.1702, 'lng' => 72.8311, 'pincode' => '395003'],
        'vadodara' => ['lat' => 22.3072, 'lng' => 73.1812, 'pincode' => '390001'],
        'jaipur' => ['lat' => 26.9124, 'lng' => 75.7873, 'pincode' => '302001'],
        'chandigarh' => ['lat' => 30.7333, 'lng' => 76.7794, 'pincode' => '160017'],
        'indore' => ['lat' => 22.7196, 'lng' => 75.8577, 'pincode' => '452001'],
        'lucknow' => ['lat' => 26.8467, 'lng' => 80.9462, 'pincode' => '226001'],
    ];

    /**
     * Resolve latitude, longitude, and pincode based on property location attributes.
     *
     * @return array{latitude: ?float, longitude: ?float, pincode: ?string}
     */
    public static function resolve(
        ?string $city,
        ?string $locality,
        ?string $projectName = null,
        ?string $subLocality = null,
        ?string $address = null,
        ?string $landmark = null,
        ?string $pincode = null,
        ?float $latitude = null,
        ?float $longitude = null,
    ): array {
        $resolvedPincode = $pincode ? trim($pincode) : null;
        $resolvedLat = $latitude !== null && $latitude != 0 ? (float) $latitude : null;
        $resolvedLng = $longitude !== null && $longitude != 0 ? (float) $longitude : null;

        // If all 3 are provided and valid, return immediately
        if ($resolvedPincode && $resolvedLat !== null && $resolvedLng !== null) {
            return [
                'latitude' => $resolvedLat,
                'longitude' => $resolvedLng,
                'pincode' => $resolvedPincode,
            ];
        }

        // 1. Try extracting 6-digit Indian pincode from address strings
        if (! $resolvedPincode) {
            $searchTexts = [$address, $landmark, $subLocality, $projectName, $locality];
            foreach ($searchTexts as $text) {
                if ($text && preg_match('/\b[1-9][0-9]{5}\b/', $text, $matches)) {
                    $resolvedPincode = $matches[0];
                    break;
                }
            }
        }

        // 2. Try lookup in database Locality table
        if ($locality) {
            $locQuery = Locality::query()->where('name', $locality);
            if ($city) {
                $locQuery->whereHas('city', function ($q) use ($city) {
                    $q->where('name', $city)
                        ->orWhere('name', 'LIKE', "%{$city}%");
                });
            }
            $dbLocality = $locQuery->first() ?? Locality::where('name', 'LIKE', "%{$locality}%")->first();

            if ($dbLocality) {
                if (! $resolvedPincode && $dbLocality->pincode) {
                    $resolvedPincode = $dbLocality->pincode;
                }
                if ($resolvedLat === null && $dbLocality->latitude) {
                    $resolvedLat = (float) $dbLocality->latitude;
                }
                if ($resolvedLng === null && $dbLocality->longitude) {
                    $resolvedLng = (float) $dbLocality->longitude;
                }
            }
        }

        // 3. Try lookup in curated real estate coordinates dictionary
        $targets = array_filter([$projectName, $locality, $subLocality, $landmark]);
        foreach ($targets as $target) {
            $key = strtolower(trim($target));
            // Direct key match
            if (isset(self::LOCALITY_COORDINATES[$key])) {
                $match = self::LOCALITY_COORDINATES[$key];
                $resolvedLat ??= $match['lat'];
                $resolvedLng ??= $match['lng'];
                $resolvedPincode ??= $match['pincode'];
                break;
            }

            // Substring search in dictionary
            foreach (self::LOCALITY_COORDINATES as $dictKey => $match) {
                if (str_contains($key, $dictKey) || str_contains($dictKey, $key)) {
                    $resolvedLat ??= $match['lat'];
                    $resolvedLng ??= $match['lng'];
                    $resolvedPincode ??= $match['pincode'];
                    break 2;
                }
            }
        }

        // 4. Try lookup in database City table or dictionary fallback
        $cityKey = strtolower(trim($city ?? ''));
        if ($resolvedLat === null || $resolvedLng === null || ! $resolvedPincode) {
            if (isset(self::CITY_COORDINATES[$cityKey])) {
                $cityMatch = self::CITY_COORDINATES[$cityKey];
                $resolvedLat ??= $cityMatch['lat'];
                $resolvedLng ??= $cityMatch['lng'];
                $resolvedPincode ??= $cityMatch['pincode'];
            } else {
                $dbCity = City::where('name', $city)->orWhere('name', 'LIKE', "%{$city}%")->first();
                if ($dbCity) {
                    if ($resolvedLat === null && $dbCity->latitude) {
                        $resolvedLat = (float) $dbCity->latitude;
                    }
                    if ($resolvedLng === null && $dbCity->longitude) {
                        $resolvedLng = (float) $dbCity->longitude;
                    }
                }
            }
        }

        // 5. Ultimate fallback if city not matched (Default to Ahmedabad real estate hub)
        $resolvedLat ??= 23.0225;
        $resolvedLng ??= 72.5714;
        $resolvedPincode ??= '380001';

        return [
            'latitude' => $resolvedLat,
            'longitude' => $resolvedLng,
            'pincode' => $resolvedPincode,
        ];
    }
}
