<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterFieldInPaymentsHaul1243 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('customer_payment_invoice_id')
                ->nullable();
            $table->string('customer_payment_invoice_notes')
                ->nullable();
            $table->string('customer_payment_invoice_issue_date')
                ->nullable();
            $table->string('broker_payment_invoice_id')
                ->nullable();
            $table->string('broker_payment_invoice_notes')
                ->nullable();
            $table->string('broker_payment_invoice_issue_date')
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
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('customer_payment_invoice_id');
            $table->dropColumn('customer_payment_invoice_notes');
            $table->dropColumn('customer_payment_invoice_issue_date');
            $table->dropColumn('broker_payment_invoice_id');
            $table->dropColumn('broker_payment_invoice_notes');
            $table->dropColumn('broker_payment_invoice_issue_date');
        });
    }
}
