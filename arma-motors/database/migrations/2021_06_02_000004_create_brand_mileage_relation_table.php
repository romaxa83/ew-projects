<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBrandMileageRelationTable extends Migration
{
    public function up()
    {
        Schema::create('car_brand_mileage_relations', function (Blueprint $table) {
            $table->unsignedBigInteger('brand_id');
            $table->foreign('brand_id')
                ->references('id')
                ->on('car_brands')
                ->onDelete('cascade');
            $table->unsignedBigInteger('mileage_id');
            $table->foreign('mileage_id')
                ->references('id')
                ->on('mileages')
                ->onDelete('cascade');

            $table->primary(['brand_id', 'mileage_id'], 'pk-cbmr_primary');
        });
    }

    public function down()
    {
        Schema::dropIfExists('car_brand_mileage_relations');
    }
}

