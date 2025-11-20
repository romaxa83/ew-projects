<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeNameToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->string('login')->after('id');
            $table->string('phone')->nullable()->after('email');
            $table->smallInteger('status')->default(1)->after('password');
            $table->bigInteger('jd_id')->nullable()->after('status');
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
            $table->dropColumn('login');
            $table->dropColumn('phone');
            $table->dropColumn('status');
            $table->dropColumn('jd_id');
            $table->string('name')->after('id');
        });
    }
}