<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('wallet_details', function (Blueprint $table) {
            $table->string('status')->default('active')->after('wallet_remarks');
            $table->date('status_date')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('wallet_details', function (Blueprint $table) {
            $table->dropColumn(['status', 'status_date']);
        });
    }
};
