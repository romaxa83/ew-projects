<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterNewsTable1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('news', function (Blueprint $table) {
            $table->string('title_ru')->nullable()->change();
            $table->string('title_es')->nullable()->change();
            $table->text('body_short_en')->nullable()->change();
            $table->text('body_short_ru')->nullable()->change();
            $table->text('body_short_es')->nullable()->change();
            $table->mediumText('body_en')->nullable()->change();
            $table->mediumText('body_ru')->nullable()->change();
            $table->mediumText('body_es')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('news', function (Blueprint $table) {
            $table->string('title_ru')->nullable(false)->change();
            $table->string('title_es')->nullable(false)->change();
            $table->text('body_short_en')->nullable(false)->change();
            $table->text('body_short_ru')->nullable(false)->change();
            $table->text('body_short_es')->nullable(false)->change();
            $table->mediumText('body_en')->nullable(false)->change();
            $table->mediumText('body_ru')->nullable(false)->change();
            $table->mediumText('body_es')->nullable(false)->change();
        });
    }
}
