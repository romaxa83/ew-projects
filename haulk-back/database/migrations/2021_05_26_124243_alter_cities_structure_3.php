<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterCitiesStructure3 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->dropUnique(['zip', 'country_code']);
        });

        Schema::table('cities', function (Blueprint $table) {
            $table->unique(['name', 'zip', 'country_code']);
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
            $table->dropUnique(['name', 'zip', 'country_code']);
        });

        Schema::table('cities', function (Blueprint $table) {
            $table->unique(['zip', 'country_code']);
        });
    }
}
