<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'versions',
            static function (Blueprint $table) {
                $table->id();
                $table->string('recommended_version');
                $table->string('required_version');
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('versions');
    }
};
