<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['state_id', 'country_id', 'name', 'slug', 'latitude', 'longitude', 'is_metro'])]
class City extends Model
{
    /**
     * Get the state this city belongs to.
     */
    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    /**
     * Get the country this city belongs to.
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Get the localities in this city.
     */
    public function localities(): HasMany
    {
        return $this->hasMany(Locality::class);
    }
}
