<?php

use App\Models\Fueling\FuelCard;
use App\Models\Users\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFuelCardHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fuel_card_histories', function (Blueprint $table) {
            $table->id();
            $table->timestamp('date_assigned')->nullable();
            $table->timestamp('date_unassigned')->nullable();
            $table->boolean('active')->default(false);
            $table->foreignId('fuel_card_id')
                ->references('id')
                ->on(FuelCard::TABLE_NAME)
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreignId('user_id')
                ->references('id')
                ->on(User::TABLE_NAME)
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
        Schema::dropIfExists('fuel_card_histories');
    }
}
