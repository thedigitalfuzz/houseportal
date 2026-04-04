<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('channels', function (Blueprint $table) {
            $table->id(); // BIGINT UNSIGNED
            $table->string('name')->unique();
            $table->enum('type', ['public', 'private', 'announcement'])->default('private');

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('staffs')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('channels');
    }
};
