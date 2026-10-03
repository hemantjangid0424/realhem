<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['id', 'country_id', 'name', 'state_code', 'type'])]
class State extends Model
{
    /**
     * Get the country this state belongs to.
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Get the cities belonging to this state.
     */
    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }
}
