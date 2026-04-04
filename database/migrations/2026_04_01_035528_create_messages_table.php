<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();

            // ✅ THIS LINE FIXES YOUR ERROR
            $table->foreignId('channel_id')
                ->constrained('channels')
                ->cascadeOnDelete();

            $table->unsignedBigInteger('sender_id')->nullable();
            $table->string('sender_type');

            $table->text('message')->nullable();
            $table->enum('type', ['text'])->default('text');
            $table->json('reactions')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
