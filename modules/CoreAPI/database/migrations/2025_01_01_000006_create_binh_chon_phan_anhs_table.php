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
        Schema::create('binh_chon_phan_anhs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('phan_anh_id')->constrained('phan_anhs')->onDelete('cascade');
            $table->foreignId('nguoi_dung_id')->constrained('nguoi_dungs')->onDelete('cascade');
            $table->tinyInteger('loai_binh_chon')->comment('1:upvote, 0:downvote');
            $table->timestamps();

            // Indexes
            $table->index('phan_anh_id');
            $table->index('nguoi_dung_id');

            // Unique constraint: một user chỉ vote 1 lần cho 1 report
            $table->unique(['phan_anh_id', 'nguoi_dung_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('binh_chon_phan_anhs');
    }
};
