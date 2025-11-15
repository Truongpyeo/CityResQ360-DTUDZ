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
        Schema::create('muc_uu_tiens', function (Blueprint $table) {
            $table->id();
            $table->string('ten_muc', 50)->unique();
            $table->string('ma_muc', 50)->unique()->comment('low, medium, high, urgent');
            $table->text('mo_ta')->nullable();
            $table->integer('cap_do')->unique()->comment('0:low, 1:medium, 2:high, 3:urgent');
            $table->string('mau_sac', 20)->default('#6B7280')->comment('Hex color code');
            $table->integer('thoi_gian_phan_hoi_toi_da')->default(72)->comment('hours');
            $table->boolean('trang_thai')->default(true)->comment('1:active, 0:inactive');
            $table->timestamps();

            // Indexes
            $table->index('trang_thai');
            $table->index('cap_do');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('muc_uu_tiens');
    }
};
