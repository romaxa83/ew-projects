<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddQuantityMachineToReportClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('report_report_client', function (Blueprint $table) {
            $table->dropColumn('name_machine');
            $table->integer('quantity_machine')->nullable();
            $table->bigInteger('model_description_id')->nullable()->unsigned();
            $table->foreign('model_description_id')
                ->references('id')
                ->on('jd_model_descriptions');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('report_report_client', function (Blueprint $table) {
            $table->dropForeign('report_report_client_model_description_id_foreign');
            $table->dropIndex('report_report_client_model_description_id_foreign');
            $table->dropColumn('model_description_id');
            $table->dropColumn('quantity_machine');
            $table->string('name_machine')->nullable();
        });
    }
}
