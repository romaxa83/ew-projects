<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'states',
            static function (Blueprint $table) {
                $table->increments('id');

                $table->string('short_name', 5);

                $table->boolean('status')->default(true);
                $table->boolean('hvac_license')->default(false);
                $table->boolean('epa_license')->default(false);

                $table->timestamps();

                $table->unique(['short_name']);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('states');
    }
};
