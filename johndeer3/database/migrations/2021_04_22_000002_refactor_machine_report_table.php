<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RefactorMachineReportTable extends Migration
{
    public function up()
    {
        Schema::table('reports_machines', function (Blueprint $table) {
            $table->dropColumn('header_brand');
            $table->dropColumn('header_model');

            $table->bigInteger('header_brand_id')->unsigned()->nullable();
            $table->foreign('header_brand_id')
                ->references('id')
                ->on('jd_manufacturers')
                ->onDelete('cascade');

            $table->bigInteger('header_model_id')->unsigned()->nullable();
            $table->foreign('header_model_id')
                ->references('id')
                ->on('jd_model_descriptions')
                ->onDelete('cascade');

            $table->string('trailer_model')->nullable();
        });
    }

    public function down()
    {
        Schema::table('reports_machines', function (Blueprint $table) {
            $table->dropForeign('reports_machines_header_brand_id_foreign');
            $table->dropForeign('reports_machines_header_model_id_foreign');
            $table->dropColumn('header_brand_id');
            $table->dropColumn('header_model_id');
            $table->dropColumn('trailer_model');

            $table->string('header_brand')->nullable();
            $table->string('header_model')->nullable();
        });
    }
}
