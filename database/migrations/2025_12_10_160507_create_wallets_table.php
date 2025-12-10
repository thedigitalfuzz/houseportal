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
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->string('agent');
            $table->string('wallet_name');
            $table->string('wallet_remarks'); // now owner's name
            $table->decimal('current_balance', 15, 2)->default(0); // decimal with 2 digits
            $table->date('date');
            $table->unsignedBigInteger('created_by_id')->nullable();; // required
            $table->string('created_by_type')->nullable();; // required
            $table->unsignedBigInteger('updated_by_id')->nullable(); // optional on creation
            $table->string('updated_by_type')->nullable(); // optional on creation
            $table->timestamps(); // created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
