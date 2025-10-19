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
        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();

            // Core fields
            $table->string('name', 120);
            $table->string('email', 191)->index();
            $table->string('phone', 30)->nullable();
            $table->longText('message');

            // Status & handling
            $table->enum('status', ['new', 'in_progress', 'resolved', 'spam'])->default('new')->index();
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('handled_at')->nullable();
            $table->text('notes')->nullable();

            // Request meta
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();

            // Timestamps
            $table->timestamps();

            // Performance indexes
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
    }
};
