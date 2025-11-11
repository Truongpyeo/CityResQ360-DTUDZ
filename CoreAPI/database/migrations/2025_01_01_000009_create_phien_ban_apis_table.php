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
        Schema::create('phien_ban_apis', function (Blueprint $table) {
            $table->id();
            $table->string('phien_ban', 20)->unique();
            $table->text('mo_ta')->nullable();
            $table->date('ngay_phat_hanh')->nullable();
            $table->date('ngay_het_han')->nullable();
            $table->tinyInteger('trang_thai')->default(1)->comment('0:deprecated, 1:active, 2:beta');
            $table->text('ghi_chu_thay_doi')->nullable();
            $table->timestamp('created_at')->useCurrent();

            // Indexes
            $table->index('phien_ban');
            $table->index('trang_thai');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phien_ban_apis');
    }
};
