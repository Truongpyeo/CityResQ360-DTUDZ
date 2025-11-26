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
        Schema::create('nhat_ky_he_thongs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('nguoi_dung_id')->nullable();
            $table->string('hanh_dong', 100);
            $table->string('loai_doi_tuong', 100)->nullable();
            $table->unsignedBigInteger('id_doi_tuong')->nullable();
            $table->json('du_lieu_meta')->nullable();
            $table->string('dia_chi_ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();

            // Indexes
            $table->index('nguoi_dung_id');
            $table->index('hanh_dong');
            $table->index(['loai_doi_tuong', 'id_doi_tuong']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nhat_ky_he_thongs');
    }
};
