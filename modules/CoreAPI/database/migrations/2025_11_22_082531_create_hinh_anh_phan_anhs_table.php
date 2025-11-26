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
        Schema::create('hinh_anh_phan_anhs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nguoi_dung_id')->constrained('nguoi_dungs')->onDelete('cascade');
            $table->string('duong_dan_hinh_anh', 500)->comment('URL to original file');
            $table->string('duong_dan_thumbnail', 500)->nullable()->comment('URL to thumbnail');
            $table->string('loai_file', 20)->comment('image or video');
            $table->bigInteger('kich_thuoc')->nullable()->comment('File size in bytes');
            $table->string('dinh_dang', 100)->nullable()->comment('MIME type');
            $table->text('mo_ta')->nullable()->comment('Description');
            $table->string('media_service_id', 100)->nullable()->comment('ID from Media Service');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('nguoi_dung_id');
            $table->index('loai_file');
            $table->index('media_service_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hinh_anh_phan_anhs');
    }
};
