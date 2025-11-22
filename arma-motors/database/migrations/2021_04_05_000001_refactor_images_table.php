<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RefactorImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('images', function (Blueprint $table) {
            $table->dropColumn('url');
            $table->string('model')->nullable();
            $table->string('hash');
            $table->boolean('active')->default(true);
            $table->integer('position')->default(0);
            $table->string('mime')->nullable();
            $table->string('ext')->nullable();
            $table->string('size')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('images', function (Blueprint $table) {
            $table->string('url');
            $table->dropColumn('model');
            $table->dropColumn('hash');
            $table->dropColumn('active');
            $table->dropColumn('position');
            $table->dropColumn('mime');
            $table->dropColumn('ext');
            $table->dropColumn('size');
        });
    }
}
