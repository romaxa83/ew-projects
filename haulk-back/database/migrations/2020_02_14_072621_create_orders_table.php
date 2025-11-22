<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('driver_id')->nullable();
            $table->string('load_id');
            $table->string('internal_load_id')->nullable();
            $table->integer('status');
            $table->integer('inspection_type')->nullable();
            $table->text('instructions')->nullable();
            $table->unsignedBigInteger('pickup_contact_id')->nullable();
            $table->unsignedBigInteger('pickup_date')->nullable();
            $table->unsignedBigInteger('delivery_contact_id')->nullable();
            $table->unsignedBigInteger('delivery_date')->nullable();
            $table->unsignedBigInteger('shipper_contact_id')->nullable();

            $table->foreign('pickup_contact_id')
                ->references('id')->on('contacts')
                ->onDelete('set null');
            $table->foreign('delivery_contact_id')
                ->references('id')->on('contacts')
                ->onDelete('set null');
            $table->foreign('shipper_contact_id')
                ->references('id')->on('contacts')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['pickup_contact_id']);
            $table->dropForeign(['delivery_contact_id']);
            $table->dropForeign(['shipper_contact_id']);
        });
        Schema::dropIfExists('orders');
    }
}
