<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use WezomCms\Users\Models\User;
use WezomCms\Users\Types\UserStatus;

class RefactorUserCarsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_cars', function (Blueprint $table) {
            $table->dropColumn('millage');
            $table->dropColumn('engine_description');
            $table->string('engine_volume')->nullable();

            $table->unsignedBigInteger('engine_type_id')->nullable();
            $table->foreign('engine_type_id')
                ->references('id')
                ->on('car_engine_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_cars', function (Blueprint $table) {
            $table->string('millage')->nullable()->comment('Пробег авто (км)');
            $table->string('engine_description')->nullable();
            $table->dropColumn('engine_volume');

            $table->dropForeign('user_cars_engine_type_id_foreign');
            $table->dropIndex('user_cars_engine_type_id_foreign');
            $table->dropColumn('engine_type_id');
        });
    }
}


