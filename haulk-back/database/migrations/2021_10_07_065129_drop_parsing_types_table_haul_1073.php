<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropParsingTypesTableHaul1073 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('parsing_types');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create(
            'parsing_types',
            function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->timestamps();
                $table->boolean('status');
                $table->string('name');
                $table->string('pattern', 1024);
                $table->json('options')->nullable();
            }
        );
    }
}
