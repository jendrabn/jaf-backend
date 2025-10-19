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
        Schema::create('banks', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('code', 3)->nullable()->unique('uniq_banks_code');
            $table->string('account_name', 100);
            $table->string('account_number', 100)->unique('uniq_banks_account_number');
            $table->index('name', 'idx_banks_name'); // lookup by name when filtering lists
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banks');
    }
};
