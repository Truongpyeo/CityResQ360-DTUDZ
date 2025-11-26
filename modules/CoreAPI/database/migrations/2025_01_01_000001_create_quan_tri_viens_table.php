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
        Schema::create('quan_tri_viens', function (Blueprint $table) {
            $table->id();
            $table->string('ten_quan_tri', 120);
            $table->string('email', 190)->unique();
            $table->string('mat_khau', 255);
            $table->tinyInteger('vai_tro')->default(1)->comment('0:superadmin, 1:data_admin, 2:agency_admin');
            $table->string('anh_dai_dien', 255)->nullable();
            $table->tinyInteger('trang_thai')->default(1)->comment('1:active, 0:locked');
            $table->timestamp('lan_dang_nhap_cuoi')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('email');
            $table->index(['vai_tro', 'trang_thai']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quan_tri_viens');
    }
};
