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
        Schema::table('properties', function (Blueprint $table) {
            $table->index(['city', 'property_for', 'is_featured', 'id'], 'props_city_for_feat_id_idx');
            $table->index(['city', 'property_for', 'property_type', 'is_featured', 'id'], 'props_city_for_type_feat_idx');
            $table->index(['city', 'property_for', 'expected_price', 'id'], 'props_city_for_price_id_idx');
            $table->index(['city', 'locality', 'property_for', 'id'], 'props_city_loc_for_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropIndex('props_city_for_feat_id_idx');
            $table->dropIndex('props_city_for_type_feat_idx');
            $table->dropIndex('props_city_for_price_id_idx');
            $table->dropIndex('props_city_loc_for_id_idx');
        });
    }
};
