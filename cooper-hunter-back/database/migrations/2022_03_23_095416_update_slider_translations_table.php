<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(
            'slider_translations',
            static function (Blueprint $table)
            {
                $table->string('title')
                    ->nullable()
                    ->change();
                $table->text('description')
                    ->nullable()
                    ->change();
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'slider_translations',
            static function (Blueprint $table)
            {
                $table->string('title')
                    ->change();
                $table->text('description')
                    ->change();
            }
        );
    }
};
