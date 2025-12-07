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
        Schema::table('co_quan_xu_lys', function (Blueprint $table) {
            // Add latitude and longitude columns for geolocation
            $table->decimal('vi_do', 10, 8)->nullable()->after('dia_chi')->comment('Latitude');
            $table->decimal('kinh_do', 11, 8)->nullable()->after('vi_do')->comment('Longitude');

            // Add index for geospatial queries
            $table->index(['vi_do', 'kinh_do'], 'idx_coordinates');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('co_quan_xu_lys', function (Blueprint $table) {
            $table->dropIndex('idx_coordinates');
            $table->dropColumn(['vi_do', 'kinh_do']);
        });
    }
};
