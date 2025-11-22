<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'chat_menu_translations',
            static function (Blueprint $table)
            {
                $table->id();

                $table->string('title');

                $table
                    ->foreignId('row_id')
                    ->constrained('chat_menus')
                    ->references('id')
                    ->cascadeOnDelete();
                $table->string('language');
                $table->foreign('language')
                    ->on('languages')
                    ->references('slug')
                    ->cascadeOnDelete();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_menu_translations');
    }
};
