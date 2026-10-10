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
        Schema::create('google_places', function (Blueprint $table) {
            $table->id();
            $table->string('place_id', 255)->nullable()->unique();
            $table->string('query_hash', 64)->index();
            $table->string('raw_query', 255)->index();
            $table->string('normalized_query', 255)->index();
            $table->string('name', 255);
            $table->string('formatted_address', 500);
            $table->string('locality', 150)->nullable()->index();
            $table->string('sub_locality', 150)->nullable();
            $table->string('city', 100)->nullable()->index();
            $table->string('state', 100)->nullable();
            $table->string('pincode', 10)->nullable()->index();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->json('types')->nullable();
            $table->json('raw_response')->nullable();
            $table->unsignedInteger('hit_count')->default(1);
            $table->string('provider', 50)->default('google_places');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('google_places');
    }
};
