<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RefactorReportLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reports_locations', function (Blueprint $table) {
            $table->string('lat',50)->nullable()->change();
            $table->string('long', 50)->nullable()->change();
            $table->string('country', 50)->nullable()->change();
            $table->string('region', 100)->nullable()->change();
            $table->string('zipcode', 50)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reports_locations', function (Blueprint $table) {
            $table->string('lat',50)->change();
            $table->string('long', 50)->change();
            $table->string('country', 50)->change();
            $table->string('region', 100)->change();
            $table->string('zipcode', 50)->change();
        });
    }
}
