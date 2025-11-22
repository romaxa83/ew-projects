<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterFieldInTextBlocksHaul1413 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('text_blocks', function (Blueprint $table) {
            $table->string('group')->change();
            $table->jsonb('scope');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('text_blocks', function (Blueprint $table) {
            $table->unsignedSmallInteger('group')->change();
            $table->dropColumn('scope');
        });
    }
}
