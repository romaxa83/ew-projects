<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCalcModelSparesPivotTable extends Migration
{
    public function up()
    {
        Schema::create('calc_model_spares_pivot', function (Blueprint $table) {
            $table->unsignedBigInteger('model_id');
            $table->foreign('model_id')
                ->references('id')
                ->on('calc_models')
                ->onDelete('cascade');
            $table->unsignedBigInteger('spares_id');
            $table->foreign('spares_id')
                ->references('id')
                ->on('spares')
                ->onDelete('cascade');

            $table->integer('qty')->default(\App\Models\Catalogs\Calc\Spares::DEFAULT_QTY);
            $table->primary(['model_id', 'spares_id'], 'pk-cmsp_primary');
        });
    }

    public function down()
    {
        Schema::dropIfExists('calc_model_spares_pivot');
    }
}
