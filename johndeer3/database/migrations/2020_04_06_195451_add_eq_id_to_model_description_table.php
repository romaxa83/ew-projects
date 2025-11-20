<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEqIdToModelDescriptionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('jd_model_descriptions', function (Blueprint $table) {
            $table->bigInteger('eg_jd_id')->nullable()->comment('Поле для связи equipmentGroup через поле jd_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('jd_model_descriptions', function (Blueprint $table) {
            $table->dropColumn('eg_jd_id');
        });
    }
}
