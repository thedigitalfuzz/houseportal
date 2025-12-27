<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->decimal('net_transaction', 20, 2)->default(0)->after('balance_difference');
        });
    }

    public function down()
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->dropColumn('net_transaction');
        });
    }
};
