<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterCompaniesStructure1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->unsignedBigInteger('state_id')->nullable();
            $table->string('zip')->nullable();
            $table->string('timezone')->nullable();
            $table->string('phone')->nullable();
            $table->string('phone_name')->nullable();
            $table->json('phones')->nullable();
            $table->string('email')->unique();
            $table->string('fax')->nullable();
            $table->string('website')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('address');
            $table->dropColumn('city');
            $table->dropColumn('state_id');
            $table->dropColumn('zip');
            $table->dropColumn('timezone');
            $table->dropColumn('phone');
            $table->dropColumn('phone_name');
            $table->dropColumn('phones');
            $table->dropColumn('email');
            $table->dropColumn('fax');
            $table->dropColumn('website');
        });
    }
}
