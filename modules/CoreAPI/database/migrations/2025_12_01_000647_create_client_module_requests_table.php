<?php
/*
 * CityResQ360-DTUDZ - Smart City Emergency Response System
 * Copyright (C) 2025 DTU-DZ Team
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

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
