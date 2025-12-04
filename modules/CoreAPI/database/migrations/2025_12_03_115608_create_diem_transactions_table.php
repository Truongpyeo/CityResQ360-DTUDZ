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
    public function up(): void
    {
        Schema::create('diem_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('diem_thuong_id')->constrained('diem_thuongs')->onDelete('cascade');
            $table->foreignId('nguoi_dung_id')->constrained('nguoi_dungs')->onDelete('cascade');
            $table->enum('loai_giao_dich', ['earn', 'redeem']);
            $table->integer('so_diem');
            $table->string('ly_do', 100);
            $table->text('mo_ta')->nullable();
            $table->string('lien_ket_den', 50)->nullable();
            $table->bigInteger('id_lien_ket')->nullable();
            $table->integer('so_du_truoc');
            $table->integer('so_du_sau');
            $table->timestamps();

            $table->index('nguoi_dung_id');
            $table->index(['lien_ket_den', 'id_lien_ket', 'ly_do']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diem_transactions');
    }
};
