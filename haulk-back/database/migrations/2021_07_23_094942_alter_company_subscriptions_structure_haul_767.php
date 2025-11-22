<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterCompanySubscriptionsStructureHaul767 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('company_subscriptions', function (Blueprint $table) {
            $table->dropColumn('confirm_token');
            $table->dropColumn('decline_token');
            $table->dropColumn('date_token_create');
            $table->dropColumn('delete_company');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('company_subscriptions', function (Blueprint $table) {
            $table->boolean('delete_company')->default(false);
            $table->string('confirm_token')->nullable();
            $table->string('decline_token')->nullable();
            $table->dateTime('date_token_create')->nullable();
        });
    }
}
