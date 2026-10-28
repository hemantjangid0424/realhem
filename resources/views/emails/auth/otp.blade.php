@extends('emails.layouts.master')

@section('subject', 'Your RealHem Verification Code: ' . $otp)

@section('preheader', 'Your one-time verification code is ' . $otp . '. Valid for 10 minutes.')

@section('content')
    <!-- Security Icon -->
    <div style="margin-bottom: 20px;">
        <span style="display: inline-block; width: 48px; height: 48px; line-height: 48px; border-radius: 14px; background-color: #eff6ff; color: #2563eb; text-align: center; font-size: 22px;">
            🔐
        </span>
    </div>

    <h1 style="margin: 0 0 10px; font-size: 22px; font-weight: 900; color: #0f172a; letter-spacing: -0.5px;">
        Your Verification Code
    </h1>

    <p style="margin: 0 0 16px; font-size: 15px; line-height: 1.6; color: #475569;">
        Hello <strong>{{ $userName ?? 'there' }}</strong>,
    </p>

    <p style="margin: 0 0 24px; font-size: 15px; line-height: 1.6; color: #475569;">
        Use the following one-time password (OTP) to authenticate your RealHem account:
    </p>

    <!-- Prominent OTP Code Box -->
    <div style="background: linear-gradient(135deg, #eff6ff 0%, #e0e7ff 100%); background-color: #eff6ff; border: 2px dashed #93c5fd; border-radius: 16px; padding: 24px; text-align: center; margin: 24px 0;">
        <div style="font-size: 12px; font-weight: 800; color: #1e40af; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px;">
            One-Time Password (OTP)
        </div>
        <div style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, monospace; font-size: 38px; font-weight: 900; color: #1e3a8a; letter-spacing: 12px; line-height: 1.1;">
            {{ $otp }}
        </div>
        <div style="font-size: 12px; font-weight: 600; color: #3b82f6; margin-top: 10px;">
            ⏱️ Valid for {{ $expiresInMinutes ?? 10 }} minutes
        </div>
    </div>

    <p style="margin: 0 0 18px; font-size: 14px; line-height: 1.6; color: #64748b;">
        This code is confidential. For your security, do not share this OTP with anyone, including RealHem representatives.
    </p>

    <div style="background-color: #f8fafc; border-radius: 10px; padding: 12px 16px; margin-top: 24px;">
        <p style="margin: 0; font-size: 12px; color: #94a3b8; line-height: 1.5;">
            If you did not request this verification code, please ignore this email or reach out to our team at <a href="mailto:support@realhem.com" style="color: #2563eb; text-decoration: underline;">support@realhem.com</a>.
        </p>
    </div>
@endsection

