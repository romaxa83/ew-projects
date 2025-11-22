<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(
            'tire_sizes',
            static function (Blueprint $table) {
                $table->bigInteger('tire_proportionality_id')
                    ->unsigned()
                    ->nullable()
                    ->change();
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'tire_sizes',
            static function (Blueprint $table) {
                $table->bigInteger('tire_proportionality_id')
                    ->unsigned()
                    ->nullable(false)
                    ->change();
            }
        );
    }
};
