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
        Schema::create('danh_muc_phan_anhs', function (Blueprint $table) {
            $table->id();
            $table->string('ten_danh_muc', 100)->unique();
            $table->string('ma_danh_muc', 50)->unique()->comment('traffic, environment, fire, etc.');
            $table->text('mo_ta')->nullable();
            $table->string('icon', 50)->nullable()->comment('Lucide icon name');
            $table->string('mau_sac', 20)->default('#3B82F6')->comment('Hex color code');
            $table->integer('thu_tu_hien_thi')->default(0);
            $table->boolean('trang_thai')->default(true)->comment('1:active, 0:inactive');
            $table->timestamps();

            // Indexes
            $table->index('trang_thai');
            $table->index('thu_tu_hien_thi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('danh_muc_phan_anhs');
    }
};
