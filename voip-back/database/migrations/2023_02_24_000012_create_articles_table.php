<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('articles',
            static function (Blueprint $table)
            {
                $table->increments('id');

                $table->string('status', 10)
                    ->default('draft');
                $table->unsignedInteger('sort')->default(0);
                $table->timestamp('published_at')->nullable();
                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
