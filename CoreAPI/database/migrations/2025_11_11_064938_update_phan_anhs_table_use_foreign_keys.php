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
        Schema::table('phan_anhs', function (Blueprint $table) {
            // Drop old tinyInteger columns
            $table->dropColumn(['danh_muc', 'uu_tien']);

            // Add foreign key columns
            $table->foreignId('danh_muc_id')->after('mo_ta')->constrained('danh_muc_phan_anhs')->onDelete('restrict');
            $table->foreignId('uu_tien_id')->after('trang_thai')->constrained('muc_uu_tiens')->onDelete('restrict');

            // Add indexes
            $table->index('danh_muc_id');
            $table->index('uu_tien_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('phan_anhs', function (Blueprint $table) {
            // Drop foreign keys and columns
            $table->dropForeign(['danh_muc_id']);
            $table->dropForeign(['uu_tien_id']);
            $table->dropColumn(['danh_muc_id', 'uu_tien_id']);

            // Restore old columns
            $table->tinyInteger('danh_muc')->after('mo_ta')->comment('0:traffic, 1:environment, 2:fire, 3:waste, 4:flood, 5:other');
            $table->tinyInteger('uu_tien')->default(1)->after('trang_thai')->comment('0:low, 1:medium, 2:high, 3:urgent');
        });
    }
};
