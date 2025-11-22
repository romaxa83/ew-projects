<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'state_translates',
            static function (Blueprint $table) {
                $table->increments('id');
                $table->string('name');
                $table->unsignedInteger('row_id');

                $table->string('language', 3);

                $table->foreign('language')->references('slug')->on('languages')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');

                $table->foreign('row_id')->references('id')->on('states')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('state_translates');
    }
};
