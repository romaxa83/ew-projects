<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'region_translates',
            static function (Blueprint $table)
            {
                $table->string('title');

                $table->foreignId('row_id')
                    ->constrained('regions')
                    ->references('id')
                    ->cascadeOnDelete();

                $table->string('language');
                $table->foreign('language')
                    ->on('languages')
                    ->references('slug')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

                $table->primary(['row_id', 'language'], 'pk');
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('region_translates');
    }
};
