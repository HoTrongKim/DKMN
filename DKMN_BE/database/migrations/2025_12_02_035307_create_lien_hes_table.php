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
        Schema::create('lien_hes', function (Blueprint $table) {
            $table->id();
            $table->string('ho_ten', 100);
            $table->string('email', 150);
            $table->string('so_dien_thoai', 20)->nullable();
            $table->string('chu_de', 100);
            $table->text('noi_dung');
            $table->enum('trang_thai', ['moi', 'dang_xu_ly', 'da_tra_loi', 'dong'])->default('moi');
            $table->text('tra_loi')->nullable();
            $table->unsignedBigInteger('nguoi_phu_trach_id')->nullable();
            $table->timestamp('ngay_tra_loi')->nullable();
            $table->timestamp('ngay_tao')->useCurrent();
            $table->timestamp('ngay_cap_nhat')->useCurrent()->useCurrentOnUpdate();
            
            $table->index(['trang_thai'], 'idx_lh_trang_thai');
            $table->index(['ngay_tao'], 'idx_lh_ngay_tao');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lien_hes');
    }
};
