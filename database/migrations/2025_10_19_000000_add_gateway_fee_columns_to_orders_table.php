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
        // Tambahkan kolom hanya jika belum ada (idempotent)
        if (! Schema::hasColumn('orders', 'gateway_fee')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->bigInteger('gateway_fee')->default(0)->after('shipping_cost');
            });
        }

        if (! Schema::hasColumn('orders', 'gateway_fee_name')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->string('gateway_fee_name')->nullable()->after('gateway_fee');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop kolom hanya jika ada
        if (Schema::hasColumn('orders', 'gateway_fee_name')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('gateway_fee_name');
            });
        }

        if (Schema::hasColumn('orders', 'gateway_fee')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('gateway_fee');
            });
        }
    }
};
