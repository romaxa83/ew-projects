<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePermissionGroupTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('permission_group_translations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('group_id');
            $table->string('lang')->index();
            $table->string('name')->nullable();

            $table->unique(['group_id', 'lang']);
            $table->foreign('group_id')
                ->references('id')
                ->on('permission_groups')
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
        Schema::dropIfExists('permission_group_translations');
    }
}
