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
        Schema::create('vai_tros', function (Blueprint $table) {
            $table->id();
            $table->string('ten_vai_tro', 100)->comment('Tên vai trò');
            $table->string('slug', 100)->unique()->comment('Slug vai trò (unique)');
            $table->text('mo_ta')->nullable()->comment('Mô tả vai trò');
            $table->tinyInteger('trang_thai')->default(1)->comment('0: Khóa, 1: Hoạt động');
            $table->timestamps();
            $table->softDeletes();

            $table->index('slug');
            $table->index('trang_thai');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vai_tros');
    }
};
