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
        Schema::create('cau_hinh_he_thongs', function (Blueprint $table) {
            $table->id();
            $table->string('khoa_cau_hinh', 100)->unique();
            $table->text('gia_tri');
            $table->tinyInteger('loai_du_lieu')->default(0)->comment('0:string, 1:integer, 2:float, 3:boolean, 4:json');
            $table->text('mo_ta')->nullable();
            $table->string('nhom', 50)->nullable();
            $table->timestamps();

            // Indexes
            $table->index('khoa_cau_hinh');
            $table->index('nhom');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cau_hinh_he_thongs');
    }
};
