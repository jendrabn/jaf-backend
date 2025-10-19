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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_category_id')->nullable()->constrained('product_categories')->onDelete('set null');
            $table->foreignId('product_brand_id')->nullable()->constrained('product_brands')->onDelete('set null');
            $table->string('name', 200)->unique('uniq_products_name');
            $table->fullText('name', 'products_fulltext_name');
            $table->string('slug')->unique('uniq_products_slug');
            $table->integer('weight')->comment('gram');
            $table->integer('price');
            $table->integer('stock')->default(1);
            $table->text('description')->nullable();
            $table->boolean('is_publish')->default(true)->index('idx_products_is_publish');
            $table->enum('sex', [1, 2, 3])->nullable()->comment('1:male, 2:female, 3:unisex')->index('idx_products_sex');
            $table->index('price', 'idx_products_price'); // price range filtering
            $table->index(['is_publish', 'product_category_id'], 'idx_products_publish_category');
            $table->index(['is_publish', 'product_brand_id'], 'idx_products_publish_brand');
            $table->timestamps();
            $table->index('created_at', 'idx_products_created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
