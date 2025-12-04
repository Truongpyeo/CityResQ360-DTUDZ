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
        Schema::create('chi_tiet_phan_quyens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_vai_tro')->constrained('vai_tros')->onDelete('cascade')->comment('ID vai trò');
            $table->foreignId('id_chuc_nang')->constrained('chuc_nangs')->onDelete('cascade')->comment('ID chức năng');
            $table->timestamps();

            // Unique constraint để tránh duplicate
            $table->unique(['id_vai_tro', 'id_chuc_nang'], 'unique_role_permission');

            $table->index('id_vai_tro');
            $table->index('id_chuc_nang');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chi_tiet_phan_quyens');
    }
};
