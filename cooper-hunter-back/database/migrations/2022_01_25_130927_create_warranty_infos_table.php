<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'warranty_infos',
            static function (Blueprint $table) {
                $table->id();
                $table->string('video_link');
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('warranty_infos');
    }
};
