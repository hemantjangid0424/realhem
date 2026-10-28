<?php

namespace App\Http\Controllers;

use App\Models\Inquiry;
use App\Models\Property;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Notifications\Messages\MailMessage;

class EmailPreviewController extends Controller
{
    /**
     * Gallery index listing all available email templates.
     */
    public function index(): Response
    {
        $templates = [
            [
                'id' => 'otp',
                'name' => 'One-Time Password (OTP)',
                'category' => 'Authentication',
                'description' => 'Sent to mobile and email simultaneously for 1-click passwordless login and registration.',
                'recipient' => 'All Users',
            ],
            [
                'id' => 'verify-email',
                'name' => 'Email Verification',
                'category' => 'Authentication',
                'description' => 'Sent to new users to verify their email address with a 60-minute expiring secure link.',
                'recipient' => 'Buyer / Tenant / Owner',
            ],
            [
                'id' => 'inquiry-confirmation',
                'name' => 'Inquiry & Tour Confirmation',
                'category' => 'Buyer Leads',
                'description' => 'Sent to buyers/tenants after submitting a property inquiry or requesting a site visit.',
                'recipient' => 'Prospective Buyer',
            ],
            [
                'id' => 'owner-lead-alert',
                'name' => 'New Buyer Lead Alert',
                'category' => 'Broker & Owner CRM',
                'description' => 'Sent to property owners or brokers with buyer contact info, visit times, and 1-click WhatsApp.',
                'recipient' => 'Property Owner / Broker',
            ],
            [
                'id' => 'listing-published',
                'name' => 'Listing Live & Verified',
                'category' => 'Property Lifecycle',
                'description' => 'Sent when a new listing passes review and goes live to 5 Lakh+ buyers across India.',
                'recipient' => 'Seller / Broker',
            ],
            [
                'id' => 'markdown-default',
                'name' => 'Standard Markdown Notification',
                'category' => 'System Notifications',
                'description' => 'Preview of how standard Laravel MailMessage notifications look with the RealHem theme.',
                'recipient' => 'Any User',
            ],
        ];

        return response()->view('emails.preview-index', compact('templates'));
    }

    /**
     * Preview an individual email template.
     */
    public function show(string $template): mixed
    {
        $mockProperty = new Property([
            'id' => 10842,
            'title' => 'Prestige Lakeside Habitat • 3 BHK Luxury High-Rise Apartment',
            'slug' => 'prestige-lakeside-habitat-whitefield-bengaluru',
            'property_type' => 'apartment',
            'for_type' => 'sale',
            'city' => 'Bengaluru',
            'locality' => 'Whitefield',
            'bedrooms' => 3,
            'bathrooms' => 3,
            'carpet_area' => 1650,
            'carpet_area_unit' => 'sq.ft',
            'expected_price' => 14500000,
            'is_featured' => true,
            'is_verified' => true,
        ]);

        $mockUser = new User([
            'name' => 'Hemant Sharma',
            'email' => 'hemant@example.com',
            'mobile' => '9876543210',
            'role' => 'user',
        ]);

        $inquiry1 = new Inquiry([
            'id' => 4501,
            'name' => 'Hemant Sharma',
            'email' => 'hemant@example.com',
            'phone' => '+91 98765 43210',
            'type' => 'request_info',
            'preferred_time' => 'Tomorrow, 4:00 PM - 6:00 PM',
            'message' => 'I would like to schedule an in-person site visit this Saturday. Please confirm if parking and club amenities are ready for possession.',
        ]);
        $inquiry1->setRelation('property', $mockProperty);

        $inquiry2 = new Inquiry([
            'id' => 4502,
            'name' => 'Vikram Malhotra',
            'email' => 'vikram.m@example.com',
            'phone' => '9820012345',
            'type' => 'request_info',
            'preferred_time' => 'Sunday Morning (10:00 AM)',
            'message' => 'Interested in 3 BHK flat. Have pre-approved home loan with HDFC. Looking for immediate booking.',
        ]);
        $inquiry2->setRelation('property', $mockProperty);

        return match ($template) {
            'otp' => view('emails.auth.otp', [
                'otp' => '4829',
                'userName' => 'Hemant Sharma',
                'expiresInMinutes' => 10,
            ]),

            'verify-email' => view('emails.auth.verify-email', [
                'verificationUrl' => url('/api/email/verify/1/sample-hash-12345?expires=1793038800&signature=abcdef123456'),
                'notifiable' => $mockUser,
            ]),

            'inquiry-confirmation' => view('emails.inquiries.buyer-confirmation', [
                'inquiry' => $inquiry1,
                'typeLabel' => 'Property Tour & Info Reservation',
            ]),

            'owner-lead-alert' => view('emails.inquiries.owner-lead-alert', [
                'recipientName' => 'Rajesh Mehta (Prestige Estates)',
                'inquiry' => $inquiry2,
            ]),

            'listing-published' => view('emails.properties.listing-published', [
                'ownerName' => 'Rajesh Mehta',
                'property' => $mockProperty,
            ]),

            'markdown-default' => (new MailMessage)
                ->subject('Security Alert: New Sign-In from Chrome on Mac OS • RealHem')
                ->greeting('Hello Hemant!')
                ->line('We detected a new sign-in to your RealHem account from Chrome on Mac OS X in Bengaluru, India.')
                ->line('If this was you, you can safely disregard this message. If you do not recognize this activity, please secure your account immediately.')
                ->action('Review Account Activity', url('/dashboard/security'))
                ->line('For security reasons, your active sessions can be revoked at any time from your account settings.')
                ->salutation("Best regards,\nThe RealHem Security Desk")
                ->render(),

            default => abort(404, 'Email template not found.'),
        };
    }
}
