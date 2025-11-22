<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'distributor_translations',
            static function (Blueprint $table) {
                $table->id();
                $table->string('title');

                $table->foreignId('row_id')
                    ->constrained('distributors')
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
        Schema::dropIfExists('distributor_translations');
    }
};
