<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Wezom\Settings\Models\Setting;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create(Setting::TABLE, function (Blueprint $table) {
            $table->id();

            $table->string('key')->unique();
            $table->text('value')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(Setting::TABLE);
    }
};
