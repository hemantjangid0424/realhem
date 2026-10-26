<?php

namespace App\Filters;

use App\Enums\PropertyFor;
use App\Models\Property;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class PropertyFilter
{
    /**
     * Apply all search and criteria filters to the property query builder.
     *
     * @param  Builder<Property>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<Property>
     */
    public function apply(Builder $query, array $filters): Builder
    {
        $this->filterCity($query, $filters['city'] ?? null);
        $this->filterTypeAndIntent($query, $filters['type'] ?? null, $filters['property_for'] ?? null);
        $this->filterPropertyType($query, $filters['property_type'] ?? null);
        $this->filterBedrooms($query, $filters['bhk'] ?? null);
        $this->filterBudget($query, $filters['budget_min'] ?? null, $filters['budget_max'] ?? null);
        $this->filterConstructionStatus($query, $filters['construction_status'] ?? null);
        $this->filterTags($query, $filters['tags'] ?? null);
        $this->filterFurnishing($query, $filters['furnishing'] ?? null);
        $this->filterPostedBy($query, $filters['posted_by'] ?? null);
        $this->filterBathrooms($query, $filters['bathrooms'] ?? null);
        $this->filterFacing($query, $filters['facing'] ?? null);
        $this->filterNegotiable($query, ! empty($filters['negotiable_only']));
        $this->filterAmenities($query, $filters['amenities'] ?? null);
        $this->filterSearch($query, $filters['search'] ?? ($filters['keyword'] ?? null));
        $this->filterLocalities($query, $filters['localities'] ?? null, $filters['locality'] ?? null);
        $this->filterVerified($query, ! empty($filters['verified_only']));
        $this->filterFeatured($query, ! empty($filters['featured_only']));
        $this->applySorting($query, $filters['sort'] ?? ($filters['sort_by'] ?? 'relevance'));

        return $query;
    }

    /**
     * Filter by city.
     *
     * @param  Builder<Property>  $query
     */
    protected function filterCity(Builder $query, ?string $city): void
    {
        if ($city !== null && trim($city) !== '') {
            $query->where('city', trim($city));
        }
    }

    /**
     * Filter by category intent and commercial/plot classification.
     *
     * @param  Builder<Property>  $query
     */
    protected function filterTypeAndIntent(Builder $query, mixed $typeParam, mixed $propertyForParam): void
    {
        $typeParam = strtolower(trim((string) ($typeParam ?? '')));

        if ($typeParam === 'commercial') {
            $query->where(function (Builder $q) {
                $q->where('property_type', 'LIKE', 'Commercial%')
                    ->orWhere('property_type', 'LIKE', '%Office%')
                    ->orWhere('property_type', 'LIKE', '%Shop%')
                    ->orWhere('property_type', 'LIKE', '%Showroom%');
            });

            if ($propertyForParam) {
                $pFor = PropertyFor::tryFromQuery((string) $propertyForParam);
                if ($pFor && $pFor !== PropertyFor::Commercial) {
                    $query->where('property_for', $pFor->value);
                }
            }
        } elseif (in_array($typeParam, ['plots', 'plot', 'plots_land', 'land'], true)) {
            $query->where(function (Builder $q) {
                $q->where('property_type', 'LIKE', '%Plot%')
                    ->orWhere('property_type', 'LIKE', '%Land%');
            });

            if ($propertyForParam) {
                $pFor = PropertyFor::tryFromQuery((string) $propertyForParam);
                if ($pFor && $pFor !== PropertyFor::Commercial) {
                    $query->where('property_for', $pFor->value);
                }
            }
        } elseif (! empty($typeParam) && $typeParam !== 'all') {
            $propertyFor = PropertyFor::tryFromQuery($typeParam);
            if ($propertyFor && $propertyFor !== PropertyFor::Commercial) {
                $query->where('property_for', $propertyFor->value);
            }
        } elseif (! empty($propertyForParam)) {
            $propertyFor = PropertyFor::tryFromQuery((string) $propertyForParam);
            if ($propertyFor && $propertyFor !== PropertyFor::Commercial) {
                $query->where('property_for', $propertyFor->value);
            }
        }
    }

    /**
     * Filter by specific property types.
     *
     * @param  Builder<Property>  $query
     */
    protected function filterPropertyType(Builder $query, mixed $propertyType): void
    {
        if (empty($propertyType)) {
            return;
        }

        $types = is_array($propertyType) ? $propertyType : explode(',', (string) $propertyType);
        $types = array_values(array_filter(array_map('trim', $types)));

        if (! empty($types)) {
            $query->whereIn('property_type', $types);
        }
    }

    /**
     * Filter by bedrooms count (BHK).
     *
     * @param  Builder<Property>  $query
     */
    protected function filterBedrooms(Builder $query, mixed $bhk): void
    {
        if (empty($bhk)) {
            return;
        }

        $bhks = is_array($bhk) ? $bhk : explode(',', (string) $bhk);
        $bhks = array_values(array_filter(array_map('intval', $bhks)));

        if (! empty($bhks)) {
            $query->whereIn('bedrooms', $bhks);
        }
    }

    /**
     * Filter by minimum and maximum budget.
     *
     * @param  Builder<Property>  $query
     */
    protected function filterBudget(Builder $query, mixed $min, mixed $max): void
    {
        if ($min !== null && $min !== '') {
            $query->where('expected_price', '>=', (int) $min);
        }

        if ($max !== null && $max !== '') {
            $query->where('expected_price', '<=', (int) $max);
        }
    }

    /**
     * Filter by construction and possession status.
     *
     * @param  Builder<Property>  $query
     */
    protected function filterConstructionStatus(Builder $query, ?string $status): void
    {
        if (empty($status)) {
            return;
        }

        $normalized = strtolower(trim($status));
        if ($normalized === 'ready' || $normalized === 'ready_to_move') {
            $query->where('construction_status', 'like', '%ready%');
        } elseif ($normalized === 'under' || $normalized === 'under_construction') {
            $query->where(function (Builder $q) {
                $q->where('construction_status', 'like', '%under%')
                    ->orWhere('construction_status', 'like', '%construction%');
            });
        }
    }

    /**
     * Filter by quick tags.
     *
     * @param  Builder<Property>  $query
     */
    protected function filterTags(Builder $query, mixed $tags): void
    {
        if (empty($tags)) {
            return;
        }

        $tagList = is_array($tags) ? $tags : explode(',', (string) $tags);
        $tagList = array_values(array_filter(array_map('trim', $tagList)));

        foreach ($tagList as $tag) {
            match ($tag) {
                'new_launch' => $query->where(function (Builder $q) {
                    $q->where('construction_status', 'like', '%new%')
                        ->orWhere('title', 'like', '%new launch%')
                        ->orWhere('description', 'like', '%new launch%');
                }),
                'owner' => $query->where(function (Builder $q) {
                    $q->where('user_type', 'owner')
                        ->orWhereHas('user', fn ($u) => $u->where('role', 'user'));
                }),
                'verified' => $query->where('is_verified', true),
                'under_construction' => $query->where(function (Builder $q) {
                    $q->where('construction_status', 'like', '%under%')
                        ->orWhere('construction_status', 'like', '%construction%');
                }),
                'ready_to_move' => $query->where('construction_status', 'like', '%ready%'),
                'with_photos' => $query->whereNotNull('photos')->whereRaw("photos != '[]' AND photos != 'null'"),
                default => null,
            };
        }
    }

    /**
     * Filter by furnishing status.
     *
     * @param  Builder<Property>  $query
     */
    protected function filterFurnishing(Builder $query, mixed $furnishing): void
    {
        if (empty($furnishing)) {
            return;
        }

        $list = is_array($furnishing) ? $furnishing : explode(',', (string) $furnishing);
        $list = array_values(array_filter(array_map('trim', $list)));

        if (! empty($list)) {
            $query->whereIn('furnishing_status', $list);
        }
    }

    /**
     * Filter by poster type (Owner, Agent, Builder).
     *
     * @param  Builder<Property>  $query
     */
    protected function filterPostedBy(Builder $query, mixed $postedBy): void
    {
        if (empty($postedBy)) {
            return;
        }

        $list = is_array($postedBy) ? $postedBy : explode(',', (string) $postedBy);
        $list = array_values(array_filter(array_map('trim', $list)));

        if (! empty($list)) {
            $query->whereIn('user_type', $list);
        }
    }

    /**
     * Filter by minimum bathrooms count.
     *
     * @param  Builder<Property>  $query
     */
    protected function filterBathrooms(Builder $query, mixed $bathrooms): void
    {
        $count = (int) $bathrooms;
        if ($count > 0) {
            $query->where('bathrooms', '>=', $count);
        }
    }

    /**
     * Filter by property facing orientation.
     *
     * @param  Builder<Property>  $query
     */
    protected function filterFacing(Builder $query, mixed $facing): void
    {
        if (empty($facing)) {
            return;
        }

        $list = is_array($facing) ? $facing : explode(',', (string) $facing);
        $list = array_values(array_filter(array_map('trim', $list)));

        if (! empty($list)) {
            $query->whereIn('facing', $list);
        }
    }

    /**
     * Filter by negotiable pricing only.
     *
     * @param  Builder<Property>  $query
     */
    protected function filterNegotiable(Builder $query, bool $negotiableOnly): void
    {
        if ($negotiableOnly) {
            $query->where('price_negotiable', true);
        }
    }

    /**
     * Filter by amenities json array.
     *
     * @param  Builder<Property>  $query
     */
    protected function filterAmenities(Builder $query, mixed $amenities): void
    {
        if (empty($amenities)) {
            return;
        }

        $list = is_array($amenities) ? $amenities : explode(',', (string) $amenities);
        $list = array_values(array_filter(array_map('trim', $list)));

        foreach ($list as $amenity) {
            $query->whereJsonContains('amenities', $amenity);
        }
    }

    /**
     * Search properties using MySQL FULLTEXT indexing or fallback LIKE matching.
     *
     * @param  Builder<Property>  $query
     */
    protected function filterSearch(Builder $query, ?string $search): void
    {
        $rawSearch = trim((string) $search);
        if ($rawSearch === '') {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql' && strlen($rawSearch) >= 3) {
            $sanitizedSearch = preg_replace('/[+\-><()~*\"@]+/', ' ', $rawSearch);
            $words = array_values(array_filter(explode(' ', trim((string) $sanitizedSearch))));

            if (! empty($words)) {
                $booleanQuery = implode(' ', array_map(fn ($w) => '+'.$w.'*', $words));
                $query->whereRaw('MATCH(title, project_name, locality) AGAINST(? IN BOOLEAN MODE)', [$booleanQuery]);

                return;
            }
        }

        $like = '%'.$rawSearch.'%';
        $query->where(function (Builder $q) use ($like) {
            $q->where('title', 'like', $like)
                ->orWhere('locality', 'like', $like)
                ->orWhere('sub_locality', 'like', $like)
                ->orWhere('project_name', 'like', $like)
                ->orWhere('city', 'like', $like)
                ->orWhere('address', 'like', $like)
                ->orWhere('property_type', 'like', $like);
        });
    }

    /**
     * Filter by single locality or array of localities.
     *
     * @param  Builder<Property>  $query
     */
    protected function filterLocalities(Builder $query, mixed $localities, ?string $singleLocality): void
    {
        if (! empty($localities)) {
            $locList = is_array($localities) ? $localities : explode(',', (string) $localities);
            $locList = array_values(array_filter(array_map('trim', $locList)));
            if (! empty($locList)) {
                $query->whereIn('locality', $locList);
            }
        } elseif (! empty($singleLocality) && trim($singleLocality) !== '') {
            $query->where('locality', trim($singleLocality));
        }
    }

    /**
     * Filter by verified properties only.
     *
     * @param  Builder<Property>  $query
     */
    protected function filterVerified(Builder $query, bool $verifiedOnly): void
    {
        if ($verifiedOnly) {
            $query->where('is_verified', true);
        }
    }

    /**
     * Filter by featured properties only.
     *
     * @param  Builder<Property>  $query
     */
    protected function filterFeatured(Builder $query, bool $featuredOnly): void
    {
        if ($featuredOnly) {
            $query->where('is_featured', true);
        }
    }

    /**
     * Apply ordering to properties query.
     *
     * @param  Builder<Property>  $query
     */
    protected function applySorting(Builder $query, ?string $sortBy): void
    {
        match ($sortBy) {
            'price_low' => $query->orderBy('expected_price', 'asc')->orderBy('id', 'desc'),
            'price_high' => $query->orderBy('expected_price', 'desc')->orderBy('id', 'desc'),
            'newest' => $query->orderBy('id', 'desc'),
            default => $query->orderBy('is_featured', 'desc')->orderBy('id', 'desc'),
        };
    }
}
