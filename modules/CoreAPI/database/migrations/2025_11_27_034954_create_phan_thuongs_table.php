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
        Schema::create('phan_thuongs', function (Blueprint $table) {
            $table->id();
            $table->string('ten_phan_thuong');
            $table->text('mo_ta')->nullable();
            $table->tinyInteger('loai')->default(0)->comment('0: voucher, 1: gift, 2: service');
            $table->integer('so_diem_can');
            $table->string('hinh_anh')->nullable();
            $table->integer('so_luong')->default(0);
            $table->integer('so_luong_con_lai')->default(0);
            $table->date('ngay_het_han')->nullable();
            $table->tinyInteger('trang_thai')->default(1)->comment('0: inactive, 1: active');
            $table->timestamps();
            
            $table->index(['trang_thai', 'ngay_het_han']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phan_thuongs');
    }
};
