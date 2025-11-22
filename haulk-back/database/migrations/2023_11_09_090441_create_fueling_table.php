<?php

use App\Models\Fueling\FuelCard;
use App\Models\Fueling\FuelingHistory;
use App\Models\Users\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFuelingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fueling', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->foreignId('fueling_history_id')
                ->nullable()
                ->references('id')
                ->on(FuelingHistory::TABLE_NAME)
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->string('card')->nullable();
            $table->string('uuid')->unique();
            $table->string('invoice')->nullable();
            $table->string('transaction_date')->nullable();

            $table->string('user')->nullable();
            $table->string('location')->nullable();
            $table->string('state')->nullable();
            $table->string('fees')->nullable();
            $table->string('item')->nullable();
            $table->string('unit_price')->nullable();
            $table->string('quantity')->nullable();
            $table->string('amount')->nullable();
            $table->string('status')->nullable();
            $table->string('source')->nullable();
            $table->string('provider')->nullable();

            $table->foreignId('fuel_card_id')
                ->nullable()
                ->references('id')
                ->on(FuelCard::TABLE_NAME)
                ->onUpdate('cascade')
                ->onDelete('set null');
            $table->foreignId('user_id')
                ->nullable()
                ->references('id')
                ->on(User::TABLE_NAME)
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->unsignedBigInteger('broker_id')->nullable();
            $table->unsignedBigInteger('carrier_id')->nullable();
            $table->index('broker_id');
            $table->index('carrier_id');
            $table->boolean('valid')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fueling');
    }
}
