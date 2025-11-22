<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoleTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('roles_translations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('role_id');
            $table->string('lang')->index();
            $table->string('name')->nullable();

            $table->unique(['role_id', 'lang']);
            $table->foreign('role_id')
                ->references('id')
                ->on('roles')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('roles_translations');
    }
}
