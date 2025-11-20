<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRegionIdToJdClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('jd_clients', function (Blueprint $table) {
            $table->bigInteger('region_id')->nullable()->unsigned();
//            $table->foreign('region_id')
//                ->references('jd_id')
//                ->on('jd_regions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('jd_clients', function (Blueprint $table) {
            $table->dropColumn('region_id');
        });
    }
}
