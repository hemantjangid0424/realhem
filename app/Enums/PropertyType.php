<?php

namespace App\Enums;

enum PropertyType: string
{
    // Residential
    case ResidentialApartment = 'Residential Apartment';
    case IndependentHouseVilla = 'Independent House/Villa';
    case BuilderFloor = 'Builder Floor';
    case StudioApartment = '1 RK/ Studio Apartment';
    case Penthouse = 'Penthouse';

    // Plots & Land
    case ResidentialPlot = 'Residential Plot';
    case CommercialLand = 'Commercial Land';
    case AgriculturalLand = 'Agricultural Land';

    // Commercial
    case CommercialOffice = 'Commercial Office';
    case CommercialShop = 'Commercial Shop';
    case CommercialShowroom = 'Commercial Showroom';

    /**
     * Check if this property type is residential.
     */
    public function isResidential(): bool
    {
        return in_array($this, [
            self::ResidentialApartment,
            self::IndependentHouseVilla,
            self::BuilderFloor,
            self::StudioApartment,
            self::Penthouse,
        ], true);
    }

    /**
     * Check if this property type is commercial.
     */
    public function isCommercial(): bool
    {
        return in_array($this, [
            self::CommercialOffice,
            self::CommercialShop,
            self::CommercialShowroom,
        ], true);
    }

    /**
     * Check if this property type is plot or land.
     */
    public function isPlot(): bool
    {
        return in_array($this, [
            self::ResidentialPlot,
            self::CommercialLand,
            self::AgriculturalLand,
        ], true);
    }

    /**
     * Check if a given string represents a residential property type.
     */
    public static function isResidentialType(?string $type): bool
    {
        if (! $type) {
            return false;
        }

        $case = self::tryFrom($type);

        return $case ? $case->isResidential() : true;
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
