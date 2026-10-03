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
        ]);

    // Verify public endpoint immediately reflects updated branding
    $publicResponse = $this->getJson('/api/settings');
    $publicResponse->assertStatus(200)
        ->assertJsonFragment([
            'company_name' => 'Acres99 Bharat',
            'company_short_name' => 'A99',
            'company_tagline' => 'India\'s Largest Real Estate Super-App',
        ]);
});
