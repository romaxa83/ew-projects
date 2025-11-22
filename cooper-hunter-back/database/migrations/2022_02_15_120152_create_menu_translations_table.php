<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'menu_translations',
            static function (Blueprint $table) {
                $table->id();

                $table->string('title');
                $table->string('link', 1000);

                $table->foreignId('row_id')
                    ->constrained('menus')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

                $table->string('language');
                $table->foreign('language')
                    ->on('languages')
                    ->references('slug')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_translations');
    }
};
