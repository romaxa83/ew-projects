<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddQuantityMachineToReportReportClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('client_report', function (Blueprint $table) {
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
        Schema::table('client_report', function (Blueprint $table) {
            $table->dropForeign('client_report_model_description_id_foreign');
            $table->dropIndex('client_report_model_description_id_foreign');
            $table->dropColumn('model_description_id');
            $table->dropColumn('quantity_machine');
            $table->string('name_machine')->nullable();
        });
    }
}
