<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ChangeDriverInformationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('driver_information', function (Blueprint $table) {

            DB::statement('INSERT INTO driver_licenses (driver_id, license_number, issuing_state_id, type)
                 SELECT driver_id, driver_license_number, state_id, \'current\' FROM driver_information');

            $table->string('medical_card_number', 16)->nullable();
            $table->date('medical_card_issuing_date')->nullable();
            $table->date('medical_card_expiration_date')->nullable();

            $table->date('mvr_reported_date')->nullable();

            $table->boolean('has_company')->default(false);
            $table->string('company_name', 20)->nullable();
            $table->string('company_ein', 20)->nullable();
            $table->string('company_address', 150)->nullable();
            $table->string('company_city', 50)->nullable();
            $table->string('company_zip', 10)->nullable();

            $table->dropColumn('driver_license_number');
            $table->dropColumn('state_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('driver_information', function (Blueprint $table) {
            $table->dropColumn('medical_card_number');
            $table->dropColumn('medical_card_issuing_date');
            $table->dropColumn('medical_card_expiration_date');

            $table->dropColumn('mvr_reported_date');

            $table->dropColumn('has_company');
            $table->dropColumn('company_name');
            $table->dropColumn('company_ein');
            $table->dropColumn('company_address');
            $table->dropColumn('company_city');
            $table->dropColumn('company_zip');

            $table->string('driver_license_number')->nullable();
            $table->foreignId('state_id')
                ->nullable()
                ->references('id')->on('states')
                ->onDelete('set null')
                ->onUpdate('cascade');
        });

        DB::statement('
                UPDATE driver_information
                SET driver_license_number = driver_licenses.license_number,
                    state_id = driver_licenses.issuing_state_id
                FROM driver_licenses
                    WHERE driver_licenses.driver_id = driver_information.driver_id AND driver_licenses.type = \'current\'
            ');
    }
}
