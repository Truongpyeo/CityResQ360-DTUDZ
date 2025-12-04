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
        Schema::create('quan_tri_viens', function (Blueprint $table) {
            $table->id();
            $table->string('ten_quan_tri', 120);
            $table->string('email', 190)->unique();
            $table->string('mat_khau', 255);
            $table->tinyInteger('vai_tro')->default(1)->comment('0:superadmin, 1:data_admin, 2:agency_admin');
            $table->string('anh_dai_dien', 255)->nullable();
            $table->tinyInteger('trang_thai')->default(1)->comment('1:active, 0:locked');
            $table->timestamp('lan_dang_nhap_cuoi')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('email');
            $table->index(['vai_tro', 'trang_thai']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quan_tri_viens');
    }
};
