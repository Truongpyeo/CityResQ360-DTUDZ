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
        Schema::create('module_definitions', function (Blueprint $table) {
            $table->id();
            
            // Module info
            $table->string('module_key', 50)->unique()->comment('Unique key: media, notification, wallet');
            $table->string('module_name', 100)->comment('Display name: MediaService');
            $table->text('description')->nullable();
            $table->string('icon', 50)->nullable()->comment('Icon name for UI');
            
            // Status
            $table->boolean('is_active')->default(true);
            $table->boolean('is_public')->default(true)->comment('Allow public registration');
            
            // URLs
            $table->string('base_url', 255)->nullable();
            $table->string('docs_url', 255)->nullable()->comment('Documentation URL path');
            
            // Default quotas
            $table->integer('default_max_storage_mb')->default(1000);
            $table->integer('default_max_requests_per_day')->default(10000);
            
            // Metadata
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            // Indexes
            $table->index('module_key');
            $table->index('is_active');
            $table->index('is_public');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('module_definitions');
    }
};
