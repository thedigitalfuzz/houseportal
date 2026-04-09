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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();

            $table->string('type'); // wallet, transaction, game, player, message
            $table->text('message');

            $table->nullableMorphs('notifiable'); // User or Staff

            $table->boolean('is_read')->default(false);

            $table->string('redirect_to')->nullable(); // route name or URL

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
