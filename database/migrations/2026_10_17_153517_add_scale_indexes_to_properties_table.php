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
            $table->index(['city', 'property_for', 'expected_price'], 'props_city_for_price_idx');
            $table->index(['city', 'property_type', 'expected_price'], 'props_city_type_price_idx');
            $table->index(['city', 'bedrooms', 'expected_price'], 'props_city_bhk_price_idx');
            $table->index(['city', 'is_featured', 'id'], 'props_city_feat_id_idx');
            $table->index(['expected_price', 'id'], 'props_price_id_idx');
            $table->index(['construction_status', 'id'], 'props_const_status_id_idx');

            if (Schema::getConnection()->getDriverName() === 'mysql') {
                $table->fullText(['title', 'project_name', 'locality'], 'props_search_fulltext');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropIndex('props_city_for_price_idx');
            $table->dropIndex('props_city_type_price_idx');
            $table->dropIndex('props_city_bhk_price_idx');
            $table->dropIndex('props_city_feat_id_idx');
            $table->dropIndex('props_price_id_idx');
            $table->dropIndex('props_const_status_id_idx');

            if (Schema::getConnection()->getDriverName() === 'mysql') {
                $table->dropIndex('props_search_fulltext');
            }
        });
    }
};
