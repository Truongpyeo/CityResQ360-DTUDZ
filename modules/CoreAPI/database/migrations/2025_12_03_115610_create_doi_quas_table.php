<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doi_quas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nguoi_dung_id')->constrained('nguoi_dungs')->onDelete('cascade');
            $table->foreignId('qua_tang_id')->constrained('qua_tangs')->onDelete('cascade');
            $table->foreignId('diem_transaction_id')->constrained('diem_transactions')->onDelete('cascade');
            $table->integer('so_diem_tieu');
            $table->string('ma_voucher', 100)->nullable();
            $table->enum('trang_thai', ['pending', 'approved', 'delivered', 'cancelled'])->default('pending');
            $table->timestamp('ngay_doi')->useCurrent();
            $table->date('ngay_giao')->nullable();
            $table->text('ghi_chu')->nullable();
            $table->timestamps();

            $table->index('nguoi_dung_id');
            $table->index('trang_thai');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doi_quas');
    }
};
