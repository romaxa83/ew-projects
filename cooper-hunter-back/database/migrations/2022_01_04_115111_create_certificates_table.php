<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'certificates',
            static function (Blueprint $table) {
                $table->id();
                $table->foreignId('certificate_type_id')
                    ->constrained()
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

                $table->string('number');
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
