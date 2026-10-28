<?php

use App\Models\Inquiry;
use App\Models\Property;
use App\Models\User;
use App\Notifications\InquiryConfirmationNotification;
use App\Notifications\SendOtpNotification;
use App\Notifications\VerifyEmailNotification;

test('email preview gallery index is accessible in dev environment', function () {
    $response = $this->get('/dev/mail-preview');

    $response->assertStatus(200);
    $response->assertSee('RealHem Email Design System');
    $response->assertSee('One-Time Password (OTP)');
    $response->assertSee('Email Verification');
    $response->assertSee('Inquiry & Tour Confirmation');
    $response->assertSee('New Buyer Lead Alert');
    $response->assertSee('Listing Live & Verified');
});

test('otp email template renders successfully', function () {
    $response = $this->get('/dev/mail-preview/otp');

    $response->assertStatus(200);
    $response->assertSee('Your Verification Code');
    $response->assertSee('4829');
    $response->assertSee('Hemant Sharma');
    $response->assertSee('RealHem India Pvt. Ltd.');
    $response->assertSee('Valid for 10 minutes');
});

test('email verification template renders successfully', function () {
    $response = $this->get('/dev/mail-preview/verify-email');

    $response->assertStatus(200);
    $response->assertSee('Verify your email address');
    $response->assertSee('Hemant Sharma');
    $response->assertSee('Verify Email Address');
    $response->assertSee('RealHem India Pvt. Ltd.');
    $response->assertSee('Link Expires in 60 Minutes');
});

test('inquiry confirmation template renders with property details', function () {
    $response = $this->get('/dev/mail-preview/inquiry-confirmation');

    $response->assertStatus(200);
    $response->assertSee('Your inquiry has been received!');
    $response->assertSee('Prestige Lakeside Habitat');
    $response->assertSee('Whitefield, Bengaluru');
    $response->assertSee('Hemant Sharma');
    $response->assertSee('What happens next?');
});

test('owner lead alert template renders with buyer contact info', function () {
    $response = $this->get('/dev/mail-preview/owner-lead-alert');

    $response->assertStatus(200);
    $response->assertSee('You have a new inquiry!');
    $response->assertSee('Vikram Malhotra');
    $response->assertSee('9820012345');
    $response->assertSee('Chat on WhatsApp');
    $response->assertSee('Prestige Lakeside Habitat');
});

test('listing published template renders with listing details', function () {
    $response = $this->get('/dev/mail-preview/listing-published');

    $response->assertStatus(200);
    $response->assertSee('Your property is now live!');
    $response->assertSee('Prestige Lakeside Habitat');
    $response->assertSee('Tips to close 2x faster');
    $response->assertSee('View Live Listing on RealHem');
});

test('markdown default notification renders with RealHem brand styling', function () {
    $response = $this->get('/dev/mail-preview/markdown-default');

    $response->assertStatus(200);
    $response->assertSee('We detected a new sign-in');
    $response->assertSee('Review Account Activity');
    $response->assertSee('RealHem');
    $response->assertSee("India's Premier Real Estate Portal", false);
});

test('send otp notification produces valid mail message using RealHem template', function () {
    $notification = new SendOtpNotification('9821', 'Hemant', 10);
    $mailMessage = $notification->toMail((object) ['email' => 'hemant@example.com']);

    expect($mailMessage->view)->toBe('emails.auth.otp');
    expect($mailMessage->subject)->toBe('Your RealHem Verification Code: 9821');
});

test('verify email notification produces valid mail message using RealHem template', function () {
    $user = new User([
        'name' => 'Aditi Rao',
        'email' => 'aditi@example.com',
    ]);
    $user->id = 1;

    $notification = new VerifyEmailNotification;
    $mailMessage = $notification->toMail($user);

    expect($mailMessage->view)->toBe('emails.auth.verify-email');
    expect($mailMessage->subject)->toContain('Verify Your Email Address • RealHem');
});

test('inquiry confirmation notification produces valid mail message using RealHem template', function () {
    $property = new Property([
        'id' => 1,
        'title' => 'Godrej Woodsman Estate',
        'city' => 'Bengaluru',
    ]);

    $inquiry = new Inquiry([
        'name' => 'Karan Johar',
        'email' => 'karan@example.com',
        'phone' => '9876543210',
        'type' => 'request_info',
    ]);
    $inquiry->setRelation('property', $property);

    $notification = new InquiryConfirmationNotification($inquiry);
    $mailMessage = $notification->toMail((object) ['email' => 'karan@example.com']);

    expect($mailMessage->view)->toBe('emails.inquiries.buyer-confirmation');
    expect($mailMessage->subject)->toContain('RealHem');
});
