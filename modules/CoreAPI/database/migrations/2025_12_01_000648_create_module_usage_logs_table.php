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
        Schema::create('module_usage_logs', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('credential_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('module_id');
            
            // Request info
            $table->string('endpoint', 255)->nullable();
            $table->string('method', 10)->nullable();
            $table->integer('status_code')->nullable();
            
            // Response data (module-specific)
            $table->json('response_data')->nullable();
            
            // Metadata
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();
            
            // Foreign keys
            $table->foreign('credential_id')->references('id')->on('client_module_credentials')->onDelete('cascade');
            
            // Indexes
            $table->index('credential_id');
            $table->index('created_at');
            $table->index(['user_id', 'module_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('module_usage_logs');
    }
};
