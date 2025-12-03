<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diem_thuongs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nguoi_dung_id')->constrained('nguoi_dungs')->onDelete('cascade');
            $table->integer('so_du_hien_tai')->default(0);
            $table->integer('tong_diem_kiem_duoc')->default(0);
            $table->integer('tong_diem_da_tieu')->default(0);
            $table->timestamps();

            $table->unique('nguoi_dung_id');
            $table->index('so_du_hien_tai');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diem_thuongs');
    }
};
