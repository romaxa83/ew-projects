<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use WezomCms\Users\Models\User;
use WezomCms\Users\Types\UserStatus;

class ChangeFieldLevelToLoyaltiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('loyalties', function (Blueprint $table) {
            $table->smallInteger('level')->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('loyalties', function (Blueprint $table) {
            $table->dropUnique('loyalties_level_unique');
            $table->smallInteger('level')->change();
        });
    }
}
