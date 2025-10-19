<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->bigInteger('total_price');
            $table->bigInteger('discount')->default(0);
            $table->string('discount_name')->nullable();
            $table->bigInteger('tax_amount')->default(0);
            $table->string('tax_name')->nullable();
            $table->bigInteger('shipping_cost');
            $table->bigInteger('gateway_fee')->default(0);
            $table->string('note', 200)->nullable();
            $table->string('cancel_reason', 200)->nullable();
            $table->enum('status', [
                'pending_payment',
                'pending',
                'processing',
                'on_delivery',
                'completed',
                'cancelled'
            ])->index('idx_orders_status');
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->index('created_at', 'idx_orders_created_at');
            $table->index(['status', 'created_at'], 'idx_orders_status_created_at'); // frequent status filter with date range
            $table->index(['user_id', 'created_at'], 'idx_orders_user_created_at');
        });

        // if (env('DB_CONNECTION') === 'mysql') {
        //     DB::update('ALTER TABLE orders AUTO_INCREMENT = 1000000');
        // }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
