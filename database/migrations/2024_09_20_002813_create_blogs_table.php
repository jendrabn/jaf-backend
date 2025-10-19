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
        Schema::create('blogs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('content')->nullable();
            $table->integer('min_read')->nullable()->default(0);
            $table->text('featured_image_description')->nullable();
            $table->boolean('is_publish')->default(true)->index('idx_blogs_is_publish');
            $table->integer('views_count')->default(0);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('blog_category_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            $table->index(['blog_category_id', 'is_publish'], 'idx_blogs_category_publish');
            $table->index(['user_id', 'created_at'], 'idx_blogs_user_created_at');
            $table->index('views_count', 'idx_blogs_views_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blogs');
    }
};
