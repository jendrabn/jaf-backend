<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 200);
            $table->integer('weight');
            $table->bigInteger('price');
            $table->integer('discount_in_percent')->default(0);
            $table->bigInteger('price_after_discount')->nullable();
            $table->integer('quantity');
            $table->timestamps();
            $table->index(['order_id', 'product_id'], 'idx_order_items_order_product');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
