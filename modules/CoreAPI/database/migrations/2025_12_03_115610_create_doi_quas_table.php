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
        Schema::create('doi_quas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nguoi_dung_id')->constrained('nguoi_dungs')->onDelete('cascade');
            $table->foreignId('qua_tang_id')->constrained('qua_tangs')->onDelete('cascade');
            $table->foreignId('diem_transaction_id')->constrained('diem_transactions')->onDelete('cascade');
            $table->integer('so_diem_tieu');
            $table->string('ma_voucher', 100)->nullable();
            $table->enum('trang_thai', ['pending', 'approved', 'delivered', 'cancelled'])->default('pending');
            $table->timestamp('ngay_doi')->useCurrent();
            $table->date('ngay_giao')->nullable();
            $table->text('ghi_chu')->nullable();
            $table->timestamps();

            $table->index('nguoi_dung_id');
            $table->index('trang_thai');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doi_quas');
    }
};
