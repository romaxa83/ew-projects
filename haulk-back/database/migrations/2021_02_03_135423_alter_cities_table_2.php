<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterCitiesTable2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->dropUnique(['zip']);
        });

        Schema::table('cities', function (Blueprint $table) {
            $table->string('country_code');
            $table->string('country_name');

            $table->index('name');
            $table->index('zip');
            $table->unique(['zip', 'country_code']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->dropIndex(['name']);
            $table->dropIndex(['zip']);
            $table->dropUnique(['zip', 'country_code']);
        });

        Schema::table('cities', function (Blueprint $table) {
            $table->unique('zip');

            $table->dropColumn('country_code');
            $table->dropColumn('country_name');
        });
    }
}
