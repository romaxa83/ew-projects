<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use WezomCms\Users\Models\User;
use WezomCms\Users\Types\UserStatus;

class CreateUserLoyaltyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_loyalty', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->tinyInteger('loyalty_type')->default(\WezomCms\Users\Types\LoyaltyType::NONE);
            $table->tinyInteger('loyalty_level')->default(\WezomCms\Users\Types\LoyaltyLevel::NONE);
            $table->integer('level_up_amount')->nullable()->comment('Сколько клиент (группа) дожен потратить для перехода на новый уровень');
            $table->tinyInteger('buy_cars')->default(0)->comment('Купленные авто для определения уровня в программе лояльности');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_loyalty');
    }
}
