<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVehiclesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->unsignedBigInteger('order_id');
            $table->boolean('inop')->default(false);
            $table->boolean('enclosed')->default(false);
            $table->string('vin')->nullable();
            $table->string('year', 4)->nullable();
            $table->string('make')->nullable();
            $table->string('model')->nullable();
            $table->unsignedBigInteger('type_id')->nullable();
            $table->string('color')->nullable();
            $table->string('license_plate')->nullable();
            $table->string('odometer')->nullable();
            $table->decimal('price', 10, 2)->nullable();

            $table->foreign('order_id')
                ->references('id')->on('orders')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
        });
        Schema::dropIfExists('vehicles');
    }
}
