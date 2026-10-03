<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('user_type')->default('Owner'); // Owner, Agent, Builder
            $table->string('property_for')->default('Sell'); // Sell, Rent, PG
            $table->string('property_type')->default('Residential Apartment');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('project_name')->nullable();
            $table->string('city')->index();
            $table->string('locality')->index();
            $table->string('sub_locality')->nullable();
            $table->text('address')->nullable();
            $table->string('landmark')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->unsignedTinyInteger('bedrooms')->default(2);
            $table->unsignedTinyInteger('bathrooms')->default(2);
            $table->unsignedTinyInteger('balconies')->default(1);
            $table->unsignedInteger('carpet_area'); // sq.ft
            $table->unsignedInteger('super_builtup_area')->nullable(); // sq.ft
            $table->string('furnishing_status')->default('Semi-Furnished');
            $table->string('floor_no')->nullable();
            $table->unsignedSmallInteger('total_floors')->nullable();
            $table->string('facing')->nullable();
            $table->string('construction_status')->default('Ready to Move');
            $table->unsignedBigInteger('expected_price'); // INR
            $table->unsignedInteger('price_per_sqft')->nullable();
            $table->unsignedInteger('maintenance_charge')->nullable();
            $table->boolean('price_negotiable')->default(false);
            $table->json('amenities')->nullable();
            $table->json('photos')->nullable();
            $table->string('status')->default('active');
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
