@extends('emails.layouts.master')

@section('subject', 'Verify Your Email Address • RealHem')

@section('preheader')
    Please confirm your email address to complete your RealHem registration and access verified listings.
@endsection

@section('content')
    <!-- Icon Badge -->
    <div style="margin-bottom: 20px;">
        <span style="display: inline-block; width: 48px; height: 48px; line-height: 48px; border-radius: 14px; background-color: #eff6ff; color: #2563eb; text-align: center; font-size: 22px;">
            ✉️
        </span>
    </div>

    <h1 style="margin: 0 0 12px; font-size: 22px; font-weight: 900; color: #0f172a; letter-spacing: -0.5px;">
        Verify your email address
    </h1>

    <p style="margin: 0 0 16px; font-size: 15px; line-height: 1.6; color: #475569;">
        Hello <strong>{{ $notifiable->name ?? 'there' }}</strong>,
    </p>

    <p style="margin: 0 0 20px; font-size: 15px; line-height: 1.6; color: #475569;">
        Thank you for joining <strong>RealHem</strong>, India's trusted platform for buying, renting, and discovering verified residential &amp; commercial properties.
    </p>

    <p style="margin: 0 0 24px; font-size: 15px; line-height: 1.6; color: #475569;">
        To activate your account features and secure your profile, please confirm your email address by clicking the button below:
    </p>

    <!-- Action Button -->
    @include('emails.partials.button', [
        'url' => $verificationUrl,
        'color' => 'primary',
        'slot' => 'Verify Email Address &rarr;',
    ])

    <!-- Expiration & Security Notice -->
    <div style="background-color: #f8fafc; border-left: 4px solid #3b82f6; border-radius: 8px; padding: 14px 18px; margin: 24px 0;">
        <p style="margin: 0 0 6px; font-size: 13px; font-weight: 700; color: #1e40af;">
            ⏱️ Link Expires in 60 Minutes
        </p>
        <p style="margin: 0; font-size: 12px; color: #64748b; line-height: 1.5;">
            If you did not create a RealHem account, you can safely ignore this email. No further action is required.
        </p>
    </div>

    <!-- Troubleshooting link -->
    <div style="border-top: 1px solid #f1f5f9; padding-top: 20px; margin-top: 24px;">
        <p style="margin: 0 0 6px; font-size: 12px; color: #94a3b8;">
            Button not working? Copy and paste this URL into your browser:
        </p>
        <p style="margin: 0; font-size: 11px; word-break: break-all; color: #2563eb; line-height: 1.4;">
            <a href="{{ $verificationUrl }}" style="color: #2563eb; text-decoration: underline;">{{ $verificationUrl }}</a>
        </p>
    </div>
@endsection

