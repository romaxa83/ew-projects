<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEqGroupRelationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('eq_group_relation', function (Blueprint $table) {
            $table->bigInteger('eg_id')->unsigned();
            $table->foreign('eg_id')
                ->references('id')
                ->on('jd_equipment_groups')
                ->onDelete('cascade');

            $table->bigInteger('sub_eg_id')->unsigned();
            $table->foreign('sub_eg_id')
                ->references('id')
                ->on('jd_equipment_groups')
                ->onUpdate('cascade');

            $table->primary(['eg_id', 'sub_eg_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('eq_group_relation');
    }
}
