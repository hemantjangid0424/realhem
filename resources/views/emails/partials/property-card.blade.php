@props([
    'property',
])
@php
    $priceDisplay = '';
    if (isset($property->expected_price) && $property->expected_price > 0) {
        $price = (float) $property->expected_price;
        if ($price >= 10000000) {
            $priceDisplay = '₹ ' . round($price / 10000000, 2) . ' Cr';
        } elseif ($price >= 100000) {
            $priceDisplay = '₹ ' . round($price / 100000, 2) . ' Lac';
        } else {
            $priceDisplay = '₹ ' . number_format($price);
        }
    }
    $location = trim(($property->locality ?? '') . ', ' . ($property->city ?? ''), ', ');
    $config = '';
    if (!empty($property->bedrooms)) {
        $config .= $property->bedrooms . ' BHK ';
    }
    if (!empty($property->property_type)) {
        $config .= ucwords(str_replace('_', ' ', $property->property_type));
    }
    $propUrl = config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:3000')) . '/listings/' . ($property->slug ?? $property->id);
@endphp

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin: 20px 0; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; overflow: hidden; box-shadow: 0 2px 6px rgba(0,0,0,0.03);">
    <tr>
        <td style="padding: 18px 20px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td style="vertical-align: top;">
                        <span style="display: inline-block; background-color: #ecfdf5; color: #065f46; font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 6px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">
                            Verified Property &bull; #PROP-{{ $property->id ?? 'LIVE' }}
                        </span>
                        <div style="font-size: 16px; font-weight: 800; color: #0f172a; line-height: 1.3; margin-bottom: 4px;">
                            <a href="{{ $propUrl }}" target="_blank" style="color: #0f172a; text-decoration: none;">
                                {{ $property->title ?? 'Featured Property' }}
                            </a>
                        </div>
                        <div style="font-size: 13px; color: #64748b; margin-bottom: 8px;">
                            📍 {{ $location ?: 'India' }}
                        </div>
                        <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                @if ($config)
                                    <td style="padding-right: 14px; font-size: 13px; font-weight: 600; color: #334155;">
                                        🏠 {{ $config }}
                                    </td>
                                @endif
                                @if (!empty($property->carpet_area))
                                    <td style="padding-right: 14px; font-size: 13px; font-weight: 600; color: #334155;">
                                        📐 {{ $property->carpet_area }} {{ $property->carpet_area_unit ?? 'sq.ft' }}
                                    </td>
                                @endif
                                @if ($priceDisplay)
                                    <td style="font-size: 15px; font-weight: 900; color: #1d4ed8;">
                                        {{ $priceDisplay }}
                                    </td>
                                @endif
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

