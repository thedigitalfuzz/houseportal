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
        Schema::table('game_points', function (Blueprint $table) {
            $table->decimal('recharge_points', 12, 2)->default(0)->after('points');
            $table->decimal('total_starting_points', 12, 2)->default(0)->after('recharge_points');
            $table->decimal('used_points', 12, 2)->nullable()->after('total_starting_points');
        });
    }

    public function down(): void
    {
        Schema::table('game_points', function (Blueprint $table) {
            $table->dropColumn([
                'recharge_points',
                'total_starting_points',
                'used_points',
            ]);
        });
    }

};
