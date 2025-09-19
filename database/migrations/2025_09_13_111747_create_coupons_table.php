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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->mediumText('description');
            $table->enum('promo_type', ['limit', 'period', 'product'])->index('idx_coupons_promo_type');
            $table->string('code')->unique()->nullable();
            $table->enum('discount_type', ['fixed', 'percentage'])->index('idx_coupons_discount_type');
            $table->bigInteger('discount_amount')->nullable();
            $table->integer('limit')->nullable();
            $table->integer('limit_per_user')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true)->index('idx_coupons_is_active');
            $table->timestamps();
            $table->index(['is_active', 'promo_type'], 'idx_coupons_active_type');
            $table->index(['is_active', 'end_date'], 'idx_coupons_active_end_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
