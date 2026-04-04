<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('channel_user', function (Blueprint $table) {
            $table->id();

            $table->foreignId('channel_id')
                ->constrained('channels')
                ->cascadeOnDelete();

            $table->unsignedBigInteger('user_id');
            $table->string('user_type'); // App\Models\Staff OR App\Models\User

            $table->enum('role', ['admin', 'member'])->default('member');

            $table->timestamps();

            $table->unique(['channel_id', 'user_id', 'user_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('channel_user');
    }
};
