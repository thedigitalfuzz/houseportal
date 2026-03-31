<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('channels', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // channel name
            $table->enum('type', ['public', 'private', 'announcement'])->default('public');
            $table->unsignedBigInteger('created_by')->nullable(); // user or staff id
            $table->foreign('created_by')->references('id')->on('staffs')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('channels');
    }
};
