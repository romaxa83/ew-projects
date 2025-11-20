<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJdProductsTable extends Migration
{
    public function up(): void
    {
        Schema::create('jd_products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('jd_id')->comment('Поле id из импорта');
            $table->bigInteger('jd_model_description_id')->nullable();
            $table->bigInteger('jd_equipment_group_id')->nullable();
            $table->bigInteger('jd_manufacture_id')->nullable();
            $table->bigInteger('jd_size_parameter_id')->nullable();
            $table->integer('size_name')->nullable();
            $table->boolean('status')->nullable();
            $table->integer('type')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jd_products');
    }
}
