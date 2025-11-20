<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeTranslateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('translates', function (Blueprint $table) {
            $table->string('entity_type', 250)->nullable()->change();
            $table->integer('entity_id')->nullable()->change();
            $table->text('text')->change();
            $table->string('lang', 5)->change();
            $table->string('alias')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('translates', function (Blueprint $table) {
            $table->string('entity_type', 250)->comment('К какой модели относиться запись (к примеру user)')->change();
            $table->integer('entity_id')->comment('К какой модели относиться запись (к примеру notification)')->change();
            $table->string('text')->comment('Действия который произошли (create, delete)')->change();
            $table->text('lang')->comment('К какой модели относиться запись (к примеру notification)')->change();
            $table->dropColumn('alias');
        });
    }
}