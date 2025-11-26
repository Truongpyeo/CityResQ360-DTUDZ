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
        Schema::create('nguoi_dungs', function (Blueprint $table) {
            $table->id();
            $table->string('ho_ten', 120);
            $table->string('email', 190)->unique();
            $table->string('mat_khau', 255);
            $table->string('so_dien_thoai', 20)->nullable();
            $table->tinyInteger('vai_tro')->default(0)->comment('0:citizen, 1:officer');
            $table->string('anh_dai_dien', 255)->nullable();
            $table->tinyInteger('trang_thai')->default(1)->comment('1:active, 0:banned');
            $table->integer('diem_thanh_pho')->default(0)->comment('CityPoint token thưởng');
            $table->boolean('xac_thuc_cong_dan')->default(false)->comment('KYC verified');
            $table->integer('diem_uy_tin')->default(0);
            $table->integer('tong_so_phan_anh')->default(0);
            $table->integer('so_phan_anh_chinh_xac')->default(0);
            $table->float('ty_le_chinh_xac')->default(0)->comment('%');
            $table->tinyInteger('cap_huy_hieu')->default(0)->comment('0:bronze, 1:silver, 2:gold, 3:platinum');
            $table->string('push_token', 255)->nullable()->comment('FCM token');
            $table->json('tuy_chon_thong_bao')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('email');
            $table->index('so_dien_thoai');
            $table->index(['vai_tro', 'trang_thai']);
            $table->index('diem_thanh_pho');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nguoi_dungs');
    }
};
