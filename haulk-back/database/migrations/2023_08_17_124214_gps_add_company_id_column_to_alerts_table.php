<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class GpsAddCompanyIdColumnToAlertsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('pgsql_gps')->table('alerts', function (Blueprint $table) {
            $table->bigInteger('company_id')
                ->index()
                ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('pgsql_gps')->table('alerts', function (Blueprint $table) {
            $table->dropColumn('company_id');
        });
    }
}
