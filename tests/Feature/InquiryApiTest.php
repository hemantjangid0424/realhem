<?php

use App\Models\Inquiry;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('public can submit contact inquiry', function () {
    $response = $this->postJson('/api/inquiries', [
        'type' => 'contact',
        'name' => 'Kunal Sharma',
        'email' => 'kunal@example.com',
        'phone' => '+91 9876543210',
        'subject' => 'General Inquiry',
        'message' => 'I would like to inquire about developer advertising opportunities on RealHem.',
    ]);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
        ])
        ->assertJsonFragment([
            'name' => 'Kunal Sharma',
            'type' => 'contact',
            'status' => 'pending',
        ]);

    $this->assertDatabaseHas('inquiries', [
        'email' => 'kunal@example.com',
        'type' => 'contact',
        'status' => 'pending',
    ]);
});

test('public can submit feedback with star rating', function () {
    $response = $this->postJson('/api/inquiries', [
        'type' => 'feedback',
        'name' => 'Anita Desai',
        'email' => 'anita@example.com',
        'message' => 'The map search and radius filters work flawlessly! Excellent speed.',
        'rating' => 5,
    ]);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
        ]);

    $this->assertDatabaseHas('inquiries', [
        'email' => 'anita@example.com',
        'type' => 'feedback',
        'rating' => 5,
    ]);
});

test('public can submit problem report for a property', function () {
    $user = User::factory()->create();
    $property = Property::factory()->create(['user_id' => $user->id]);

    $response = $this->postJson('/api/inquiries', [
        'type' => 'report_problem',
        'name' => 'Rahul Verma',
        'email' => 'rahul@example.com',
        'property_id' => $property->id,
        'subject' => 'Fake or Inaccurate Price',
        'message' => 'The listed price is ₹45 Lakhs but owner states ₹65 Lakhs on phone.',
    ]);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
        ]);

    $this->assertDatabaseHas('inquiries', [
        'email' => 'rahul@example.com',
        'property_id' => $property->id,
        'type' => 'report_problem',
    ]);
});

test('public can submit career job application', function () {
    $response = $this->postJson('/api/inquiries', [
        'type' => 'career',
        'name' => 'Vikram Seth',
        'email' => 'vikram@example.com',
        'phone' => '+91 9988776655',
        'subject' => 'Application for Senior Full-Stack Engineer',
        'message' => 'I have 5 years experience with Laravel and Vue 3.',
        'metadata' => [
            'portfolio_url' => 'https://linkedin.com/in/vikramseth',
            'job_title' => 'Senior Full-Stack Engineer',
        ],
    ]);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
        ]);

    $this->assertDatabaseHas('inquiries', [
        'email' => 'vikram@example.com',
        'type' => 'career',
    ]);
});

test('inquiry submission rejects invalid types and missing required fields', function () {
    $response = $this->postJson('/api/inquiries', [
        'type' => 'invalid_type',
        'name' => '',
        'email' => 'not-an-email',
        'message' => '',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['type', 'name', 'email', 'message']);
});

test('unauthenticated users cannot access admin inquiries endpoint', function () {
    $response = $this->getJson('/api/admin/inquiries');
    $response->assertStatus(401);
});

test('admin can retrieve and filter inquiries', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'email' => 'admin@realhem.com',
    ]);

    Inquiry::create([
        'type' => 'feedback',
        'name' => 'User One',
        'email' => 'one@example.com',
        'message' => 'Feedback message',
        'status' => 'pending',
    ]);

    Inquiry::create([
        'type' => 'report_problem',
        'name' => 'User Two',
        'email' => 'two@example.com',
        'message' => 'Problem report message',
        'status' => 'resolved',
    ]);

    $this->actingAs($admin, 'sanctum');

    $response = $this->getJson('/api/admin/inquiries');
    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
        ])
        ->assertJsonStructure([
            'data',
            'counts' => [
                'total',
                'pending',
                'resolved',
                'feedback',
                'report_problem',
            ],
            'pagination',
        ]);

    // Filter by type
    $filterResponse = $this->getJson('/api/admin/inquiries?type=report_problem');
    $filterResponse->assertStatus(200);
    expect($filterResponse->json('data'))->toHaveCount(1);
    expect($filterResponse->json('data.0.type'))->toBe('report_problem');
});

test('admin can update inquiry status', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'email' => 'admin@realhem.com',
    ]);

    $inquiry = Inquiry::create([
        'type' => 'contact',
        'name' => 'User Three',
        'email' => 'three@example.com',
        'message' => 'Need help with listing',
        'status' => 'pending',
    ]);

    $this->actingAs($admin, 'sanctum');

    $response = $this->patchJson("/api/admin/inquiries/{$inquiry->id}/status", [
        'status' => 'resolved',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Status updated to Resolved successfully!',
        ]);

    expect($inquiry->fresh()->status)->toBe('resolved');
});

test('admin can delete an inquiry', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'email' => 'admin@realhem.com',
    ]);

    $inquiry = Inquiry::create([
        'type' => 'feedback',
        'name' => 'Spam Bot',
        'email' => 'spambot@example.com',
        'message' => 'Buy cheap links here',
        'status' => 'pending',
    ]);

    $this->actingAs($admin, 'sanctum');

    $response = $this->deleteJson("/api/admin/inquiries/{$inquiry->id}");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
        ]);

    $this->assertDatabaseMissing('inquiries', [
        'id' => $inquiry->id,
    ]);
});
