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
        Schema::create('muc_uu_tiens', function (Blueprint $table) {
            $table->id();
            $table->string('ten_muc', 50)->unique();
            $table->string('ma_muc', 50)->unique()->comment('low, medium, high, urgent');
            $table->text('mo_ta')->nullable();
            $table->integer('cap_do')->unique()->comment('0:low, 1:medium, 2:high, 3:urgent');
            $table->string('mau_sac', 20)->default('#6B7280')->comment('Hex color code');
            $table->integer('thoi_gian_phan_hoi_toi_da')->default(72)->comment('hours');
            $table->boolean('trang_thai')->default(true)->comment('1:active, 0:inactive');
            $table->timestamps();

            // Indexes
            $table->index('trang_thai');
            $table->index('cap_do');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('muc_uu_tiens');
    }
};
