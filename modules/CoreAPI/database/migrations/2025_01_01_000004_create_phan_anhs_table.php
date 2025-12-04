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
        Schema::create('phan_anhs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nguoi_dung_id')->constrained('nguoi_dungs')->onDelete('cascade');
            $table->string('tieu_de', 255);
            $table->text('mo_ta');
            $table->tinyInteger('danh_muc')->comment('0:traffic, 1:environment, 2:fire, 3:waste, 4:flood, 5:other');
            $table->tinyInteger('trang_thai')->default(0)->comment('0:pending, 1:verified, 2:in_progress, 3:resolved, 4:rejected');
            $table->tinyInteger('uu_tien')->default(1)->comment('0:low, 1:medium, 2:high, 3:urgent');
            $table->decimal('vi_do', 10, 7)->nullable();
            $table->decimal('kinh_do', 10, 7)->nullable();
            $table->string('dia_chi', 255)->nullable();
            $table->string('nhan_ai', 100)->nullable()->comment('AI classification result');
            $table->float('do_tin_cay')->nullable()->comment('AI confidence 0-1');
            $table->foreignId('co_quan_phu_trach_id')->nullable()->constrained('co_quan_xu_lys')->onDelete('set null');
            $table->boolean('la_cong_khai')->default(true);
            $table->integer('luot_ung_ho')->default(0);
            $table->integer('luot_khong_ung_ho')->default(0);
            $table->integer('luot_xem')->default(0);
            $table->timestamp('han_phan_hoi')->nullable();
            $table->integer('thoi_gian_phan_hoi_thuc_te')->nullable()->comment('minutes');
            $table->integer('thoi_gian_giai_quyet')->nullable()->comment('hours');
            $table->tinyInteger('danh_gia_hai_long')->nullable()->comment('1-5 stars');
            $table->boolean('la_trung_lap')->default(false);
            $table->foreignId('trung_lap_voi_id')->nullable()->constrained('phan_anhs')->onDelete('set null');
            $table->json('the_tags')->nullable();
            $table->json('du_lieu_mo_rong')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('nguoi_dung_id');
            $table->index('danh_muc');
            $table->index('trang_thai');
            $table->index(['vi_do', 'kinh_do']);
            $table->index('co_quan_phu_trach_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phan_anhs');
    }
};
