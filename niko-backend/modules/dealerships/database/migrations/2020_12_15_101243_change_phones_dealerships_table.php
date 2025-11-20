<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangePhonesDealershipsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dealerships', function (Blueprint $table) {
            $table->dropColumn('phone');
            $table->json('phones')->nullable();
            $table->string('site_link')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dealerships', function (Blueprint $table) {
            $table->dropColumn('phones');
            $table->dropColumn('site_link');
            $table->string('phone')->nullable();
        });
    }
}

