<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateParsingTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'parsing_types',
            function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->timestamps();
                $table->boolean('status');
                $table->string('name');
                $table->string('pattern', 1024);
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
        Schema::dropIfExists('parsing_types');
    }
}
