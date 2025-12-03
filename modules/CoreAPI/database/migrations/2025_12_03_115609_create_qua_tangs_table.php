<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qua_tangs', function (Blueprint $table) {
            $table->id();
            $table->string('ten_qua_tang');
            $table->text('mo_ta')->nullable();
            $table->string('hinh_anh', 500)->nullable();
            $table->integer('so_diem_can');
            $table->integer('so_luong_kho')->default(0);
            $table->integer('da_doi')->default(0);
            $table->enum('loai', ['voucher', 'gift', 'merchandise']);
            $table->string('nha_tai_tro', 255)->nullable();
            $table->date('ngay_het_han')->nullable();
            $table->boolean('trang_thai')->default(true);
            $table->timestamps();

            $table->index('trang_thai');
            $table->index('so_diem_can');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qua_tangs');
    }
};
