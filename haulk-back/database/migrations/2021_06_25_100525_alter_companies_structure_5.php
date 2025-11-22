<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterCompaniesStructure5 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('saas_confirm_token')->nullable();
            $table->string('saas_decline_token')->nullable();
            $table->dateTime('saas_date_token_create')->nullable();
            $table->date('saas_date_delete')->nullable();
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
            $table->dropColumn('saas_confirm_token');
            $table->dropColumn('saas_decline_token');
            $table->dropColumn('saas_date_token_create');
            $table->dropColumn('saas_date_delete');
        });
    }
}
