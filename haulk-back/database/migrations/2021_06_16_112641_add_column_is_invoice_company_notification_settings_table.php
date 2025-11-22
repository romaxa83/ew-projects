<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnIsInvoiceCompanyNotificationSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('company_notification_settings', function (Blueprint $table) {
            $table->boolean('is_invoice_allowed')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('company_notification_settings', function (Blueprint $table) {
            $table->dropColumn('is_invoice_allowed');
        });
    }
}
