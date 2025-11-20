<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSomeFieldsToReportMachinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reports_machines', function (Blueprint $table) {

            $table->dropColumn('equipment_group');
            $table->dropColumn('model_description');

            $table->string('sub_machine_serial_number')->nullable();

            $table->bigInteger('sub_equipment_group_id')
                ->nullable()->unsigned();
            $table->foreign('sub_equipment_group_id')
                ->references('id')->on('jd_equipment_groups');

            $table->bigInteger('sub_model_description_id')
                ->nullable()->unsigned();
            $table->foreign('sub_model_description_id')
                ->references('id')->on('jd_model_descriptions');

            $table->bigInteger('sub_manufacturer_id')
                ->nullable()->unsigned();
            $table->foreign('sub_manufacturer_id')
                ->references('id')->on('jd_manufacturers');
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
            $table->string('equipment_group')->nullable();
            $table->string('model_description')->nullable();

            $table->dropForeign('reports_machines_sub_equipment_group_id_foreign');
            $table->dropIndex('reports_machines_sub_equipment_group_id_foreign');
            $table->dropColumn('sub_equipment_group_id');

            $table->dropForeign('reports_machines_sub_manufacturer_id_foreign');
            $table->dropIndex('reports_machines_sub_manufacturer_id_foreign');
            $table->dropColumn('sub_manufacturer_id');

            $table->dropForeign('reports_machines_sub_model_description_id_foreign');
            $table->dropIndex('reports_machines_sub_model_description_id_foreign');
            $table->dropColumn('sub_model_description_id');

            $table->dropColumn('sub_machine_serial_number');
        });
    }
}
