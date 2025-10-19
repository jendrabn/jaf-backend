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
        Schema::create('contact_replies', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->foreignId('contact_message_id')
                ->constrained('contact_messages')
                ->cascadeOnDelete();
            $table->foreignId('admin_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Payload
            $table->string('subject');
            $table->longText('body');

            // Status & delivery
            $table->enum('status', ['draft', 'sent', 'failed'])->default('draft')->index();
            $table->timestamp('sent_at')->nullable();
            $table->text('error_message')->nullable();

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
        Schema::dropIfExists('contact_replies');
    }
};
