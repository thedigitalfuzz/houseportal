<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['daily', 'weekly', 'monthly']);
            $table->date('period_start'); // start of the day/week/month
            $table->date('period_end');   // end of the day/week/month
            $table->string('title');      // e.g. "Report for 2026-January-01"
            $table->longText('payload');  // JSON of all computed data
            $table->timestamps();

            $table->unique(['type', 'period_start', 'period_end'], 'unique_report_period');
        });
    }

    public function down()
    {
        Schema::dropIfExists('reports');
    }
};
