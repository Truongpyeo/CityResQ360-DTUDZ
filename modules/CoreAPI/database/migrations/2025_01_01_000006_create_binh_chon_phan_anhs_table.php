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
        Schema::create('binh_chon_phan_anhs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('phan_anh_id')->constrained('phan_anhs')->onDelete('cascade');
            $table->foreignId('nguoi_dung_id')->constrained('nguoi_dungs')->onDelete('cascade');
            $table->tinyInteger('loai_binh_chon')->comment('1:upvote, 0:downvote');
            $table->timestamps();

            // Indexes
            $table->index('phan_anh_id');
            $table->index('nguoi_dung_id');

            // Unique constraint: một user chỉ vote 1 lần cho 1 report
            $table->unique(['phan_anh_id', 'nguoi_dung_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('binh_chon_phan_anhs');
    }
};
