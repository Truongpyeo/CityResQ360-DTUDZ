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
        Schema::table('binh_luan_phan_anhs', function (Blueprint $table) {
            $table->integer('luot_thich')->default(0)->after('noi_dung');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('binh_luan_phan_anhs', function (Blueprint $table) {
            $table->dropColumn('luot_thich');
        });
    }
};
