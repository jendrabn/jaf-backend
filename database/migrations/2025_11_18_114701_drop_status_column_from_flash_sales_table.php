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
        if (! Schema::hasColumn('flash_sales', 'status')) {
            return;
        }

        Schema::table('flash_sales', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('flash_sales', 'status')) {
            return;
        }

        Schema::table('flash_sales', function (Blueprint $table) {
            $table->enum('status', ['scheduled', 'running', 'finished'])->default('scheduled')->index();
        });
    }
};
