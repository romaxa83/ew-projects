<?php

use App\Models\SendPulse\AuthToken;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(AuthToken::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->string('access_token', 1000);
            $table->string('token_type');
            $table->integer('expires_in');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(AuthToken::TABLE_NAME);
    }
};
