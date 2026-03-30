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
        Schema::table('player_agents', function (Blueprint $table) {
            $table->string('email_id')->nullable()->after('facebook_password');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_agents');
    }
};
