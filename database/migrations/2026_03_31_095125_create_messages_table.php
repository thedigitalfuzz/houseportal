<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('channel_id')->nullable(); // nullable for DMs
            $table->unsignedBigInteger('sender_id')->nullable(); // staff/user id
            $table->string('sender_type'); // 'staff' or 'user'
            $table->text('message')->nullable();
            $table->enum('type', ['text'])->default('text'); // for now only text
            $table->timestamps();

            $table->foreign('channel_id')->references('id')->on('channels')->onDelete('cascade');
            //$table->foreign('sender_id')->references('id')->on('staffs')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
