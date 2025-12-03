<?php

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
