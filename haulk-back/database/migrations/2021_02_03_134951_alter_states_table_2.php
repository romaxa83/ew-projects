<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterStatesTable2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('states', function (Blueprint $table) {
            $table->string('country_code');
            $table->string('country_name');

            $table->index('name');
            $table->unique(['name', 'country_code']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('states', function (Blueprint $table) {
            $table->dropUnique(['name', 'country_code']);
            $table->dropIndex(['name']);
        });

        Schema::table('states', function (Blueprint $table) {
            $table->dropColumn('country_code');
            $table->dropColumn('country_name');
        });
    }
}
