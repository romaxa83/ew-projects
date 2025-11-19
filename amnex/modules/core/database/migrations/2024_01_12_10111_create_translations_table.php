<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->string('namespace')->nullable();
            $table->string('side');
            $table->string('key');
            $table->string('language', 3);
            $table->foreign('language')
                ->on('languages')
                ->references('slug')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->mediumText('text')->nullable();
            $table->boolean('translated')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('translations');
    }
};
