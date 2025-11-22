<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'fcm_tokens',
            static function (Blueprint $table)
            {
                $table->id();
                $table->morphs('member');
                $table->string('token', 2000);
                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('fcm_tokens');
    }
};
