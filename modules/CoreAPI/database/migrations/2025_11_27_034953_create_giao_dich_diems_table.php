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
        Schema::create('giao_dich_diems', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nguoi_dung_id')->constrained('nguoi_dungs')->onDelete('cascade');
            $table->tinyInteger('loai_giao_dich')->comment('0: reward, 1: spend, 2: admin_adjust');
            $table->integer('so_diem');
            $table->integer('so_du_truoc');
            $table->integer('so_du_sau');
            $table->string('ly_do');
            $table->unsignedBigInteger('lien_ket_id')->nullable();
            $table->string('lien_ket_loai')->nullable()->comment('phan_anh, phan_thuong, etc.');
            $table->timestamps();
            
            $table->index(['nguoi_dung_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('giao_dich_diems');
    }
};
