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
        Schema::create('chi_tiet_phan_quyens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_vai_tro')->constrained('vai_tros')->onDelete('cascade')->comment('ID vai trò');
            $table->foreignId('id_chuc_nang')->constrained('chuc_nangs')->onDelete('cascade')->comment('ID chức năng');
            $table->timestamps();

            // Unique constraint để tránh duplicate
            $table->unique(['id_vai_tro', 'id_chuc_nang'], 'unique_role_permission');

            $table->index('id_vai_tro');
            $table->index('id_chuc_nang');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chi_tiet_phan_quyens');
    }
};
