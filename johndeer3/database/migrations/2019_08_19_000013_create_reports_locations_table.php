<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportsLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reports_locations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('report_id')->unsigned();
            $table->foreign('report_id')->references('id')
                ->on('reports')->onDelete('cascade');
            $table->string('lat',50);
            $table->string('long', 50);
            $table->string('country', 50);
            $table->string('city', 50)->nullable();
            $table->string('region', 100);
            $table->string('zipcode', 50);
            $table->string('street', 150)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reports_locations');
    }
}