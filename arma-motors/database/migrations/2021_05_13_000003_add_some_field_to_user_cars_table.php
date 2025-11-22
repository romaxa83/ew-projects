<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSomeFieldToUserCarsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_cars', function (Blueprint $table) {
            $table->string('delete_reason', 20)->nullable();
            $table->string('delete_comment', 300)->nullable();
            $table->boolean('has_insurance')->default(false);
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
            $table->dropColumn('delete_reason');
            $table->dropColumn('delete_comment');
            $table->dropColumn('has_insurance');
        });
    }
}
