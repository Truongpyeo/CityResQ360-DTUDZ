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
        Schema::create('binh_luan_phan_anhs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('phan_anh_id')->constrained('phan_anhs')->onDelete('cascade');
            $table->foreignId('nguoi_dung_id')->constrained('nguoi_dungs')->onDelete('cascade');
            $table->text('noi_dung');
            $table->boolean('la_chinh_thuc')->default(false)->comment('từ cơ quan');
            $table->foreignId('binh_luan_cha_id')->nullable()->constrained('binh_luan_phan_anhs')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('phan_anh_id');
            $table->index('nguoi_dung_id');
            $table->index('binh_luan_cha_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('binh_luan_phan_anhs');
    }
};
