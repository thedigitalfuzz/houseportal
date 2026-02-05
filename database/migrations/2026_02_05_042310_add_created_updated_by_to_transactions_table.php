<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by_id')->nullable()->after('staff_user_id');
            $table->string('created_by_type')->nullable()->after('created_by_id');

            $table->unsignedBigInteger('updated_by_id')->nullable()->after('created_by_type');
            $table->string('updated_by_type')->nullable()->after('updated_by_id');

            $table->index(['created_by_id', 'updated_by_id']);
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn([
                'created_by_id',
                'created_by_type',
                'updated_by_id',
                'updated_by_type'
            ]);
        });
    }
};

