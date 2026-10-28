@extends('emails.layouts.master')

@section('subject', 'Congratulations! Your Listing is Live on RealHem • #PROP-' . ($property->id ?? 'NEW'))

@section('preheader')
    Your property listing is active and now visible to 5 Lakh+ verified buyers across India.
@endsection

@section('content')
    <!-- Celebration Badge -->
    <div style="margin-bottom: 20px;">
        <span style="display: inline-block; padding: 6px 14px; border-radius: 999px; background-color: #ecfdf5; color: #047857; font-size: 12px; font-weight: 800; letter-spacing: 0.5px; text-transform: uppercase;">
            🎉 Listing Live &amp; Verified
        </span>
    </div>

    <h1 style="margin: 0 0 10px; font-size: 22px; font-weight: 900; color: #0f172a; letter-spacing: -0.5px;">
        Your property is now live!
    </h1>

    <p style="margin: 0 0 18px; font-size: 15px; line-height: 1.6; color: #475569;">
        Hello <strong>{{ $ownerName ?? 'Property Owner / Partner' }}</strong>,
    </p>

    <p style="margin: 0 0 20px; font-size: 15px; line-height: 1.6; color: #475569;">
        Great news! Your property listing has passed our verification checks and is now discoverable to thousands of active buyers and tenants searching on RealHem.
    </p>

    <!-- Property Card -->
    <div style="margin: 20px 0;">
        @include('emails.partials.property-card', ['property' => $property])
    </div>

    <!-- Live Listing Button -->
    @include('emails.partials.button', [
        'url' => config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:3000')) . '/property/' . ($property->slug ?? $property->id),
        'color' => 'primary',
        'slot' => 'View Live Listing on RealHem &rarr;',
    ])

    <!-- Pro Tips Box -->
    <div style="background-color: #f8fafc; border-radius: 12px; padding: 18px 20px; margin: 24px 0;">
        <h4 style="margin: 0 0 10px; font-size: 14px; font-weight: 800; color: #0f172a;">
            Tips to close 2x faster:
        </h4>
        <ul style="margin: 0; padding-left: 18px; font-size: 13px; color: #475569; line-height: 1.7;">
            <li><strong>Respond quickly:</strong> Responding to buyer calls within 15 minutes increases view-to-site-visit conversions by 65%.</li>
            <li><strong>High-Resolution Photos:</strong> Listings with 5+ bright photos receive 4x more buyer clicks.</li>
            <li><strong>Keep Price Transparent:</strong> Clear, realistic pricing attracts serious, pre-approved buyers.</li>
        </ul>
    </div>
@endsection

