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
        Schema::create('chuc_nangs', function (Blueprint $table) {
            $table->id();
            $table->string('ten_chuc_nang', 100)->comment('Tên chức năng');
            $table->string('route_name', 150)->unique()->comment('Route name từ Laravel routes');
            $table->string('nhom_chuc_nang', 50)->nullable()->comment('Nhóm chức năng (dashboard, reports, users, agencies, etc.)');
            $table->text('mo_ta')->nullable()->comment('Mô tả chức năng');
            $table->tinyInteger('trang_thai')->default(1)->comment('0: Khóa, 1: Hoạt động');
            $table->integer('thu_tu')->default(0)->comment('Thứ tự hiển thị');
            $table->timestamps();
            $table->softDeletes();

            $table->index('route_name');
            $table->index('nhom_chuc_nang');
            $table->index('trang_thai');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chuc_nangs');
    }
};
