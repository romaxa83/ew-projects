<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrailersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trailers', function (Blueprint $table) {
            $table->id();
            $table->string('unit_number', 5);
            $table->string('vin');
            $table->string('year', 4);
            $table->string('make');
            $table->string('model');
            $table->string('license_plate')->nullable();
            $table->string('temporary_plate')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('owner_id')
                ->nullable()
                ->references('id')->on('bs_vehicle_owners')
                ->onUpdate('cascade')
                ->onDelete('set null');
            $table->foreignId('driver_id')
                ->nullable()
                ->references('id')->on('users')
                ->onUpdate('cascade')
                ->onDelete('set null');
            $table->unsignedBigInteger('broker_id')->nullable();
            $table->unsignedBigInteger('carrier_id')->nullable();
            $table->index('broker_id');
            $table->index('carrier_id');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('trailers');
    }
}
