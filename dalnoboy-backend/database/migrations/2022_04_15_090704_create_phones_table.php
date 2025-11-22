<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'phones',
            static function (Blueprint $table)
            {
                $table->id();
                $table->morphs('owner');
                $table->string('phone', 20);
                $table->boolean('is_default')
                    ->default(false);
                $table->unique(['owner_type', 'phone']);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('phones');
    }
};
