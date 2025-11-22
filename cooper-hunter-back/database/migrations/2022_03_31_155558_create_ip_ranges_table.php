<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'ip_ranges',
            static function (Blueprint $table) {
                $table->id();

                $table->unsignedInteger('ip_from');
                $table->unsignedInteger('ip_to');

                $table->string('state');
                $table->string('city');

                $table->point('coordinates')->spatialIndex();

                $table->string('zip')->index();

                $table->index(['ip_from', 'ip_to'], 'ips_index');
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('ip_ranges');
    }
};
