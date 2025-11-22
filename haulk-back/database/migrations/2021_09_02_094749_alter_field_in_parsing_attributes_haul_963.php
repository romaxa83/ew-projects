<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterFieldInParsingAttributesHaul963 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('parsing_attributes', function (Blueprint $table) {
            $table->string('parser')->nullable();
            $table->string('pattern', 1024)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('parsing_attributes', function (Blueprint $table) {
            $table->dropColumn('parser');
            $table->string('pattern', 1024)->change();
        });
    }
}
