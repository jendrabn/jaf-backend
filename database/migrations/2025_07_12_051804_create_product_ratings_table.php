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
        Schema::create('product_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained()->cascadeOnDelete();
            $table->integer('rating');
            $table->mediumText('comment')->nullable();
            $table->boolean('is_anonymous')->default(false);
            $table->boolean('is_publish')->default(true)->index('idx_product_ratings_is_publish');
            $table->timestamps();
            $table->unique('order_item_id', 'uniq_product_ratings_order_item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_ratings');
    }
};
