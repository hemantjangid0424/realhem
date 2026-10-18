<?php

use App\Models\Setting;
use App\Services\ImageWatermarkService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('watermarks and stores uploaded jpg image', function () {
    Storage::fake('public');

    $service = new ImageWatermarkService;
    $file = UploadedFile::fake()->image('villa.jpg', 600, 400);

    $storedPath = $service->applyAndStore($file, 'properties', 'public');

    expect($storedPath)->toStartWith('properties/');
    expect(Storage::disk('public')->exists($storedPath))->toBeTrue();

    // Verify file content is valid image
    $binary = Storage::disk('public')->get($storedPath);
    $size = getimagesizefromstring($binary);
    expect($size)->not()->toBeFalse();
    expect($size[0])->toBe(600);
    expect($size[1])->toBe(400);
});

test('watermarks and stores uploaded png image', function () {
    Storage::fake('public');

    $service = new ImageWatermarkService;
    $file = UploadedFile::fake()->image('floorplan.png', 500, 300);

    $storedPath = $service->applyAndStore($file, 'properties', 'public');

    expect($storedPath)->toStartWith('properties/');
    expect(Storage::disk('public')->exists($storedPath))->toBeTrue();

    $binary = Storage::disk('public')->get($storedPath);
    $size = getimagesizefromstring($binary);
    expect($size)->not()->toBeFalse();
});

test('applies custom company branding text to watermark', function () {
    Storage::fake('public');

    Setting::set('company_name', 'RealHem Properties', 'branding');

    $service = new ImageWatermarkService;
    $file = UploadedFile::fake()->image('apartment.jpg', 800, 600);

    $storedPath = $service->applyAndStore($file, 'properties', 'public');
    expect(Storage::disk('public')->exists($storedPath))->toBeTrue();
});
