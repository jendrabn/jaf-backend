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
            $table->enum('promo_type', ['limit', 'period', 'product']);
            $table->string('code')->unique()->nullable();
            $table->enum('discount_type', ['fixed', 'percentage']);
            $table->bigInteger('discount_amount')->nullable();
            $table->integer('limit')->nullable();
            $table->integer('limit_per_user')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
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
