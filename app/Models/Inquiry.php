<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inquiry extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'name',
        'email',
        'phone',
        'subject',
        'message',
        'rating',
        'property_id',
        'status',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'rating' => 'integer',
    ];

    /**
     * Associated property (if inquiry is regarding a specific listing).
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}
