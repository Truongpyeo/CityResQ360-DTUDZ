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
        Schema::create('cau_hinh_he_thongs', function (Blueprint $table) {
            $table->id();
            $table->string('khoa_cau_hinh', 100)->unique();
            $table->text('gia_tri');
            $table->tinyInteger('loai_du_lieu')->default(0)->comment('0:string, 1:integer, 2:float, 3:boolean, 4:json');
            $table->text('mo_ta')->nullable();
            $table->string('nhom', 50)->nullable();
            $table->timestamps();

            // Indexes
            $table->index('khoa_cau_hinh');
            $table->index('nhom');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cau_hinh_he_thongs');
    }
};
