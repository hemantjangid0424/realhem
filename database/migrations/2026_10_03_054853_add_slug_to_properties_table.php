<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('title');
        });

        // Back-fill slugs for existing properties
        DB::table('properties')->orderBy('id')->each(function ($property) {
            $base = Str::slug(
                implode(' ', array_filter([
                    $property->bedrooms ? $property->bedrooms.' bhk' : null,
                    $property->property_type,
                    'for',
                    $property->property_for === 'Sell' ? 'sale' : strtolower($property->property_for),
                    'in',
                    $property->locality,
                    $property->city,
                ]))
            );

            $suffix = 'spld-'.strtoupper(base_convert($property->id + 1000000, 10, 36));

            DB::table('properties')
                ->where('id', $property->id)
                ->update(['slug' => $base.'-'.$suffix]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
