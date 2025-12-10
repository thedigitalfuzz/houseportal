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
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('cash_tag')->nullable()->after('deposit');
            $table->string('wallet_name')->nullable()->after('cash_tag');
            $table->text('wallet_remarks')->nullable()->after('wallet_name');
        });
    }

    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['cash_tag','wallet_name','wallet_remarks']);
        });
    }
};
