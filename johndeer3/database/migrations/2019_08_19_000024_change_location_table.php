<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeLocationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reports_locations', function (Blueprint $table) {
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
        Schema::create('reports_locations', function (Blueprint $table) {
            $table->string('region', 100)->change();
            $table->string('zipcode', 50)->change();
        });
    }
}