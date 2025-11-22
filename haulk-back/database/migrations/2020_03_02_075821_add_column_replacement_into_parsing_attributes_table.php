<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddColumnReplacementIntoParsingAttributesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(
            'parsing_attributes',
            function (Blueprint $table) {
                $table->json('replacement_before')->nullable();
                $table->json('replacement_after')->nullable();
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(
            'parsing_attributes',
            function (Blueprint $table) {
                $table->dropColumn('replacement_before');
                $table->dropColumn('replacement_after');
            }
        );
    }
}
