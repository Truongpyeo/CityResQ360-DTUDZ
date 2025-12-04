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
        Schema::table('quan_tri_viens', function (Blueprint $table) {
            // Drop old index and column
            $table->dropIndex(['vai_tro', 'trang_thai']);
            $table->dropColumn('vai_tro');
        });

        Schema::table('quan_tri_viens', function (Blueprint $table) {
            // Add new columns
            $table->foreignId('id_vai_tro')->nullable()->after('mat_khau')->constrained('vai_tros')->onDelete('set null')->comment('ID vai trò');
            $table->boolean('is_master')->default(false)->after('id_vai_tro')->comment('Super Admin có toàn quyền (chỉ 1 tài khoản)');

            // Add new indexes
            $table->index('is_master');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quan_tri_viens', function (Blueprint $table) {
            $table->dropIndex(['is_master']);
            $table->dropForeign(['id_vai_tro']);
            $table->dropColumn(['id_vai_tro', 'is_master']);
        });

        Schema::table('quan_tri_viens', function (Blueprint $table) {
            // Restore old vai_tro column
            $table->tinyInteger('vai_tro')->default(2)->after('mat_khau')->comment('0: SuperAdmin, 1: Data Admin, 2: Agency Admin');
            $table->index(['vai_tro', 'trang_thai']);
        });
    }
};
