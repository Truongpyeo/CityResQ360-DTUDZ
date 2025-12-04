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
        Schema::create('client_module_credentials', function (Blueprint $table) {
            $table->id();
            
            // Reference
            $table->unsignedBigInteger('request_id')->comment('FK to client_module_requests');
            $table->unsignedBigInteger('user_id')->comment('FK to NguoiDung');
            $table->unsignedBigInteger('module_id')->comment('FK to module_definitions');
            
            // Credentials
            $table->string('client_id', 100)->unique()->comment('Unique client identifier');
            $table->string('jwt_secret', 255)->comment('JWT secret key');
            
            // Quotas
            $table->integer('max_storage_mb')->default(1000);
            $table->integer('max_requests_per_day')->default(10000);
            $table->integer('max_file_size_mb')->default(10);
            
            // Usage tracking
            $table->integer('current_storage_mb')->default(0);
            $table->integer('total_requests')->default(0);
            $table->timestamp('last_used_at')->nullable();
            
            // Status
            $table->boolean('is_active')->default(true);
            $table->timestamp('revoked_at')->nullable();
            $table->text('revoked_reason')->nullable();
            
            // Metadata
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('request_id')->references('id')->on('client_module_requests')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('nguoi_dungs')->onDelete('cascade');
            $table->foreign('module_id')->references('id')->on('module_definitions')->onDelete('cascade');
            
            // Indexes
            $table->index('user_id');
            $table->index('module_id');
            $table->index('client_id');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_module_credentials');
    }
};
