<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddColumnOptionsIntoParsingTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(
            'parsing_types',
            function (Blueprint $table) {
                $table->json('options')->nullable();
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
            'parsing_types',
            function (Blueprint $table) {
                $table->dropColumn('options');
            }
        );
    }
}
