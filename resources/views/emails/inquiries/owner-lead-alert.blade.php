@extends('emails.layouts.master')

@section('subject', 'New Lead Alert: Inquiry on ' . ($inquiry->property?->title ?? 'Your Property') . ' • RealHem')

@section('preheader')
    A prospective buyer/tenant just submitted an inquiry on your RealHem listing. Connect with them now!
@endsection

@section('content')
    <!-- Hot Lead Badge -->
    <div style="margin-bottom: 20px;">
        <span style="display: inline-block; padding: 6px 14px; border-radius: 999px; background-color: #fef3c7; color: #92400e; font-size: 12px; font-weight: 800; letter-spacing: 0.5px; text-transform: uppercase;">
            ⚡ New Buyer Lead Received
        </span>
    </div>

    <h1 style="margin: 0 0 10px; font-size: 22px; font-weight: 900; color: #0f172a; letter-spacing: -0.5px;">
        You have a new inquiry!
    </h1>

    <p style="margin: 0 0 18px; font-size: 15px; line-height: 1.6; color: #475569;">
        Hello <strong>{{ $recipientName ?? 'Property Owner / Partner' }}</strong>,
    </p>

    <p style="margin: 0 0 20px; font-size: 15px; line-height: 1.6; color: #475569;">
        A verified buyer has requested details and expressed interest in visiting your listing on RealHem. Leads contacted within 15 minutes have a <strong>3x higher closing rate</strong>.
    </p>

    <!-- Buyer Details Box -->
    @include('emails.partials.details-box', [
        'title' => 'Buyer Contact Information',
        'slot' => '
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td style="padding: 8px 0; font-size: 13px; color: #64748b; width: 140px; vertical-align: top;">Buyer Name:</td>
                    <td style="padding: 8px 0; font-size: 14px; font-weight: 800; color: #0f172a;">' . e($inquiry->name ?? 'Interested Buyer') . '</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-size: 13px; color: #64748b; vertical-align: top;">Mobile Number:</td>
                    <td style="padding: 8px 0; font-size: 14px; font-weight: 800; color: #2563eb;">
                        <a href="tel:' . e($inquiry->phone) . '" style="color: #2563eb; text-decoration: none;">' . e($inquiry->phone ?? 'Not provided') . '</a>
                    </td>
                </tr>
                ' . (!empty($inquiry->email) ? '
                <tr>
                    <td style="padding: 8px 0; font-size: 13px; color: #64748b; vertical-align: top;">Email Address:</td>
                    <td style="padding: 8px 0; font-size: 13px; color: #334155;">
                        <a href="mailto:' . e($inquiry->email) . '" style="color: #2563eb; text-decoration: none;">' . e($inquiry->email) . '</a>
                    </td>
                </tr>' : '') . '
                ' . (!empty($inquiry->preferred_time) ? '
                <tr>
                    <td style="padding: 8px 0; font-size: 13px; color: #64748b; vertical-align: top;">Preferred Visit Time:</td>
                    <td style="padding: 8px 0; font-size: 13px; font-weight: 700; color: #059669;">' . e($inquiry->preferred_time) . '</td>
                </tr>' : '') . '
                ' . (!empty($inquiry->message) ? '
                <tr>
                    <td style="padding: 8px 0; font-size: 13px; color: #64748b; vertical-align: top;">Buyer Note:</td>
                    <td style="padding: 8px 0; font-size: 13px; color: #334155; font-style: italic; background-color: #ffffff; padding: 10px; border-radius: 8px; border: 1px solid #e2e8f0;">"' . e($inquiry->message) . '"</td>
                </tr>' : '') . '
            </table>
        ',
    ])

    <!-- Property Inquired -->
    @if ($inquiry->property)
        <div style="margin: 20px 0;">
            <p style="margin: 0 0 8px; font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">
                Listing Reference
            </p>
            @include('emails.partials.property-card', ['property' => $inquiry->property])
        </div>
    @endif

    <!-- Call / WhatsApp Action Buttons -->
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin: 24px 0 12px;">
        <tr>
            <td align="center">
                @if (!empty($inquiry->phone))
                    @php
                        $cleanPhone = preg_replace('/\D/', '', $inquiry->phone);
                        if (strlen($cleanPhone) === 10) {
                            $cleanPhone = '91' . $cleanPhone;
                        }
                    @endphp
                    <a href="https://wa.me/{{ $cleanPhone }}?text={{ urlencode('Hello ' . ($inquiry->name ?? '') . ', thank you for your inquiry on RealHem regarding ' . ($inquiry->property?->title ?? 'our property') . '. When is a good time to connect?') }}" target="_blank" style="display: inline-block; background-color: #16a34a; color: #ffffff; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; font-size: 14px; font-weight: 700; text-decoration: none; padding: 12px 24px; border-radius: 10px; margin: 0 6px 10px; box-shadow: 0 4px 12px rgba(22, 163, 74, 0.25);">
                        💬 Chat on WhatsApp &rarr;
                    </a>
                @endif
                <a href="{{ config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:3000')) }}/dashboard" target="_blank" style="display: inline-block; background-color: #2563eb; color: #ffffff; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; font-size: 14px; font-weight: 700; text-decoration: none; padding: 12px 24px; border-radius: 10px; margin: 0 6px 10px; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);">
                    View in Dashboard &rarr;
                </a>
            </td>
        </tr>
    </table>
@endsection

