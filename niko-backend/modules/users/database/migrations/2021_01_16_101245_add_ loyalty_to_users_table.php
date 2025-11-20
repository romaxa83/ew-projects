<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use WezomCms\Users\Models\User;
use WezomCms\Users\Types\UserStatus;

class AddLoyaltyToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->tinyInteger('loyalty_type')->default(\WezomCms\Users\Types\LoyaltyType::NONE);
            $table->tinyInteger('loyalty_level')->default(\WezomCms\Users\Types\LoyaltyLevel::NONE);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('loyalty_type');
            $table->dropColumn('loyalty_level');
        });
    }
}
