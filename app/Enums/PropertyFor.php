<?php

namespace App\Enums;

enum PropertyFor: string
{
    case Sell = 'Sell';
    case Rent = 'Rent';
    case Pg = 'PG';
    case Commercial = 'Commercial';

    /**
     * Map flexible query parameters ('buy', 'sale', 'rent', 'lease', 'pg') to standard enum case.
     */
    public static function tryFromQuery(?string $query): ?self
    {
        if (! $query) {
            return null;
        }

        return match (strtolower(trim($query))) {
            'buy', 'sale', 'sell' => self::Sell,
            'rent', 'lease' => self::Rent,
            'commercial' => self::Commercial,
            'pg', 'co-living', 'coliving' => self::Pg,
            default => self::tryFrom($query),
        };
    }

    /**
     * Display label for listing titles and descriptions.
     */
    public function label(): string
    {
        return match ($this) {
            self::Sell => 'Sale',
            self::Rent => 'Rent',
            self::Pg => 'PG / Co-Living',
        };
    }

    /**
     * Get all values for validation rules.
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
