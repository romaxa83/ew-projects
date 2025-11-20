<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use WezomCms\Users\Models\User;
use WezomCms\Users\Types\UserStatus;

class AddStatusUserCarsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_cars', function (Blueprint $table) {
            $table->smallInteger('status')->default(\WezomCms\Users\Types\UserCarStatus::ACTIVE);
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
            $table->dropColumn('status');
        });
    }
}
