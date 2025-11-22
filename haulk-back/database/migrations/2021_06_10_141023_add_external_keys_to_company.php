<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExternalKeysToCompany extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('company_subscriptions', function (Blueprint $table) {
            $table->foreign('company_id')
                ->references('id')->on('companies')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->foreign('carrier_id')
                ->references('id')->on('companies')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });

        Schema::table('active_driver_histories', function (Blueprint $table) {
            $table->foreign('carrier_id')
                ->references('id')->on('companies')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });

        Schema::table('change_emails', function (Blueprint $table) {
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');
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
            $table->dropForeign(['company_id']);
        });

        Schema::table('active_driver_histories', function (Blueprint $table) {
            $table->dropForeign(['carrier_id']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['carrier_id']);
        });

        Schema::table('change_emails', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
    }
}
