@extends('emails.layouts.master')

@section('subject', 'Inquiry Confirmed: ' . ($inquiry->property?->title ?? 'Property Visit Request') . ' • RealHem')

@section('preheader')
    We have received your inquiry. A verified property specialist will reach out shortly.
@endsection

@section('content')
    <!-- Success Badge -->
    <div style="margin-bottom: 20px;">
        <span style="display: inline-block; width: 48px; height: 48px; line-height: 48px; border-radius: 14px; background-color: #ecfdf5; color: #059669; text-align: center; font-size: 22px;">
            ✅
        </span>
    </div>

    <h1 style="margin: 0 0 10px; font-size: 22px; font-weight: 900; color: #0f172a; letter-spacing: -0.5px;">
        Your inquiry has been received!
    </h1>

    <p style="margin: 0 0 18px; font-size: 15px; line-height: 1.6; color: #475569;">
        Hello <strong>{{ $inquiry->name ?? 'Valued Customer' }}</strong>,
    </p>

    <p style="margin: 0 0 20px; font-size: 15px; line-height: 1.6; color: #475569;">
        Thank you for reaching out through RealHem. We've notified the verified listing owner and registered your interest in our system under Reference <strong>#INQ-{{ $inquiry->id ?? 'NEW' }}</strong>.
    </p>

    <!-- Property Card -->
    @if ($inquiry->property)
        <div style="margin: 20px 0;">
            <p style="margin: 0 0 8px; font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">
                Property Inquired
            </p>
            @include('emails.partials.property-card', ['property' => $inquiry->property])
        </div>
    @endif

    <!-- Request Details Box -->
    @include('emails.partials.details-box', [
        'title' => 'Your Submission Details',
        'slot' => '
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td style="padding: 6px 0; font-size: 13px; color: #64748b; width: 140px; vertical-align: top;">Request Type:</td>
                    <td style="padding: 6px 0; font-size: 13px; font-weight: 700; color: #0f172a;">' . ucwords(str_replace('_', ' ', $inquiry->type ?? 'Property Tour & Info')) . '</td>
                </tr>
                <tr>
                    <td style="padding: 6px 0; font-size: 13px; color: #64748b; vertical-align: top;">Contact Mobile:</td>
                    <td style="padding: 6px 0; font-size: 13px; font-weight: 700; color: #0f172a;">' . e($inquiry->phone ?? 'Not provided') . '</td>
                </tr>
                ' . (!empty($inquiry->preferred_time) ? '
                <tr>
                    <td style="padding: 6px 0; font-size: 13px; color: #64748b; vertical-align: top;">Preferred Visit Time:</td>
                    <td style="padding: 6px 0; font-size: 13px; font-weight: 700; color: #2563eb;">' . e($inquiry->preferred_time) . '</td>
                </tr>' : '') . '
                ' . (!empty($inquiry->message) ? '
                <tr>
                    <td style="padding: 6px 0; font-size: 13px; color: #64748b; vertical-align: top;">Your Message:</td>
                    <td style="padding: 6px 0; font-size: 13px; color: #334155; font-style: italic;">"' . e($inquiry->message) . '"</td>
                </tr>' : '') . '
            </table>
        ',
    ])

    <!-- What happens next -->
    <div style="background-color: #f8fafc; border-radius: 12px; padding: 18px 20px; margin: 24px 0;">
        <h4 style="margin: 0 0 10px; font-size: 14px; font-weight: 800; color: #0f172a;">
            What happens next?
        </h4>
        <ol style="margin: 0; padding-left: 18px; font-size: 13px; color: #475569; line-height: 1.7;">
            <li>The property owner/relationship manager is reviewing your inquiry.</li>
            <li>You will receive a call or WhatsApp confirmation to schedule your site visit.</li>
            <li>No agent commission is charged for direct-owner verified listings on RealHem.</li>
        </ol>
    </div>

    <!-- View Listing Button -->
    @if ($inquiry->property)
        @include('emails.partials.button', [
            'url' => config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:3000')) . '/listings/' . ($inquiry->property->slug ?? $inquiry->property->id),
            'color' => 'primary',
            'slot' => 'View Listing Details on RealHem &rarr;',
        ])
    @endif
@endsection

