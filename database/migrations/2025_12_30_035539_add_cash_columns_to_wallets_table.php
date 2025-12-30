<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->decimal('cashin', 15, 2)->default(0)->after('balance_difference');
            $table->decimal('cashout', 15, 2)->default(0)->after('cashin');
            $table->decimal('bonus', 15, 2)->default(0)->after('cashout');
        });
    }

    public function down(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->dropColumn(['cashin', 'cashout', 'bonus']);
        });
    }
};
