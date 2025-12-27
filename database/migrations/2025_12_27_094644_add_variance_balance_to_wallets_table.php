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
            $table->string('variance_balance')->nullable()->default(0)->after('net_transaction');
        });
    }

    public function down()
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->dropColumn('variance_balance');
        });
    }
};
