<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['id', 'name', 'iso2', 'iso3', 'phonecode', 'currency'])]
class Country extends Model
{
    /**
     * Get the states belonging to this country.
     */
    public function states(): HasMany
    {
        return $this->hasMany(State::class);
    }

    /**
     * Get the cities belonging to this country.
     */
    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }
}
