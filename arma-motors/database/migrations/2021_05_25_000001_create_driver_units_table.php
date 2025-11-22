<?php

use App\Models\Catalogs\Calc\Work;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDriverUnitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('drive_units', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('sort')->default(0);
            $table->boolean('active')->default(true);
            $table->string('name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('drive_units');
    }
}
