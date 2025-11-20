<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddManufacturerIdToReportMachinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reports_machines', function (Blueprint $table) {
            $table->bigInteger('manufacturer_id')
                ->nullable()->unsigned();
            $table->foreign('manufacturer_id')
                ->references('id')
                ->on('jd_manufacturers');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reports_machines', function (Blueprint $table) {
            $table->dropForeign('reports_machines_manufacturer_id_foreign');
            $table->dropIndex('reports_machines_manufacturer_id_foreign');
            $table->dropColumn('manufacturer_id');
        });
    }
}
