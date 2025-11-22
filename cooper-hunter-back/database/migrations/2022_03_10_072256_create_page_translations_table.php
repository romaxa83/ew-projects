<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'page_translations',
            static function (Blueprint $table)
            {
                $table->id();
                $table->string('title');
                $table->text('description');
                $table->string('slug');
                $table->unsignedBigInteger('row_id');

                $table->string('language');

                $table->foreign('row_id')
                    ->on('pages')
                    ->references('id')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

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
        Schema::dropIfExists('page_translations');
    }
};
