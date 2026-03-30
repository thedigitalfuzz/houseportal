<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
public function up(): void
{
Schema::create('player_agents', function (Blueprint $table) {
$table->id();
$table->string('player_agent_name');
$table->string('facebook_profile')->nullable();
$table->string('facebook_password')->nullable();
$table->string('two_way_verification_code')->nullable();
$table->string('photo')->nullable();
$table->timestamps();
});
}

public function down(): void
{
Schema::dropIfExists('player_agents');
}
};
