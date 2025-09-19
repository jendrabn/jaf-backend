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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            // info dasar
            $table->string('description', 255)->nullable();
            $table->string('event', 30)->nullable()->index(); // created|updated|deleted|restored|...

            // subject polymorphic (model yang diaudit)
            // menghasilkan: subject_type (string), subject_id (unsignedBigInteger) + index
            $table->nullableMorphs('subject');

            // user yang melakukan aksi (opsional)
            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete()
                ->cascadeOnUpdate();

            // payload perubahan
            $table->json('before')->nullable();    // nilai lama (hanya field yang berubah / snapshot tergantung event)
            $table->json('after')->nullable();     // nilai baru
            $table->json('changed')->nullable();   // daftar nama field yang berubah (array of strings)
            $table->json('meta')->nullable();      // metadata tambahan (route name, action, guard, locale, env, versi app, dsb)

            // legacy/kompatibilitas lama (opsional dipakai)
            $table->json('properties')->nullable(); // ringkasan gabungan before/after/changed (legacy)
            $table->string('host', 255)->nullable(); // host lama (tetap disediakan)

            // konteks request
            $table->text('url')->nullable();
            $table->string('method', 10)->nullable();
            $table->string('ip', 45)->nullable();         // IPv4/IPv6
            $table->text('user_agent')->nullable();
            $table->uuid('request_id')->nullable()->index(); // untuk mengelompokkan banyak log dalam 1 request

            $table->timestamps();

            // index bantu
            $table->index(['event', 'created_at']);
            $table->index(['user_id', 'event']);
            // nullableMorphs sudah membuat index bawaan, tapi kalau ingin composite:
            // $table->index(['subject_type', 'subject_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
