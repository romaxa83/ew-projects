<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImportSparesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('import_spares', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('article');
            $table->integer('brand');
            $table->string('name');
            $table->bigInteger('cost')->nullable();
            $table->json('row_data')->nullable();
            $table->unique(['article', 'brand']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('import_spares');
    }
}
