<?php

use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('public can retrieve active job postings', function () {
    JobPosting::create([
        'title' => 'Backend Architect',
        'department' => 'Engineering',
        'location' => 'Ahmedabad',
        'type' => 'Full Time',
        'experience' => '5+ Years',
        'tags' => ['PHP', 'Laravel', 'Redis'],
        'description' => 'Architect high-concurrency systems.',
        'is_active' => true,
    ]);

    JobPosting::create([
        'title' => 'Closed Position',
        'department' => 'Engineering',
        'location' => 'Ahmedabad',
        'type' => 'Full Time',
        'description' => 'Already hired.',
        'is_active' => false,
    ]);

    $response = $this->getJson('/api/jobs');

    $response->assertStatus(200)
        ->assertJson(['success' => true])
        ->assertJsonFragment(['title' => 'Backend Architect'])
        ->assertJsonMissing(['title' => 'Closed Position']);
});

test('unauthenticated users cannot access admin job management endpoints', function () {
    $response = $this->getJson('/api/admin/jobs');
    $response->assertStatus(401);

    $postResponse = $this->postJson('/api/admin/jobs', [
        'title' => 'Test Job',
    ]);
    $postResponse->assertStatus(401);
});

test('admin can retrieve, filter, and count job postings', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'email' => 'admin@realhem.com',
    ]);

    JobPosting::create([
        'title' => 'Mobile App Developer',
        'department' => 'Engineering',
        'location' => 'Remote',
        'type' => 'Full Time',
        'description' => 'Build Flutter / React Native applications.',
        'is_active' => true,
    ]);

    JobPosting::create([
        'title' => 'HR Specialist',
        'department' => 'Operations',
        'location' => 'Ahmedabad',
        'type' => 'Full Time',
        'description' => 'Recruit top engineering talent.',
        'is_active' => false,
    ]);

    $this->actingAs($admin, 'sanctum');

    $response = $this->getJson('/api/admin/jobs');
    $response->assertStatus(200)
        ->assertJson(['success' => true])
        ->assertJsonStructure([
            'data',
            'counts' => ['total', 'active', 'inactive'],
            'departments',
        ]);

    // Department filter
    $filterResponse = $this->getJson('/api/admin/jobs?department=Operations');
    $filterResponse->assertStatus(200);
    expect($filterResponse->json('data'))->toHaveCount(1);
    expect($filterResponse->json('data.0.title'))->toBe('HR Specialist');
});

test('admin can create, update, toggle, and delete job postings', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'email' => 'admin@realhem.com',
    ]);

    $this->actingAs($admin, 'sanctum');

    // 1. Create
    $createResponse = $this->postJson('/api/admin/jobs', [
        'title' => 'VP of Engineering',
        'department' => 'Leadership',
        'location' => 'Ahmedabad / Hybrid',
        'type' => 'Full Time',
        'experience' => '8+ Years',
        'tags' => ['Leadership', 'Architecture', 'PropTech'],
        'description' => 'Lead entire technical and infrastructure organization.',
        'is_active' => true,
    ]);

    $createResponse->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'Job posting created successfully!',
        ]);

    $jobId = $createResponse->json('job.id');
    $this->assertDatabaseHas('job_postings', [
        'id' => $jobId,
        'title' => 'VP of Engineering',
        'slug' => 'vp-of-engineering',
    ]);

    // 2. Update
    $updateResponse = $this->putJson("/api/admin/jobs/{$jobId}", [
        'title' => 'VP of Engineering & Technology',
        'department' => 'Leadership',
        'location' => 'Ahmedabad / Remote',
        'type' => 'Full Time',
        'experience' => '10+ Years',
        'tags' => ['Cloud', 'Strategy'],
        'description' => 'Updated leadership role description.',
        'is_active' => true,
    ]);

    $updateResponse->assertStatus(200)
        ->assertJsonFragment(['title' => 'VP of Engineering & Technology']);

    // 3. Toggle Active
    $toggleResponse = $this->patchJson("/api/admin/jobs/{$jobId}/toggle-active");
    $toggleResponse->assertStatus(200)
        ->assertJsonFragment(['is_active' => false]);
    expect(JobPosting::find($jobId)->is_active)->toBeFalse();

    // 4. Delete
    $deleteResponse = $this->deleteJson("/api/admin/jobs/{$jobId}");
    $deleteResponse->assertStatus(200)
        ->assertJson(['success' => true]);

    $this->assertDatabaseMissing('job_postings', ['id' => $jobId]);
});
