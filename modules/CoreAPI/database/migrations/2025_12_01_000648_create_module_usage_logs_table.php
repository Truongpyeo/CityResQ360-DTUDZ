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
