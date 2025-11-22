<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRateToOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_additions', function (Blueprint $table) {
            $table->tinyInteger('rate')->nullable();
            $table->string('rate_comment', 1000)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_additions', function (Blueprint $table) {
            $table->dropColumn('rate');
            $table->dropColumn('rate_comment');
        });
    }
}

