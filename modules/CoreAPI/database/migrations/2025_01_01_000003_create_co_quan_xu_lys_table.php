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
        Schema::create('co_quan_xu_lys', function (Blueprint $table) {
            $table->id();
            $table->string('ten_co_quan', 150);
            $table->string('email_lien_he', 150)->nullable();
            $table->string('so_dien_thoai', 30)->nullable();
            $table->string('dia_chi', 255)->nullable();
            $table->tinyInteger('cap_do')->default(0)->comment('0:ward, 1:district, 2:city');
            $table->text('mo_ta')->nullable();
            $table->tinyInteger('trang_thai')->default(1)->comment('1:active, 0:inactive');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('cap_do');
            $table->index('trang_thai');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('co_quan_xu_lys');
    }
};
