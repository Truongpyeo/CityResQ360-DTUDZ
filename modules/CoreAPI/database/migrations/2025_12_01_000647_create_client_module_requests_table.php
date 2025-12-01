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
        Schema::create('client_module_requests', function (Blueprint $table) {
            $table->id();
            
            // Client info
            $table->unsignedBigInteger('user_id')->comment('FK to NguoiDung');
            $table->unsignedBigInteger('module_id')->comment('FK to module_definitions');
            
            // Request details
            $table->string('app_domain', 255)->comment('Domain của app cần tích hợp');
            $table->string('app_name', 255)->nullable();
            $table->text('purpose')->comment('Mô tả mục đích tích hợp');
            
            // Custom quotas (optional)
            $table->integer('requested_max_storage_mb')->nullable();
            $table->integer('requested_max_requests_per_day')->nullable();
            
            // Status
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            
            // Admin action
            $table->unsignedInteger('reviewed_by_admin_id')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('admin_notes')->nullable();
            
            // Metadata
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('user_id')->references('id')->on('nguoi_dungs')->onDelete('cascade');
            $table->foreign('module_id')->references('id')->on('module_definitions')->onDelete('cascade');
            
            // Indexes
            $table->index('user_id');
            $table->index('module_id');
            $table->index('status');
            $table->index('created_at');
            
            // Unique constraint
            $table->unique(['user_id', 'module_id', 'app_domain'], 'unique_user_module_domain');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_module_requests');
    }
};
