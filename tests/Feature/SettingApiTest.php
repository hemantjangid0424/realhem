<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('public can retrieve platform branding settings', function () {
    $response = $this->getJson('/api/settings');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
        ])
        ->assertJsonFragment([
            'company_name' => 'RealHem',
            'company_short_name' => 'RH',
        ]);
});

test('unauthenticated user cannot update company settings', function () {
    $response = $this->postJson('/api/admin/settings', [
        'company_name' => 'New Estate Brand',
        'company_short_name' => 'NEB',
        'company_tagline' => 'Luxury Homes Everywhere',
    ]);

    $response->assertStatus(401);
});

test('admin can retrieve and update company branding settings', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'email' => 'admin@realhem.com',
    ]);

    $this->actingAs($admin, 'sanctum');

    $getResponse = $this->getJson('/api/admin/settings');
    $getResponse->assertStatus(200)
        ->assertJsonFragment(['company_name' => 'RealHem']);

    $updatePayload = [
        'company_name' => 'Acres99 Bharat',
        'company_short_name' => 'A99',
        'company_tagline' => 'India\'s Largest Real Estate Super-App',
        'company_sub_tagline' => 'Flats, Villas, Plots & Commercial Spaces',
        'company_logo_url' => 'https://example.com/logo.png',
        'company_phone' => '1800-99-9999',
        'company_email' => 'support@acres99.in',
        'brand_primary_color' => '#004e8f',
        'brand_secondary_color' => '#1d4ed8',
        'festival_animation' => 'diwali_diyas',
        'footer_about' => 'Custom updated company about text.',
        'copyright_text' => '© 2026 Acres99 Bharat Ltd.',
    ];

    $updateResponse = $this->postJson('/api/admin/settings', $updatePayload);

    $updateResponse->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Company branding & settings updated successfully!',
        ])
        ->assertJsonFragment([
            'company_name' => 'Acres99 Bharat',
            'company_short_name' => 'A99',
            'brand_primary_color' => '#004e8f',
            'festival_animation' => 'diwali_diyas',
        ]);

    // Verify public endpoint immediately reflects updated branding
    $publicResponse = $this->getJson('/api/settings');
    $publicResponse->assertStatus(200)
        ->assertJsonFragment([
            'company_name' => 'Acres99 Bharat',
            'company_short_name' => 'A99',
            'company_tagline' => 'India\'s Largest Real Estate Super-App',
            'festival_animation' => 'diwali_diyas',
        ]);
});

test('public can retrieve legal and statutory policy settings', function () {
    $response = $this->getJson('/api/legal');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
        ])
        ->assertJsonStructure([
            'success',
            'legal' => [
                'terms',
                'privacy',
                'rera',
                'refund',
                'updated_at',
                'version',
            ],
        ]);
});

test('unauthenticated user cannot update legal policies', function () {
    $response = $this->postJson('/api/admin/legal', [
        'terms' => 'New custom terms and conditions',
    ]);

    $response->assertStatus(401);
});

test('admin can retrieve and update legal policies', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'email' => 'admin_legal@realhem.com',
    ]);

    $this->actingAs($admin, 'sanctum');

    $adminGet = $this->getJson('/api/admin/legal');
    $adminGet->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'legal',
        ]);

    $customLegal = [
        'terms' => '### Custom Terms of Service\nUpdated broker and user agreement for RealHem platform.',
        'privacy' => '### Custom Privacy Policy\nUser data encryption and location safety policies.',
        'rera' => '### Custom RERA Advisory\nMandatory state authority registration details.',
        'refund' => '### Custom Refund Policy\nDetailed cancellation guidelines for paid featured packages.',
        'updated_at' => 'November 2026',
        'version' => '3.0',
    ];

    $updateResponse = $this->postJson('/api/admin/legal', $customLegal);

    $updateResponse->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Legal documents and policies updated successfully!',
        ])
        ->assertJsonFragment([
            'terms' => '### Custom Terms of Service\nUpdated broker and user agreement for RealHem platform.',
            'version' => '3.0',
            'updated_at' => 'November 2026',
        ]);

    // Verify public endpoint reflects custom legal policies
    $publicResponse = $this->getJson('/api/legal');
    $publicResponse->assertStatus(200)
        ->assertJsonFragment([
            'terms' => '### Custom Terms of Service\nUpdated broker and user agreement for RealHem platform.',
            'version' => '3.0',
            'updated_at' => 'November 2026',
        ]);
});
