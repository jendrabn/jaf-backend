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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->enum('method', ['bank', 'ewallet', 'gateway'])->index('idx_payments_method');
            $table->text('info');
            $table->bigInteger('amount');
            $table->enum('status', ['pending', 'cancelled', 'realeased'])->index('idx_payments_status');
            $table->timestamps();
            $table->index('created_at', 'idx_payments_created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
