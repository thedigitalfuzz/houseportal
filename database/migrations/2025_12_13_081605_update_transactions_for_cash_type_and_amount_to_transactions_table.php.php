<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Add type field (cashin/cashout) and amount
            $table->enum('transaction_type', ['cashin','cashout'])->after('game_id')->default('cashin');
            $table->decimal('amount', 20, 2)->after('transaction_type')->default(0);

            // Optional: Keep cashin/cashout for now but not used in UI
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['transaction_type', 'amount']);
        });
    }
};
