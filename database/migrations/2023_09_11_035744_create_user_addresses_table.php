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
        Schema::create('user_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('province_id')->nullable()->index('idx_user_addresses_province_id');
            $table->unsignedBigInteger('city_id')->nullable()->index('idx_user_addresses_city_id');
            $table->unsignedBigInteger('district_id')->nullable()->index('idx_user_addresses_district_id');
            $table->unsignedBigInteger('subdistrict_id')->nullable()->index('idx_user_addresses_subdistrict_id');
            $table->string('name', 100);
            $table->string('phone', 25);
            $table->string('zip_code')->nullable();
            $table->string('address');
            $table->timestamps();
            $table->index(['user_id', 'created_at'], 'idx_user_addresses_user_created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_addresses');
    }
};
