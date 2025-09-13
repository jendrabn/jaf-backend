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
        Schema::create('shippings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->text('address');
            $table->string('courier');
            $table->string('courier_name', 100)->nullable();
            $table->string('service', 50);
            $table->string('service_name', 100)->nullable();
            $table->string('etd', 50)->nullable();
            $table->integer('weight');
            $table->string('tracking_number')->unique()->nullable();
            $table->enum('status', [
                'pending',
                'processing',
                'shipped',
            ]);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shippings');
    }
};
