<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'place_id',
    'query_hash',
    'raw_query',
    'normalized_query',
    'name',
    'formatted_address',
    'locality',
    'sub_locality',
    'city',
    'state',
    'pincode',
    'latitude',
    'longitude',
    'types',
    'raw_response',
    'hit_count',
    'provider',
])]
class GooglePlace extends Model
{
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'types' => 'array',
            'raw_response' => 'array',
            'hit_count' => 'integer',
        ];
    }
}
