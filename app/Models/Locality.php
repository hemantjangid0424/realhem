<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['city_id', 'name', 'slug', 'pincode', 'latitude', 'longitude', 'avg_price_per_sqft', 'yoy_growth_percent'])]
class Locality extends Model
{
    /**
     * Get the city this locality belongs to.
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }
}
