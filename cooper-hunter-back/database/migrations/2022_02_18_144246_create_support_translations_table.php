<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'support_translations',
            static function (Blueprint $table) {
                $table->id();

                $table->string('short_description', 1000);
                $table->text('description');
                $table->string('working_time', 1000);
                $table->string('video_link', 1000);

                $table->foreignId('row_id')
                    ->constrained('supports')
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
        Schema::dropIfExists('support_translations');
    }
};
