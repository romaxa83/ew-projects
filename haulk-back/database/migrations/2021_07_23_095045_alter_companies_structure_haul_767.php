<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterCompaniesStructureHaul767 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('crm_confirm_token')->nullable();
            $table->string('crm_decline_token')->nullable();
            $table->dateTime('crm_date_token_create')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('crm_confirm_token');
            $table->dropColumn('crm_decline_token');
            $table->dropColumn('crm_date_token_create');
        });
    }
}
