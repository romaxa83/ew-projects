<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'commercial_settings',
            static function (Blueprint $table) {
                $table->id();
                $table->string('nextcloud_link')->nullable();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('commercial_settings');
    }
};
