<?php

use App\Models\Catalogs\Calc\Work;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCalcModelWorkPivotTable extends Migration
{
    public function up()
    {
        Schema::create('calc_model_work_pivot', function (Blueprint $table) {
            $table->unsignedBigInteger('model_id');
            $table->foreign('model_id')
                ->references('id')
                ->on('calc_models')
                ->onDelete('cascade');
            $table->unsignedBigInteger('work_id');
            $table->foreign('work_id')
                ->references('id')
                ->on('works')
                ->onDelete('cascade');

            $table->integer('minutes')->default(Work::DEFAULT_MINUTES);
            $table->primary(['model_id', 'work_id'], 'pk-cmwp_primary');
        });
    }

    public function down()
    {
        Schema::dropIfExists('calc_model_work_pivot');
    }
}

