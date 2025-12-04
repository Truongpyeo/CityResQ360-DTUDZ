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
        Schema::create('hinh_anh_phan_anhs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nguoi_dung_id')->constrained('nguoi_dungs')->onDelete('cascade');
            $table->string('duong_dan_hinh_anh', 500)->comment('URL to original file');
            $table->string('duong_dan_thumbnail', 500)->nullable()->comment('URL to thumbnail');
            $table->string('loai_file', 20)->comment('image or video');
            $table->bigInteger('kich_thuoc')->nullable()->comment('File size in bytes');
            $table->string('dinh_dang', 100)->nullable()->comment('MIME type');
            $table->text('mo_ta')->nullable()->comment('Description');
            $table->string('media_service_id', 100)->nullable()->comment('ID from Media Service');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('nguoi_dung_id');
            $table->index('loai_file');
            $table->index('media_service_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hinh_anh_phan_anhs');
    }
};
