<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_points', function (Blueprint $table) {
            $table->id();

            // The game reference
            $table->foreignId('game_id')->constrained('games')->onDelete('cascade');

            // Points
            $table->decimal('points', 12, 2)->default(0);

            // Date of the game point
            $table->date('date');

            // Tracking who created and updated
            $table->unsignedBigInteger('created_by_id')->nullable();
            $table->string('created_by_type')->nullable(); // <-- must be string
            $table->unsignedBigInteger('updated_by_id')->nullable();
            $table->string('updated_by_type')->nullable(); // <-- must be string

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_points');
    }
};
