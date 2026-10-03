/**
 * Centralized Property Constants & Helper Predicates
 */

export const PROPERTY_FOR = {
    SELL: 'Sell',
    RENT: 'Rent',
    PG: 'PG',
};

export const PROPERTY_INTENT_LABELS = {
    [PROPERTY_FOR.SELL]: 'Sale',
    [PROPERTY_FOR.RENT]: 'Rent',
    [PROPERTY_FOR.PG]: 'PG / Co-Living',
};

export const PROPERTY_TYPES = {
    // Residential
    RESIDENTIAL_APARTMENT: 'Residential Apartment',
    INDEPENDENT_HOUSE_VILLA: 'Independent House/Villa',
    BUILDER_FLOOR: 'Builder Floor',
    STUDIO_APARTMENT: '1 RK/ Studio Apartment',
    PENTHOUSE: 'Penthouse',
    VILLA: 'Villa',
    APARTMENT: 'Apartment',

    // Commercial
    COMMERCIAL_OFFICE: 'Commercial Office',
    COMMERCIAL_SHOP: 'Commercial Shop',
    COMMERCIAL_SHOWROOM: 'Commercial Showroom',

    // Plot / Land
    RESIDENTIAL_PLOT: 'Residential Plot',
    COMMERCIAL_LAND: 'Commercial Land',
    AGRICULTURAL_LAND: 'Agricultural Land',
    PLOT: 'Plot',
    LAND: 'Land',
};

export const RESIDENTIAL_PROPERTY_TYPES = [
    PROPERTY_TYPES.RESIDENTIAL_APARTMENT,
    PROPERTY_TYPES.INDEPENDENT_HOUSE_VILLA,
    PROPERTY_TYPES.BUILDER_FLOOR,
    PROPERTY_TYPES.STUDIO_APARTMENT,
    PROPERTY_TYPES.PENTHOUSE,
    PROPERTY_TYPES.VILLA,
    PROPERTY_TYPES.APARTMENT,
];

export const COMMERCIAL_PROPERTY_TYPES = [
    PROPERTY_TYPES.COMMERCIAL_OFFICE,
    PROPERTY_TYPES.COMMERCIAL_SHOP,
    PROPERTY_TYPES.COMMERCIAL_SHOWROOM,
];

export const PLOT_PROPERTY_TYPES = [
    PROPERTY_TYPES.RESIDENTIAL_PLOT,
    PROPERTY_TYPES.COMMERCIAL_LAND,
    PROPERTY_TYPES.AGRICULTURAL_LAND,
    PROPERTY_TYPES.PLOT,
    PROPERTY_TYPES.LAND,
];

/**
 * Predicate to determine if property type is residential.
 */
export function isResidentialType(type) {
    if (!type) return false;
    return RESIDENTIAL_PROPERTY_TYPES.includes(type);
}

/**
 * Predicate to determine if property type is commercial.
 */
export function isCommercialType(type) {
    if (!type) return false;
    return COMMERCIAL_PROPERTY_TYPES.includes(type);
}

/**
 * Predicate to determine if property type is plot or land.
 */
export function isPlotType(type) {
    if (!type) return false;
    return PLOT_PROPERTY_TYPES.includes(type);
}

/**
 * Get display label for transaction intent ('Sale' for 'Sell', 'Rent' for 'Rent').
 */
export function getPropertyIntentLabel(propertyFor) {
    if (!propertyFor) return 'Sale';
    return PROPERTY_INTENT_LABELS[propertyFor] || propertyFor;
}
