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
        Schema::create('cai_dat_thong_baos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nguoi_dung_id')->unique()->constrained('nguoi_dungs')->onDelete('cascade');
            $table->boolean('email_enabled')->default(true);
            $table->boolean('push_enabled')->default(true);
            $table->boolean('report_status_update')->default(true);
            $table->boolean('report_assigned')->default(true);
            $table->boolean('comment_reply')->default(true);
            $table->boolean('system_announcement')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cai_dat_thong_baos');
    }
};
