<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFuelCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fuel_cards', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('card', 5);
            $table->string('provider', 12);
            $table->boolean('active');
            $table->string('status', 12);
            $table->timestamp('deactivated_at')->nullable();

            $table->unsignedBigInteger('broker_id')->nullable();
            $table->unsignedBigInteger('carrier_id')->nullable();
            $table->index('broker_id');
            $table->index('carrier_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fuel_cards');
    }
}
