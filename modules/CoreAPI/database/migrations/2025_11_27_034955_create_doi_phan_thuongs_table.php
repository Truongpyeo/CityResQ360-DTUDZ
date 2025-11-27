<?php

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
        Schema::create('doi_phan_thuongs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nguoi_dung_id')->constrained('nguoi_dungs')->onDelete('cascade');
            $table->foreignId('phan_thuong_id')->constrained('phan_thuongs')->onDelete('cascade');
            $table->integer('so_diem_su_dung');
            $table->string('ma_voucher')->unique();
            $table->tinyInteger('trang_thai')->default(0)->comment('0: pending, 1: approved, 2: used, 3: expired');
            $table->timestamp('ngay_su_dung')->nullable();
            $table->timestamps();
            
            $table->index(['nguoi_dung_id', 'trang_thai']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doi_phan_thuongs');
    }
};
