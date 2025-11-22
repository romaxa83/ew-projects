<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'warranty_info_package_translations',
            static function (Blueprint $table) {
                $table->id();

                $table->string('title');
                $table->text('description');

                $table->foreignId('row_id')
                    ->constrained('warranty_info_packages')
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
        Schema::dropIfExists('warranty_info_package_translations');
    }
};
