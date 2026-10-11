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
            $table->index(['status', 'id']);
            $table->index(['city', 'status', 'id']);
            $table->index(['property_for', 'status', 'id']);
            $table->index(['is_verified', 'id']);
            $table->index(['is_featured', 'id']);
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropIndex(['status', 'id']);
            $table->dropIndex(['city', 'status', 'id']);
            $table->dropIndex(['property_for', 'status', 'id']);
            $table->dropIndex(['is_verified', 'id']);
            $table->dropIndex(['is_featured', 'id']);
            $table->dropIndex(['user_id', 'status']);
        });
    }
};
