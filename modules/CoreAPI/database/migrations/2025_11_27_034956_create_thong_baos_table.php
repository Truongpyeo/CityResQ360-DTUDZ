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
        Schema::create('thong_baos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nguoi_dung_id')->constrained('nguoi_dungs')->onDelete('cascade');
            $table->string('tieu_de');
            $table->text('noi_dung');
            $table->string('loai')->default('system')->comment('report_status_update, points_earned, comment_reply, system, etc.');
            $table->boolean('da_doc')->default(false);
            $table->json('du_lieu_mo_rong')->nullable();
            $table->timestamps();
            
            $table->index(['nguoi_dung_id', 'da_doc', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('thong_baos');
    }
};
