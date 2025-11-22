<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterCompanyRegistrationsStructure2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE company_registrations ALTER usdot TYPE INT USING (trim(usdot)::integer);');

        Schema::table('company_registrations', function (Blueprint $table) {
            $table->unsignedInteger('mc_number')->nullable();
            $table->string('name')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->unsignedBigInteger('state_id')->nullable();
            $table->string('zip')->nullable();
            $table->string('status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('company_registrations', function (Blueprint $table) {
            $table->string('usdot')->change();

            $table->dropColumn('mc_number');
            $table->dropColumn('name');
            $table->dropColumn('address');
            $table->dropColumn('city');
            $table->dropColumn('state_id');
            $table->dropColumn('zip');
            $table->dropColumn('status');
        });
    }
}
