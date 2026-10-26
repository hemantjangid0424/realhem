<?php

namespace App\Services;

use App\Enums\PropertyFor;
use App\Enums\PropertyType;
use App\Models\Property;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class PropertyService
{
    /**
     * Create a new property listing.
     *
     * @param  array<string, mixed>  $validated
     */
    public function create(User $user, array $validated): Property
    {
        $carpetArea = (int) $validated['carpet_area'];
        $expectedPrice = (int) $validated['expected_price'];
        $pricePerSqft = $carpetArea > 0 ? (int) round($expectedPrice / $carpetArea) : null;

        $title = $this->resolveTitle($validated, $carpetArea);
        $userType = $this->resolveUserType($user, $validated['user_type'] ?? null);

        $resolvedLocation = GooglePlacesService::resolve(
            city: $validated['city'],
            locality: $validated['locality'],
            projectName: $validated['project_name'] ?? null,
            subLocality: $validated['sub_locality'] ?? null,
            address: $validated['address'] ?? null,
            landmark: $validated['landmark'] ?? null,
            pincode: $validated['pincode'] ?? null,
            latitude: isset($validated['latitude']) ? (float) $validated['latitude'] : null,
            longitude: isset($validated['longitude']) ? (float) $validated['longitude'] : null,
        );

        return Property::create([
            'user_id' => $user->id,
            'user_type' => $userType,
            'property_for' => $validated['property_for'],
            'property_type' => $validated['property_type'],
            'title' => $title,
            'description' => $validated['description'] ?? null,
            'project_name' => $validated['project_name'] ?? null,
            'city' => $validated['city'],
            'state' => $validated['state'] ?? ($resolvedLocation['state'] ?? null),
            'locality' => $validated['locality'],
            'sub_locality' => $validated['sub_locality'] ?? null,
            'address' => $validated['address'] ?? null,
            'landmark' => $validated['landmark'] ?? null,
            'pincode' => $resolvedLocation['pincode'],
            'latitude' => $resolvedLocation['latitude'],
            'longitude' => $resolvedLocation['longitude'],
            'bedrooms' => (int) ($validated['bedrooms'] ?? 0),
            'bathrooms' => (int) ($validated['bathrooms'] ?? 0),
            'balconies' => isset($validated['balconies']) ? (int) $validated['balconies'] : 0,
            'carpet_area' => $carpetArea,
            'super_builtup_area' => isset($validated['super_builtup_area']) ? (int) $validated['super_builtup_area'] : (int) round($carpetArea * 1.25),
            'furnishing_status' => $validated['furnishing_status'] ?? 'Unfurnished',
            'floor_no' => $validated['floor_no'] ?? '1',
            'total_floors' => $validated['total_floors'] ?? 1,
            'facing' => $validated['facing'] ?? 'East',
            'construction_status' => $validated['construction_status'] ?? 'Ready to Move',
            'expected_price' => $expectedPrice,
            'price_per_sqft' => $pricePerSqft,
            'maintenance_charge' => isset($validated['maintenance_charge']) ? (int) $validated['maintenance_charge'] : 0,
            'price_negotiable' => (bool) ($validated['price_negotiable'] ?? false),
            'amenities' => $validated['amenities'] ?? [],
            'photos' => $validated['photos'] ?? [],
            'videos' => $validated['videos'] ?? [],
            'status' => 'active',
            'is_verified' => true,
            'is_featured' => false,
        ]);
    }

    /**
     * Update an existing property listing.
     *
     * @param  array<string, mixed>  $validated
     */
    public function update(Property $property, array $validated): Property
    {
        $carpetArea = (int) $validated['carpet_area'];
        $expectedPrice = (int) $validated['expected_price'];
        $pricePerSqft = $carpetArea > 0 ? (int) round($expectedPrice / $carpetArea) : null;

        $localityChanged = $validated['locality'] !== $property->locality || $validated['city'] !== $property->city;

        $resolvedLocation = GooglePlacesService::resolve(
            city: $validated['city'],
            locality: $validated['locality'],
            projectName: $validated['project_name'] ?? ($localityChanged ? null : $property->project_name),
            subLocality: $validated['sub_locality'] ?? ($localityChanged ? null : $property->sub_locality),
            address: $validated['address'] ?? ($localityChanged ? null : $property->address),
            landmark: $validated['landmark'] ?? ($localityChanged ? null : $property->landmark),
            pincode: $validated['pincode'] ?? ($localityChanged ? null : $property->pincode),
            latitude: isset($validated['latitude']) ? (float) $validated['latitude'] : ($localityChanged ? null : $property->latitude),
            longitude: isset($validated['longitude']) ? (float) $validated['longitude'] : ($localityChanged ? null : $property->longitude),
        );

        $property->update(array_merge($validated, [
            'price_per_sqft' => $pricePerSqft,
            'state' => $validated['state'] ?? ($resolvedLocation['state'] ?? $property->state),
            'pincode' => $resolvedLocation['pincode'],
            'latitude' => $resolvedLocation['latitude'],
            'longitude' => $resolvedLocation['longitude'],
        ]));

        $this->clearCache($property);

        return $property->fresh();
    }

    /**
     * Update property status (e.g. active, sold, rented, pending_approval).
     */
    public function updateStatus(Property $property, string $status): Property
    {
        $property->update(['status' => $status]);
        $this->clearCache($property);

        return $property->fresh();
    }

    /**
     * Delete a property listing and clear relevant cache entries.
     */
    public function delete(Property $property): void
    {
        $this->clearCache($property);
        $property->delete();
    }

    /**
     * Clear cached representations of the property.
     */
    public function clearCache(Property $property): void
    {
        Cache::forget("prop_show_{$property->id}");
    }

    /**
     * Auto-generate a descriptive SEO title when not explicitly provided.
     *
     * @param  array<string, mixed>  $validated
     */
    protected function resolveTitle(array $validated, int $carpetArea): string
    {
        $title = $validated['title'] ?? null;
        if (! empty($title)) {
            return $title;
        }

        $forEnum = PropertyFor::tryFrom($validated['property_for']) ?? PropertyFor::Sell;
        $forLabel = $forEnum->label();
        $bedrooms = (int) ($validated['bedrooms'] ?? 0);

        if (PropertyType::isResidentialType($validated['property_type']) && $bedrooms > 0) {
            return "{$bedrooms} BHK {$validated['property_type']} for {$forLabel} in {$validated['locality']}, {$validated['city']}";
        }

        return "{$carpetArea} sq.ft. {$validated['property_type']} for {$forLabel} in {$validated['locality']}, {$validated['city']}";
    }

    /**
     * Determine user poster type based on user role when missing.
     */
    protected function resolveUserType(User $user, ?string $userType): string
    {
        if (! empty($userType)) {
            return $userType;
        }

        return match ($user->role) {
            'agent' => 'Agent',
            'builder' => 'Builder',
            default => 'Owner',
        };
    }
}
