<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBrandWorkRelationTable extends Migration
{
    public function up()
    {
        Schema::create('car_brand_work_relations', function (Blueprint $table) {
            $table->unsignedBigInteger('brand_id');
            $table->foreign('brand_id')
                ->references('id')
                ->on('car_brands')
                ->onDelete('cascade');
            $table->unsignedBigInteger('work_id');
            $table->foreign('work_id')
                ->references('id')
                ->on('works')
                ->onDelete('cascade');

            $table->primary(['brand_id', 'work_id'], 'pk-cbwr_primary');
        });
    }

    public function down()
    {
        Schema::dropIfExists('car_brand_work_relations');
    }
}
