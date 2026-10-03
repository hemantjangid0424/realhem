<?php

namespace App\Models;

use App\Enums\PropertyFor;
use Database\Factories\PropertyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable([
    'user_id',
    'user_type',
    'property_for',
    'property_type',
    'title',
    'slug',
    'description',
    'project_name',
    'city',
    'locality',
    'sub_locality',
    'address',
    'landmark',
    'latitude',
    'longitude',
    'bedrooms',
    'bathrooms',
    'balconies',
    'carpet_area',
    'super_builtup_area',
    'furnishing_status',
    'floor_no',
    'total_floors',
    'facing',
    'construction_status',
    'expected_price',
    'price_per_sqft',
    'maintenance_charge',
    'price_negotiable',
    'amenities',
    'photos',
    'status',
    'is_verified',
    'is_featured',
])]
class Property extends Model
{
    /** @use HasFactory<PropertyFactory> */
    use HasFactory;

    /**
     * Auto-generate a unique SEO slug on creation (99acres-style).
     * Format: {bedrooms}-bhk-{type}-for-{sale|rent}-in-{locality}-{city}-spld-{base36-id}
     */
    protected static function booted(): void
    {
        static::created(function (Property $property) {
            if (empty($property->slug)) {
                $propertyFor = $property->property_for instanceof PropertyFor
                    ? $property->property_for
                    : PropertyFor::tryFromQuery($property->property_for);

                $forLabel = $propertyFor ? strtolower($propertyFor->label()) : 'sale';

                $base = Str::slug(implode(' ', array_filter([
                    $property->bedrooms ? $property->bedrooms.' bhk' : null,
                    $property->property_type,
                    'for',
                    $forLabel,
                    'in',
                    $property->locality,
                    $property->city,
                ])));

                $suffix = 'spld-'.strtoupper(base_convert($property->id + 1000000, 10, 36));

                $property->updateQuietly(['slug' => $base.'-'.$suffix]);
            }
        });
    }

    /**
     * Get the user who posted the property.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'property_for' => PropertyFor::class,
            'amenities' => 'array',
            'photos' => 'array',
            'price_negotiable' => 'boolean',
            'is_verified' => 'boolean',
            'is_featured' => 'boolean',
            'expected_price' => 'integer',
            'carpet_area' => 'integer',
            'super_builtup_area' => 'integer',
        ];
    }
}
